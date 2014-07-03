<?php

class SyncManager {

	private $settings = array();
	
	function __construct() {		

		add_action ( 'template_redirect', array ( &$this, 'render_product_feed' ), 15 );			
		
		include(plugin_dir_path( __FILE__ ) . 'StoreYaAPI.php');
	}
	
	
	function storeya_get_order_currency($order) {
	if(is_null($order) || !is_object($order)) {
		return '';
	}
	if(method_exists($order,'get_order_currency')) { 
		return $order->get_order_currency();
	}
	if(isset($order->order_custom_fields) && isset($order->order_custom_fields['_order_currency'])) {		
 		if(is_array($order->order_custom_fields['_order_currency'])) {
 			return $order->order_custom_fields['_order_currency'][0];
 		}	
	}
	return '';
}


		function past_order_time_query( $where = '' ) {
	// posts in the last 30 days
	//$where .= " AND post_date > '" . date('Y-m-d', strtotime('-90 days')) . "'";
	
		if ( isset ( $_REQUEST['from'] ))
				{
				  $from = $_REQUEST['from'];                  
                  $where .= " AND post_date > '" . date('Y-m-d', strtotime($from)) . "'";
				  
				  if ( isset ( $_REQUEST['to'] ))
				  {
				    $to = $_REQUEST['to'];
                   	$where .=" AND post_date < '" . date('Y-m-d', strtotime($to)) . "'";
				  }		     
                 		
				} 
	
	return $where;
}


function storeya_get_product_image_url($product_id) {
	$url = wp_get_attachment_url(get_post_thumbnail_id($product_id));
	return $url ? $url : null;
}


	
	
	function storeya_get_single_map_data($order_id) {
	$order = new WC_Order($order_id);
	$data = null;
	if(!is_null($order->id)) {
		$data = array();
		$data['order_date'] = $order->order_date;		
		echo $data['order_date'];
		
		$data['email'] = $order->billing_email;		
		echo $data['email'];
		
		$data['customer_name'] = $order->billing_first_name.' '.$order->billing_last_name;		
		echo $data['customer_name'];
		
		$data['order_id'] = $order_id;		
		echo $data['order_id'];
		
		$data['currency_iso'] = $this->storeya_get_order_currency($order);
		
		
		          $used_coupons = null;
		          if( $order->get_used_coupons() ) {

		           foreach( $order->get_used_coupons() as $coupon) {
                             $used_coupons.= $coupon.';';
                           
                           }                          
                         }
                          $data['used_coupons'] = $used_coupons;
                          echo "used_coupons: ".$data['used_coupons'];			 
			
			 
			 $data['total_discount'] = $order->get_total_discount();
			 echo "total_discount: ".$data['total_discount'];
			
		         $data['total'] = $order->get_total();
			 echo "total: ".$data['total'];
		
		         $data['items_count'] = $order->get_item_count();
		         echo "items_count: ".$data['items_count'];
		
		$products_arr = array();
		$product_index = 0;
		foreach ($order->get_items() as $product) 
		{
			$product_instance = get_product($product['product_id']);
 
			$description = '';
			if (is_object($product_instance)) {
				$description = strip_tags($product_instance->get_post_data()->post_excerpt);	
			}
			$product_data = array();   
			$product_data['url'] = get_permalink($product['product_id']); 
			$product_data['name'] = $product['name'];
			$product_data['image'] = $this->storeya_get_product_image_url($product['product_id']);
			$product_data['description'] = $description;
			$product_data['price'] = $product['line_total'];			
			$product_data['id'] = $product['product_id'];
			
			$products_arr[$product_index] = $product_data;
			$product_index++;				
			
		}	
		$data['products'] = $products_arr;
	}
	return $data;
}

	
		private  function storeya_get_past_orders() {  
  
  	$result = null;
  	
	$args = array(
		'post_type'			=> 'shop_order'
		
        ,'posts_per_page' 	=> -1
        	//,'tax_query' => array(
			//array(
			//	'taxonomy' => 'shop_order_status',
			//	'field' => 'slug',
			//	'terms' => array('completed'),
			//	'operator' => 'IN'
			//)
		//)	
	);	
	
	$where='';
	
	
	add_filter( 'posts_where', array ( &$this, 'past_order_time_query') );
	
	$query = new WP_Query( $args );	
	
	//remove_filter( 'posts_where', 'past_order_time_query' );
	
	wp_reset_query();
	
	if ($query->have_posts()) {	       
	        
		$orders = array();
		while ($query->have_posts()) { 
			$query->the_post();
			$order = $query->post;		
			$single_order_data = $this->storeya_get_single_map_data($order->ID);
			if(!is_null($single_order_data)) {
				$orders[] = $single_order_data;
			}      	
		}
		if(count($orders) > 0) {
			$post_bulk_orders = array_chunk($orders, 200);
			$result = array();
			foreach ($post_bulk_orders as $index => $bulk)
			{
				$result[$index] = array();
				$result[$index]['orders'] = $bulk;
				$result[$index]['platform'] = 'woocommerce';
			        $result[$index]['app_type_id'] = '1';
				if (get_option('scpID'))
				{
                                  $result[$index]['sid'] = get_option('scpID');
								  echo "sid_".$result[$index]['sid'];
				}
				
				if ( isset ( $_REQUEST['key'] ))
				{
				  $result[$index]['sty_key'] = $_REQUEST['key'];
                  echo "sty_key_".$result[$index]['sty_key'];				  
				} 
				
			}
		}		
	}
	else
	{
	
	        echo "There are no orders.";
	}
	
  
 return $result;	
}

	
		function storeya_send_past_orders() {
	
		
	       $past_orders =$this->storeya_get_past_orders();
			
		$is_success = true;
		if(!is_null($past_orders) && is_array($past_orders)) {
			$storeya_api = new StoreYaAPI('app_key', 'secret');
			
				foreach ($past_orders as $post_bulk) 
					if (!is_null($post_bulk))
					{					
						$response = $storeya_api->create_purchases($post_bulk);						
						if ($response['code'] != 200 && $is_success)
						{
							$is_success = false;							
							echo 'response code: '.$response['code'];; 						     
						}
					}
				if ($is_success)
				{
					echo "Past orders sent successfully";		
				}	
			
		}
		else {
			echo "Could not retrieve past orders";
		}	
			
}
		
	
	function render_product_feed() {	        
	       
	        $this->storeya_send_past_orders();
	        exit();
	        }
	        
  }
  
  $SyncManager = new SyncManager ();
 ?>