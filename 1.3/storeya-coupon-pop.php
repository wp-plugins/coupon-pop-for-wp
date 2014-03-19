<?php
/*
Plugin Name: Coupon Pop widget for WP
Plugin URI: http://www.storeya.com/public/couponpop
Description: A plugin that increases your fan base and email lists by popping up special offers and discounts to your visitors.
Version: 1.1
Author: StoreYa
Author URI: http://www.storeya.com/

=== VERSION HISTORY ===
01.11.13 - v1.0 - The first version

=== LEGAL INFORMATION ===
Copyright © 2013 StoreYa Feed LTD - http://www.storeya.com/

License: GPLv2 
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

$plugurldir = get_option('siteurl') . '/' . PLUGINDIR . '/storeya-coupon-pop/';
$scp_domain = 'StoreYaCouponPop';
load_plugin_textdomain($scp_domain, 'wp-content/plugins/storeya-coupon-pop');
add_action('init', 'scp_init');
add_action('wp_footer', 'scp_insert');
add_action('admin_notices', 'scp_admin_notice');
add_filter('plugin_action_links', 'scp_plugin_actions', 10, 2);

function scp_init()
{
    if (function_exists('current_user_can') && current_user_can('manage_options'))
        add_action('admin_menu', 'scp_add_settings_page');
    if (!function_exists('get_plugins'))
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    $options = get_option('scpDisable');
}
function scp_settings()
{
    register_setting('storeya-coupon-pop-group', 'scpID');
    register_setting('storeya-coupon-pop-group', 'scpDisable');
    add_settings_section('storeya-coupon-pop', "StoreYa Coupon Pop", "", 'storeya-coupon-pop-group');

}
function scp_plugin_get_version()
{
    if (!function_exists('get_plugins'))
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    $plugin_folder = get_plugins('/' . plugin_basename(dirname(__FILE__)));
    $plugin_file   = basename((__FILE__));
    return $plugin_folder[$plugin_file]['Version'];
}
function scp_insert()
{
    global $current_user;
    if (get_option('scpID')) {
            
        $script = str_replace("\"","\"",get_option('scpID'));
        echo $script; 
    }
}

function scp_admin_notice()
{
    if (!get_option('scpID'))
        echo ('<div class="error"><p><strong>' . sprintf(__('StoreYa Coupon Pop  is disabled. Please go to the <a href="%s">plugin page</a> and enter a valid Couopon Pop script to enable it.'), admin_url('options-general.php?page=storeya-coupon-pop')) . '</strong></p></div>');
}
function scp_plugin_actions($links, $file)
{
    static $this_plugin;
    if (!$this_plugin)
        $this_plugin = plugin_basename(__FILE__);
    if ($file == $this_plugin && function_exists('admin_url')) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=storeya-coupon-pop') . '">' . __('Settings', $scp_domain) . '</a>';
        array_unshift($links, $settings_link);
    }
    return ($links);
}

    function scp_add_settings_page()
    {
        function scp_settings_page()
        {
            global $scp_domain, $plugurldir, $storeya_options;
?>
      <div class="wrap">
        <?php
            screen_icon();
?>
        <h2><?php
            _e('StoreYa Coupon Pop ', $scp_domain);
?> <small><?
            echo scp_plugin_get_version();
?></small></h2>
        <div class="metabox-holder meta-box-sortables ui-sortable pointer">
          <div class="postbox" style="float:left;width:30em;margin-right:20px">
            <h3 class="hndle"><span><?php
            _e('StoreYa Coupon Pop  - Settings', $scp_domain);
?></span></h3>
            <div class="inside" style="padding: 0 10px">
              <p style="text-align:center">
		      </p>
              <form method="post" action="options.php">
                <?php
            settings_fields('storeya-coupon-pop-group');
?>
                <p><label for="scpID"><?php
            printf(__('
Enter Coupon Pop script you got from %1$sIncrease your online sales today with StoreYa!%2$sStoreYa%3$s.', $scp_domain), '<strong><a href="http://www.storeya.com/public/couponpop" target="_blank"  title="', '">', '</a></strong>');
?></label></p>

                  <p><textarea rows="11" cols="62" name="scpID" ><?php echo get_option('scpID');?></textarea></p>
                    <p class="submit">
                      <input type="submit" class="button-primary" value="<?php
            _e('Save Changes');
?>" />
                    </p>
                  </form>
</p>
                  <p style="font-size:smaller;color:#999239;background-color:#ffffe0;padding:0.4em 0.6em !important;border:1px solid #e6db55;-moz-border-radius:3px;-khtml-border-radius:3px;-webkit-border-radius:3px;border-radius:3px"><?php
            printf(__('Don&rsquo;t have a Coupon Pop? No problem! %1$sKeep your visitors engaged with you in all social networks you are active on!%2$sCreate a <strong>FREE</strong> StoreYa Coupon Pop  Now!%3$s', $scp_domain), '<a href="http://www.storeya.com/public/couponpop" target="_blank" title="', '">', '</a>');
?></p>
                  </div>
                </div>




                </div>
              </div>
			  <img src="http://www.storeya.com/widgets/admin?p=WpCouponPop"/>
              <?php
        }
        add_action('admin_init', 'scp_settings');
        add_submenu_page('options-general.php', __('StoreYa Coupon Pop ', $scp_domain), __('StoreYa Coupon Pop ', $scp_domain), 'manage_options', 'storeya-coupon-pop', 'scp_settings_page');
    }

?>