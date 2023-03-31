<?php
/**
 * WooConvo main class to control plugin flow.
 * Date Created: November 30, 2022
 * */
 
class WOOCONVO_Main {
	
	private static $ins = null;
	
	public static $settings;
	
	public static function __instance()
	{
		// create a new object if it doesn't exist.
		is_null(self::$ins) && self::$ins = new self;
		return self::$ins;
	}
	
	public function __construct() {
		
		// on order created
		add_action('woocommerce_checkout_order_created', [$this, 'on_order_created']);
		
		if( wooconvo_get_option('enable_order_notices') ){
			// on order status change
			add_action('woocommerce_order_status_changed', [$this, 'on_order_status_change'], 10, 3);
			// on order note		
			add_action('woocommerce_new_customer_note', [$this, 'on_new_order_note'], 10, 1);
		}
		
		
		// Email notification
		add_action('wooconvo_after_message_added', [$this, 'trigger_notification_emails'], 9, 6);
		
		// Register new tab on WC MY ACCOUNT page
        add_rewrite_endpoint( 'wooconvo-messages', EP_ROOT | EP_PAGES );
        add_filter( 'query_vars', [$this, 'wc_account_query_vars'] );
        add_filter( 'woocommerce_account_menu_items', [$this, 'wc_account_menu_items'] );
        add_filter( 'woocommerce_account_menu_item_classes', [$this, 'wc_account_menu_items_class'], 999, 2 );
        add_action( 'woocommerce_account_wooconvo-messages_endpoint', [$this, 'menu_content'] );
        
        // adding nonce to for react plugin data
        add_filter('wooconvo_react_data', [$this, 'add_nonce_to_wooconvo_data'] );
        
        // check if the addons are deactivated, than set their enable_settings to false
        add_filter('wooconvo_get_settings', [$this, 'check_addons_enability']);
        
        // third party plugin integrations
        
        // Multi Order For WooCommerce - update wooconvo_thread when sub order is created
        add_action('mofwc_after_insert_suborder', [$this, 'on_suborder_created_in_multi_order'], 999, 2);
        
	}
	
	function on_order_created($order){
		
		$msg_obj	= new WOOCONVO_Order($order->get_id());
		//FILTER: wooconvo_notice_message_order_change
		$notice = __("Order placed", "wooconvo");
		$notice = apply_filters('wooconvo_notice_order_created', $notice, $order->get_id());
		$type	= 'order_created';
        $thread = $msg_obj->add_notice($notice, $type);
		
	}
	
	function on_order_status_change($order_id, $old_status, $new_status)
	{
		
		$msg_obj	= new WOOCONVO_Order($order_id);
		//FILTER: wooconvo_notice_message_order_change
		$notice 	= sprintf(__("Order status changed %s", "wooconvo"), $new_status);
		$notice 	= apply_filters('wooconvo_notice_message_order_change', $notice, $order_id);
		$type		= 'order_status_change';
        $thread 	= $msg_obj->add_notice($notice, $type);
        // exit;
	}
	
	public function on_new_order_note($arg){
		
		$order_id = $arg['order_id'];
		$customer_note = $arg['customer_note'];
		
		$msg_obj	= new WOOCONVO_Order($order_id);
		//FILTER: wooconvo_notice_message_order_change
		$notice = sprintf(__("Note: %s", "wooconvo"), $customer_note);
		$notice 	= apply_filters('wooconvo_notice_message_order_change', $notice, $order_id);
		$type = 'order_note';
        $thread = $msg_obj->add_notice($notice, $type);
		
	}
	
	
	// trigger notifications email
	function trigger_notification_emails($user, $context, $message, $attachments, $thread, $order_id){
		
		$user_type = wooconvo_get_member_type_by_context($context);
		// if type is customer trigger email for vendor and vice versa
		$user_type = $user_type === 'customer' ? 'vendor' : 'customer';
		
		// wooconvo_logger("wooconvo_email_action_{$user_type}");
		do_action("wooconvo_email_action_{$user_type}", $user, $context, $message, $attachments, $order_id);
	}
	
    // Add new query var
    function wc_account_query_vars( $vars ) {
        $vars[] = 'wooconvo-messages';
        return $vars;
    }
    
