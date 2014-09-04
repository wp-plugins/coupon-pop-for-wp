<?php

/**
 * @author storeya
 */
class StoreYaAPI {

	const TIMEOUT = 5;
    protected static $app_key, $secret, $base_uri = 'http://www.storeya.com/api';
    protected $request;

    public function __construct($app_key = null, $secret = null, $base_uri = null) {
        $this->set_app_key($app_key);
       
        $this->set_secret($secret); 

        if ($base_uri != null) {
            self::$base_uri = $base_uri;
        }
    }
	
	    public function set_app_key($app_key) {
        if ($app_key != null) {
            self::$app_key = $app_key;
        }
    }
     
    public function set_secret($secret) {
        if ($secret != null) {
            self::$secret = $secret;
        }
    }

    function request($method, $url, $vars = array()) {
    	if (!empty($vars)) $vars = self::clean_array($vars);
    	$url = self::$base_uri . $url;
        $this->error = '';
        $this->request = curl_init();
        if (is_array($vars)) {       		
			$vars = json_encode($vars);			
        }

		# Set some default CURL options
        curl_setopt($this->request, CURLOPT_HEADER, false);
        curl_setopt($this->request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->request, CURLOPT_USERAGENT, 'StoreYaAPI-Php');
        curl_setopt($this->request, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->request, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->request, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->request, CURLOPT_CONNECTTIMEOUT ,self::TIMEOUT);

        
		curl_setopt($this->request, CURLOPT_POSTFIELDS, $vars);
        curl_setopt($this->request, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-length: '.strlen($vars)));            	
        curl_setopt($this->request, CURLOPT_POST, true);
				
        curl_setopt($this->request, CURLOPT_URL, $url);        
        
		
        $response = curl_exec($this->request);
		
		echo $response;
        
        curl_close($this->request);
        
        return self::process_response($response);
    }
 	
    protected function post($url, $vars = array()) {
        return $this->request('POST', $url, $vars);
    }
	
    protected static function process_response($response) {
		return json_decode($response, true);
    }


    protected static function clean_array(array $array){
        
        foreach( $array as $key => $value ) {
            if( is_array( $value ) ) {
                foreach( $value as $key2 => $value2 ) {
                    if( empty( $value2 ) ) 
                        unset( $array[ $key ][ $key2 ] );
                }
            }
            if( empty( $array[ $key ] ) )
                unset( $array[ $key ] );
        }
        return $array;

    }
	
    public function create_purchases(array $purchases_hash) {
        $request = self::build_request(array('utoken' => 'utoken', 'platform' => 'platform', 'orders' => 'orders', 'app_type_id' => 'app_type_id', 'sid' => 'sid', 'sty_key' =>'sty_key'), $purchases_hash);
          
        return $this->post("/purchases", $request);		
    }

	protected static function build_request(array $params, array $request_params) {
        $request = array();
        foreach ($params as $key => $value) {
            if (array_key_exists($key, $request_params)) {
                $request[$value] = $request_params[$key];
            }
        }
        return $request;
    }
}
?>