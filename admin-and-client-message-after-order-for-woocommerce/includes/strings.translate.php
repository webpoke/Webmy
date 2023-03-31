<?php
/**
 * Plugin strings localization in React App
 **/

function wooconvo_strings_translate() {
    
    $strings = ['__wc_orders'   => __('Orders', 'wooconvo'),
                '__wc_unread'   => __('Unread', 'wooconvo'),
                '__wc_starred'  => __('Starred', 'wooconvo'),
                '__wc_settings' => __('Settings', 'wooconvo'),
                '__wc_addons_settings'  => __('Addons Settings', 'wooconvo'),
                '__wc_search'   => __('Search', 'wooconvo'),
                '__wc_chip'     => __('Chip', 'wooconvo'),
               ];
                
    return apply_filters('wooconvo_strings_translate', $strings);
}