    /**
     * Insert the new endpoint into the My Account menu
     */
    function wc_account_menu_items( $items ) {
    	
    	$menu_label = wooconvo_get_option('myaccount_tab_label', 'Messages');
        $menu_label = sprintf(__("%s", 'wooconvo'), $menu_label);
        // $menu_label = $menu_label.' <span class="wooconvo-badge">3</span>';
        $menu['wooconvo-messages'] = htmlentities($menu_label);
        // inserting our menu in third position	
        $items = array_slice($items, 0, 2, true) + $menu + array_slice($items, 2, count($items) - 1, true) ;
    	
    	return $items;
    }
    
    /**
     * adding class to nav item in my account
     */
    function wc_account_menu_items_class( $classes, $endpoint ) {
    	
    	if( "wooconvo-messages" !== $endpoint ) return $classes;
    	
    	$user_id = get_current_user_id();
    	
    	$user_type = "customer";
    	$orders = wooconvo_get_orders($user_id);
    	
    	$unread_message = 0;
        foreach($orders as $order){
            
            $msg_obj = new WOOCONVO_Order($order->ID);
            $unread_message += $msg_obj->unread_customer;
        }
        
        // wooconvo_logger($unread_message);
        
    	
    	if( 0 < $unread_message ){
    		$classes[] = 'wooconvo-myaccount-new';
    	}
    	
    	return $classes;
    }
    
    /**
     * Add content to the new tab
     */
    function menu_content() {
    	
    	$user_id = get_current_user_id();
    	
    	$wooconvo_data = [
    					'is_pro'		=> defined('WOOCONVO_VERSION_PRO'),
    					'plugin_url'	=> WOOCONVO8_URL,
    					'user_id'		=> $user_id,
						'api_url'		=> get_rest_url(null, 'wooconvo/v1'),
						'context'		=> "myaccount",
						'settings'      => wooconvo_get_settings(),
						];
						
						
		self::load_scripts($wooconvo_data);
		
	    echo apply_filters('orderconvo_root', '<div id="orderconvo_root"></div>');
    }
    
    public static function load_scripts($wooconvo_data){
    	
    	$wooconvo_data = apply_filters('wooconvo_react_data', $wooconvo_data);
    	
    	echo '<script>window.WOOCONVO_Data=\''.addslashes(json_encode($wooconvo_data)).'\';</script>';
        
        $react_js  = WOOCONVO8_URL.'/assets/react/static/js/main.25ecdaeb.js';
        $react_css = WOOCONVO8_URL.'/assets/react/static/css/main.930edd9b.css';
        
        wp_enqueue_style('orderconvo-react-css', $react_css);
        wp_enqueue_script('orderconvo-react-js', $react_js, [], WOOCONVO8_VERSION, true );
        
        wp_enqueue_style('orderconvo-css', WOOCONVO8_URL.'/assets/css/wooconvo.css');
    }
    
    
    function add_nonce_to_wooconvo_data($wooconvo_data){
    	
    	$wooconvo_data['wooconvo_rest_nonce'] = wp_create_nonce('wp_rest');
    	// wooconvo_logger($wooconvo_data);
    	return $wooconvo_data;
    }
    
    function check_addons_enability($settings){
    	
    	if(!defined('WOOCONVO_PRO_PATH_PRO')){
    		$settings['enable_file_attachments'] = false;
    	}
    	if(!defined('WOOCONVO_QM_PATH')){
    		$settings['enable_quickreply'] = false;
    	}
    	if(!defined('WOOCONVO_REV_PATH')){
    		$settings['enable_revisions'] = false;
    	}
    	if(!defined('WOOCONVO_AM_PATH')){
    		$settings['enable_aws'] = false;
    	}
    	if(!defined('WOOCONVO_MP_PLUGIN_PATH')){
    		$settings['wooconvo_marketplace'] = "none";
    	}
    	if(!defined('WOOCONVO_CHAT_PLUGIN_PATH')){
    		$settings['enable_livechat'] = false;
    	}
    	
    	return $settings;
    	
    }
    
    function on_suborder_created_in_multi_order($suborder_id, $order_id){
		
		$msg_obj	= new WOOCONVO_Order($suborder_id);
		
		// delete the copied meta first
		delete_post_meta($suborder_id , 'wooconvo_thread');
		
		//FILTER: wooconvo_notice_message_order_change
		$notice = __("Order placed", "wooconvo");
		$notice = apply_filters('wooconvo_notice_order_created', $notice, $suborder_id);
		$type	= 'order_created';
        $thread = $msg_obj->add_notice($notice, $type);
		
	}
    	
	
}

function init_wooconvo_main(){
	return WOOCONVO_Main::__instance();
}