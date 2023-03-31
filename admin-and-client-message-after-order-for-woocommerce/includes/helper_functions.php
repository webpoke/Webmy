<?php
/**
 * Helper Function
 * Date Created: Oct 1, 2022
 * */
 

// function wooconvo_pa($info){
//     echo '<pre>';
//     print_r($info);
//     echo '</pre>';
// }

function wooconvo_load_file($file_name, $vars=null) {
         
   if( is_array($vars))
    extract( $vars );
    
   $file_path =  WOOCONVO_PATH . '/templates/'.$file_name;
   if( file_exists($file_path))
   	include ($file_path);
   else
   	die('File not found'.$file_path);
}

function wooconvo_logger($msg){
    wc_get_logger()->debug( wc_print_r( $msg, true ), array( 'source' => 'WooConvo' ) );
}


function wooconvo_get_all_order_statuses() {
    
  $order_statuses = wc_get_order_statuses();
  return $order_statuses;
}

// get member type based on context
// myaccount,wp_admin,yith_vendor,dokan_vendor
function wooconvo_get_member_type_by_context($context){
    
    $member_type = $context === 'myaccount' ? 'customer' : 'vendor';
    // FILTER: wooconvo_get_member_type_by_context
    return apply_filters('wooconvo_get_member_type_by_context', $member_type, $context);
}

// get all unred orders by meta query
// $user_type: vendor or customer
function wooconvo_get_unread_orders($user_type, $user_id=null){
    
    $order_statuses = array_keys(wooconvo_get_all_order_statuses());
    $args = array(
        'post_type' => 'shop_order',
        'post_status'  => $order_statuses,
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => "wooconvo_unread_{$user_type}",
                'compare' => '>=',
                'value' => 1
            ),
        )
    );
    
    if($user_id){
        $args['meta_query'][] = ['key'=>'_customer_user','value'=>intval($user_id),'compare'=>'='];
    }
    
    $orders  = new WP_Query( $args );
    $orders = $orders->get_posts();
	
	return $orders;
}

// get all wooconvo orders by meta query
function wooconvo_get_orders($user_id=null){
    
    $order_statuses = array_keys(wooconvo_get_all_order_statuses());
    $args = array(
        'post_type' => 'shop_order',
        'post_status'  => $order_statuses,
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'wooconvo_thread',
                'compare' => 'EXISTS'
            ),
        )
    );
    
    if($user_id){
        $args['meta_query'][] = ['key'=>'_customer_user','value'=>intval($user_id),'compare'=>'='];
    }
    
    $orders  = new WP_Query( $args );
    $orders = $orders->get_posts();
	
	$orders = apply_filters('wooconvo_get_orders_query', $orders, $user_id);
	
	return $orders;
}

// return dirname
function wooconvo_get_dirname(){
    return apply_filters('wooconvo_upload_dirname', 'wooconvo');
}

// setup file upload directory
function wooconvo_file_directory($sub_dir=null){

	$upload_dir = wp_upload_dir ();
	$wooconvo_dir = wooconvo_get_dirname();
	
	$parent_dir = $upload_dir ['basedir'] . '/' . $wooconvo_dir . '/';
	$thumb_dir  = $parent_dir . 'thumbs/';
	
	wp_mkdir_p($parent_dir);
	wp_mkdir_p($thumb_dir);
	if($sub_dir){
	    $sub_dir = $parent_dir . $sub_dir . '/';
		if(wp_mkdir_p($sub_dir)){
			return $sub_dir;
		}
	}
	return $parent_dir;
}

// get file url
function wooconvo_get_dir_url( $thumb=false ) {
	
	$upload_dir = wp_upload_dir ();
	$wooconvo_dir = wooconvo_get_dirname();
	$return_url = '';
	if ( $thumb ) {
		$return_url = $upload_dir ['baseurl'] . '/' . $wooconvo_dir . '/thumbs/';
	}	else {
		$return_url = $upload_dir ['baseurl'] . '/' . $wooconvo_dir . '/';
	}
		
	return apply_filters('ppom_dir_url', set_url_scheme( $return_url ));
}

// if file is image
function wooconvo_is_image( $file_name ){
	
	$type = strtolower ( substr ( strrchr ( $file_name , '.' ), 1 ) );
	if (($type == "gif") || ($type == "jpeg") || ($type == "png") || ($type == "pjpeg") || ($type == "jpg"))
		return true;
	else
		return false;
}

// create thumb from
function wooconvo_create_image_thumb( $file_path, $image_name, $thumb_size ) {
    
    $thumb_size = intval($thumb_size);
    $wp_image = wp_get_image_editor ( $file_path . $image_name );
    $image_destination = wooconvo_file_directory() . 'thumbs/' . $image_name;
    
    $image_name = apply_filters('wooconvo_thumb_image', true, $image_name);
    
    if (! is_wp_error ( $wp_image )) {
        $crop = apply_filters('wooconvo_crop_thumb', true);
    	$wp_image -> resize ( $thumb_size, $thumb_size, $image_name, $crop );
    	$wp_image -> save ( $image_destination );
    }
    
    return $image_destination;
}

