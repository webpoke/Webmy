<?php
/**
 * Rest API Handling
 * 
 * */

if( ! defined('ABSPATH') ) die('Not Allowed.');


class WOOCONVO_WP_REST {
	
	private static $ins = null;
	
	public static function __instance()
	{
		// create a new object if it doesn't exist.
		is_null(self::$ins) && self::$ins = new self;
		return self::$ins;
	}
	
	public function __construct() {
		
		add_action( 'rest_api_init', function()
            {
                header( "Access-Control-Allow-Origin: *" );
            }
        );
		
		add_action( 'rest_api_init', [$this, 'init_api'] ); // endpoint url

	}
	
	
	function init_api() {
	    
	    foreach(wooconvo_get_rest_endpoints() as $endpoint) {
	        
            register_rest_route('wooconvo/v1', $endpoint['slug'], array(
                'methods' => $endpoint['method'],
                'callback' => [$this, $endpoint['callback']],
                'permission_callback' => "__return_true",
    	    
            ));
	    }
        
    }
    
    // validate request
    // function permission_check($request){
        
    //     // wooconvo_logger($request->get_header('X-WP-Nonce'));
    //     // No need, already verified.
    // }
    
    // saving settings
    function save_settings($request){
        if( ! $request->sanitize_params() ) {
            wp_send_json_error( ['message'=>$request->get_error_message()] );
        }
        
        $data   = $request->get_params();
        // wooconvo_logger($data);
        wooconvo_save_settings($data);
        wp_send_json_success($data);
    }
    
    // get settings
    function get_settings($request){
        if( ! $request->sanitize_params() ) {
            wp_send_json_error( ['message'=>$request->get_error_message()] );
        }
        
        $settings = wooconvo_get_settings();
        wp_send_json_success($settings);
    }
    
    function get_meta($request){
        
        if( ! $request->sanitize_params() ) {
            wp_send_json_error( ['message'=>$request->get_error_message()] );
        }
        
        $meta = wooconvo_get_settings_meta();
        wp_send_json_success( json_encode($meta) );
    }
    
    // sending a message
    function send_message($request){
        
        if( ! $request->sanitize_params() ) {
            wp_send_json_error( ['message'=>$request->get_error_message()] );
        }
        
        
        $data   = $request->get_params();
        extract($data);
        $attachments = !$attachments ? [] : $attachments;
        
        $msg_obj = new WOOCONVO_Order($order_id);
        $order = $msg_obj->add_message($user_id, $message, $attachments, $context);
        
        wp_send_json_success($order);
    }
    
    // get order details by order_id
    function get_order_by_id($request) {
        
        if( ! $request->sanitize_params() ) {
            wp_send_json_error( ['message'=>$request->get_error_message()] );
        }
        
        $data   = $request->get_params();
        extract($data);
        
        $wooconvo_order = new WOOCONVO_Order($order_id);
        
        wp_send_json_success(apply_filters('wooconvo_get_order_by_id', $wooconvo_order, $order_id));
    }
    
    
    
    // set order starred by vendor
    function set_order_starred($request){
        
        if( ! $request->sanitize_params() ) {
            wp_send_json_error( ['message'=>$request->get_error_message()] );
        }
        
        $data   = $request->get_params();
        extract($data);
        
        $msg_obj = new WOOCONVO_Order($order_id);
        $msg_obj->set_starred();
        
        wp_send_json_success($msg_obj);
    }
    
    // set order un-starred by vendor
    function set_order_unstarred($request){
        
        if( ! $request->sanitize_params() ) {
            wp_send_json_error( ['message'=>$request->get_error_message()] );
        }
        
        $data   = $request->get_params();
        extract($data);
        
        $msg_obj = new WOOCONVO_Order($order_id);
        $msg_obj->set_unstarred();
        wp_send_json_success($msg_obj);
    }
    
    // get all orders with wooconvo threads attached
    function get_orders($request){
        
        if( ! $request->sanitize_params() ) {
            wp_send_json_error( ['message'=>$request->get_error_message()] );
        }
        
        $data   = $request->get_params();
        extract($data);
        
        $orders = [];
        if( $context === 'myaccount' && isset($user_id) ) {
            $orders = wooconvo_get_orders($user_id);
        } else if( $context === 'wp_admin' ){
            $orders = wooconvo_get_orders();
        } else if( $context === 'yith_vendor' && class_exists('WOOCONVO_Addon_YithMultiVendor') ){
            $orders = WOOCONVO_Addon_YithMultiVendor::get_orders($user_id);
        }else if( $context === 'dokan_vendor' ){
            $orders = WOOCONVO_Addon_DokanMultiVendor::get_orders($user_id);
        }else if( $context === 'wcvendors_vendor' ){
            $orders = WOOCONVO_Addon_WCVendorsMultiVendor::get_orders($user_id);
        }else if( $context === 'multivendorx_vendor' ){
            $orders = WOOCONVO_Addon_MultivendorXMultiVendor::get_orders($user_id);
        }
        
        // converting orders to WOOCONVO_Order
        $orders = array_map(function($order){
            $order_obj = new WOOCONVO_Order($order->ID);
            $order_obj->status = $order->post_status;
            return $order_obj;
        }, $orders);
        
        wp_send_json_success($orders);
    }
    
