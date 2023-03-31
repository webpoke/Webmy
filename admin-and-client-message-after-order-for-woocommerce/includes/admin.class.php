<?php
/**
 * WP Admin Related Stuff
 * Created Date: November 2, 2022
 * Created By: Ben Rider
 * */
 
class WOOCONVO_Admin {
    
    private static $ins = null;
	
	public static function __instance()
	{
		// create a new object if it doesn't exist.
		is_null(self::$ins) && self::$ins = new self;
		return self::$ins;
	}
    
    function __construct(){
        
        add_action( 'admin_bar_menu', [$this, 'unread_notifications_bar'] , 500 );
    
        // wooconvo admin page
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        
        // this is to remove 'forms' dependeny from wp-admin list
        add_action('admin_enqueue_scripts', [$this, 'disable_wp_admin_form_css'], 11);
        
    
    }
    
    function disable_wp_admin_form_css($hook){
            
        if($hook !== "woocommerce_page_orderconvo-settings") return;
        $wp_styles = wp_styles();
        $wp_styles->remove('wp-admin');
        $wp_styles->add( 'wp-admin', false, array( 'dashicons', 'common','admin-menu', 'dashboard', 'list-tables', 'edit', 'revisions', 'media', 'themes', 'about', 'nav-menus', 'widgets', 'site-icon', 'l10n' ) );
        
    }
    
 
   function admin_menu() {
        
        $parent = 'woocommerce';
        $hook = add_submenu_page(
            $parent,
             __('OrderConvo - Manage Customer Messages and Settings', 'wooconvo'),
             __('OrderConvo', 'wooconvo') ,
            'manage_options',
            'orderconvo-settings',
            array(
                $this,
                'settings_page'
            ),
            35
        );
        // script will be only loaded for this current settings page, not all the pages.
        add_action( 'load-'. $hook, [$this, 'load_scripts'] );
    }
    
    function load_scripts() {
        
        $user_id = get_current_user_id();
    	
    	$wooconvo_data = [
    	                'is_pro'		=> defined('WOOCONVO_VERSION_PRO'),
    	                'plugin_url'	=> WOOCONVO8_URL,
    					'user_id'		=> $user_id,
						'api_url'		=> get_rest_url(null, 'wooconvo/v1'),
						'context'		=> "wp_admin",
						'settings'      => wooconvo_get_settings(),
						];
						
						
		WOOCONVO_Main::load_scripts($wooconvo_data);
    }
    
    
    function settings_page() {
        
        $html = '<div class="wrap">';
        $html .= sprintf(__("<h1 class=\"wp-heading-inline\">%s</h1>", 'wooconvo'), 'OrderConvo - Manage Messages and Settings');
        $html .= '<hr class="wp-header-end">';
        $html .= '<div class="wooconvo-wp-admin-wrapper">';
	    $html .= apply_filters('orderconvo_root', '<div id="orderconvo_root"></div>');
	    $html .= '</div>';
	    $html .= '</div>';
	    
	    echo apply_filters('the_content', $html);
    }
    
    // function wcforce_call_wp(){
        
    //     wp_send_json($_POST);
    // }
    
    function unread_notifications_bar() {
        
        $user_type = 'vendor';
        $unread_orders = wooconvo_get_unread_orders($user_type);
        $unread_message = 0;
        foreach($unread_orders as $order){
            
            $msg_obj = new WOOCONVO_Order($order->ID);
            $unread_message += $msg_obj->unread_vendor;
        }
        
        // var_dump($unread_message); exit;
        if ( current_user_can( 'manage_options' ) ) {
            global $wp_admin_bar;
    
            //Add an icon with count
            $wp_admin_bar->add_menu( array(
                'id'    => 'wooconvo-unread', //Change this to yours
                'title' => '<span class="ab-icon dashicons dashicons-buddicons-pm"></span><span class="ab-label">'.$unread_message.'</span>',
                'href'  => get_admin_url( NULL, 'admin.php?page=orderconvo-settings' ),//Replace with your resired destination
            ) );
        }
    }

}

function init_wooconvo_admin(){
	return WOOCONVO_Admin::__instance();
}