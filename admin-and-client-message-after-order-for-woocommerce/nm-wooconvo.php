<?php
/**
 * Plugin Name:       OrderConvo - Vendor & Customer Messages After Orders
 * Plugin URI:        http://www.najeebmedia.com
 * Description:       Order chat, messages and file sharing in WooCommerce.
 * Author:            N-Media
 * Author URI:        https://najeebmedia.com/
 * License:           GPL v2 or later
 * Version:           8.5
 * Text Domain:       wooconvo
 */
 
define('WOOCONVO8_PATH', untrailingslashit(plugin_dir_path( __FILE__ )) );
define('WOOCONVO8_URL', untrailingslashit(plugin_dir_url( __FILE__ )) );
define('WOOCONVO8_VERSION', '8.5' );
define('WOOCONVO8_SHORTNAME', 'wooconvo' );

include_once WOOCONVO8_PATH.'/includes/helper_functions.php';
include_once WOOCONVO8_PATH.'/includes/meta.json.php';
include_once WOOCONVO8_PATH.'/includes/migration.class.php';
include_once WOOCONVO8_PATH.'/includes/order.class.php';
include_once WOOCONVO8_PATH.'/includes/wooconvo.class.php';
include_once WOOCONVO8_PATH.'/includes/field.class.php';
include_once WOOCONVO8_PATH.'/includes/wprest.class.php';
include_once WOOCONVO8_PATH.'/includes/admin.class.php';

     
function wooconvo_init(){
    
    $migrate = init_wooconvo_migration();
    
    $migrate->migrate_threads();
    
    
    init_wooconvo_wp_rest();
	init_wooconvo_main();
	init_wooconvo_admin();
	
}


add_action('init', 'wooconvo_init');