    // get undread of order by user type
    // user_type: vendor, customer
    function get_unread_orders($request){
        
        if( ! $request->sanitize_params() ) {
            wp_send_json_error( ['message'=>$request->get_error_message()] );
        }
        
        $data   = $request->get_params();
        extract($data);
        $user_type = isset($user_type) ? $user_type : 'vendor';
        
        $orders = wooconvo_get_unread_orders($user_type);
        // converting orders to WOOCONVO_Order
        $orders = array_map(function($order){
            return new WOOCONVO_Order($order->ID);
        }, $orders);
        
        wp_send_json_success($orders);
    }
    
    function reset_unread($request){
        
        if( ! $request->sanitize_params() ) {
            wp_send_json_error( ['message'=>$request->get_error_message()] );
        }
        
        $data   = $request->get_params();
        extract($data);
        $user_type = isset($user_type) ? $user_type : 'vendor';
        
        $msg_obj = new WOOCONVO_Order($order_id);
        $order = $msg_obj->reset_unread($user_type);
        wp_send_json_success($order);
        
    }
    
    
    // upload file(s)
    function upload_file($request){
        
        if( ! $request->sanitize_params() ) {
            wp_send_json_error( ['message'=>$request->get_error_message()] );
        }
        
        $data   = $request->get_params();
        extract($data);
        
        $dir_path = wooconvo_file_directory($order_id);
    	$file_tmp = $_FILES['file']['tmp_name'];
        
        // validate filetypes
        $file_name = sanitize_file_name( $_FILES['file']['name'] );
	
    	/* ========== Invalid File type checking ========== */
    	$extension = strtolower(end(explode('.',$file_name)));
    	
    	$default_restricted = 'php,php4,php5,php6,php7,phtml,exe,shtml';
    	$restricted_type = wooconvo_get_option('restricted_file_types', $default_restricted);
    	$restricted_type = explode(',', $restricted_type);
    	
    	if( in_array( strtolower($extension), $restricted_type) ){
    	    $message = __ ( 'File type not valid', 'wooconvo' );
    		wp_send_json_error( $message );
    	}
    	
    	// rename filename
        $file_name = apply_filters('wooconvo_filename', uniqid() . '.'.$extension);
    	
        $file = $dir_path . $file_name;
        move_uploaded_file($file_tmp, $file);
        
        
        $resp = ['filename' => $file_name,
                'is_image'=>false,
                'thumbnail'=>wooconvo_get_default_thumb($extension),
                'location' => 'local'];
        
        // creating thumb
        if( wooconvo_is_image( $file_name) ) {
            $thumb_size = apply_filters('wooconvo_thumb_size', 100);
            wooconvo_create_image_thumb($dir_path, $file_name, $thumb_size);
            $resp['is_image'] = true;
            $thumb_url = wooconvo_get_dir_url(true);
            $resp['thumbnail'] = $thumb_url . $file_name;
        }
        
        wp_send_json_success($resp);
    }
    
    // upload images thumbs for aws addon
    function upload_images_thumb($request){
        
        if( ! $request->sanitize_params() ) {
            wp_send_json_error( ['message'=>$request->get_error_message()] );
        }
        
        $data   = $request->get_params();
        extract($data);
        
        $file_name = sanitize_file_name( $file_name );
        $extension = strtolower(end(explode('.',$file_name)));  
        // wooconvo_logger($file_name);
    	
        $resp = ['filename' => $file_name,
                'is_image'=>false,
                'thumbnail'=>wooconvo_get_default_thumb($extension),
                'location' => 'aws',
                'key'       => $key,
                'bucket'    => $bucket,
                'region'    => $region
                ];
        if( wooconvo_is_image($file_name) ) {
            $thumb_size = apply_filters('wooconvo_thumb_size', 100);
            wooconvo_create_image_thumb_from_dataurl($file_name, $file_data, $thumb_size);
            $resp['is_image'] = true;
            $thumb_url = wooconvo_get_dir_url(true);
            $resp['thumbnail'] = $thumb_url . $file_name;
        }
        
        wp_send_json_success($resp);
    }
    
    // secure download file
    function download_file($request){
        if( ! $request->sanitize_params() ) {
            wp_send_json_error( ['message'=>$request->get_error_message()] );
        }
        
        $data   = $request->get_params();
        extract($data);
        
        $dir_path = wooconvo_file_directory($order_id);
        $file_path = $dir_path . $filename;
        
        
        if (file_exists($file_path)){

			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Content-Description: File Transfer');
			
			// if file to be opened in browser
			if( !wooconvo_get_option('image_open_click')){
			    header('Content-Disposition: attachment; filename='.basename($file_path));
			}
			
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Pragma: public');
			header('Content-Type: '.mime_content_type($file_path));
			header('Content-Length: ' . filesize($file_path));
			
			$chunksize = 1024 * 1024;

		    // Open Resume
		    $handle = @fopen($file_path, 'r');
		
		    if (false === $handle) {
		        return FALSE;
		    }
		
		    $output_resource = fopen( 'php://output', 'w' );
		    
		    while (!@feof($handle)) {
		        $content  = @fread($handle, $chunksize);
		        fwrite( $output_resource, $content );
		
		        if (ob_get_length()) {
		            ob_flush();
		            flush();
		        }
		    }
		
		    return @fclose($handle);
		}
    }
    
    
}

function init_wooconvo_wp_rest(){
	return WOOCONVO_WP_REST::__instance();
}