// create thumbnail from data_url (used for aws)
function wooconvo_create_image_thumb_from_dataurl($image_name, $file_data, $thumb_size) {
    	
	if( isset($file_data) ) {
		
		$image = imagecreatefromstring( file_get_contents($file_data) );
		
		$height = $thumb_size;
		$width	= $thumb_size;
		
		$height = $height == '' ? 150 : intval($height);
		$width	= $width  == '' ? 150 : intval($width);
		
		// calculate resized ratio
		// Note: if $height is set to TRUE then we automatically calculate the height based on the ratio
		$height = $height === true ? (ImageSY($image) * $width / ImageSX($image)) : $height;
		
		// create image 
		$output = ImageCreateTrueColor($width, $height);
		ImageCopyResampled($output, $image, 0, 0, 0, 0, $width, $height, ImageSX($image), ImageSY($image));
		
		$destination_path = wooconvo_file_directory() . 'thumbs/' . $image_name;
		// save image
		$result = ImageJPEG($output, $destination_path, 95);
	}
	
}

// get default thumbnail for files
function wooconvo_get_default_thumb($ext){
    if( file_exists(WOOCONVO8_PATH.'/images/ext/'.$ext.'.png') ) 
        return WOOCONVO8_URL.'/images/ext/'.$ext.'.png';
        
    return $default_thumb = WOOCONVO8_URL.'/images/ext/_blank.png';
}

// function to check if given feature is allowed or not
function wooconvo_is_feature_allowed($feature){
    
    $return = false;
    
    switch($feature){
        case 'email_notification':
            $return = true;
    }
    
    return apply_filters('wooconvo_is_feature_allowed', $return, $feature);
}

// saving wooconvo settings inside an option_meta
function wooconvo_save_settings($settings){
    // messages
    $settings['wooconvo_button_text'] = sanitize_text_field(addslashes($settings['wooconvo_button_text']));
    $settings['wooconvo_upload_text'] = sanitize_text_field(addslashes($settings['wooconvo_upload_text']));
    $settings['revisions_note'] = sanitize_text_field(addslashes($settings['revisions_note']));
    $settings['myaccount_tab_label'] = sanitize_text_field(addslashes($settings['myaccount_tab_label']));
    $settings['quick_replies'] = array_map('sanitize_text_field', $settings['quick_replies']);
    
    $settings = apply_filters('wooconvo_save_settings', $settings);
    update_option('wooconvo_settings', $settings);
}

// getting wooconvo settings inside an option_meta
function wooconvo_get_settings(){
    $settings = get_option('wooconvo_settings');
    $settings['wooconvo_button_text'] = stripslashes_deep($settings['wooconvo_button_text']);
    $settings['wooconvo_upload_text'] = stripslashes_deep($settings['wooconvo_upload_text']);
    $settings['revisions_note'] = stripslashes_deep($settings['revisions_note']);
    $settings['myaccount_tab_label'] = stripslashes_deep($settings['myaccount_tab_label']);
    if( isset($settings['quick_replies']) && is_valid($settings['quick_replies']) ){
        $settings['quick_replies'] = array_map('stripslashes_deep', $settings['quick_replies']);
    }
    return apply_filters('wooconvo_get_settings', $settings);
}

// get single setting option
function wooconvo_get_option($key, $default=null){
    
    $settings = wooconvo_get_settings();
    $value = isset($settings[$key]) && !empty($settings[$key])  ? $settings[$key] : $default;
    return $value;
}

// get vendor email by context
function wooconvo_get_vendor_email($context, $order_id){
    
    $vendor_email = '';
    
    if( function_exists('yith_get_vendor') ){
        $vendor = yith_get_vendor($order_id, 'order');
        // wooconvo_logger($vendor);
		if($vendor->is_valid()){
			$vendor_email = $vendor->store_email;
		}
    } 
    // else if( function_exists('is_user_wcmp_vendor') && is_user_wcmp_vendor($user_id) ) {
    //     $vendor = get_wcmp_vendor($user_id);
    //     $vendor_email = $vendor->user_data->user_email;
    // }
    
    switch($context){
        
        case 'wp_admin':
            $vendor_email = get_bloginfo('admin_email');
            break;
        case 'yith_vendor':
            if( function_exists('yith_get_vendor') ) {
                $vendor = yith_get_vendor($order_id, 'order');
                wooconvo_logger($vendor);
        		if($vendor->is_valid()){
        			$vendor_email = $vendor->store_email;
        		}
            }
            break;
    }
    
    return apply_filters('wooconvo_get_vendor_email', $vendor_email, $context, $order_id);
}