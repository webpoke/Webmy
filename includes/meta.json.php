<?php
/**
 * Plugin Meta Array
 * 
 * */

if( ! defined('ABSPATH') ) die('Not Allowed.');

// REST API
function wooconvo_get_rest_endpoints() {
    
    $endpionts =    [
                    ['slug'=>'get-admin-meta','callback'=>'get_meta','method'=>'GET'],
                    ['slug'=>'save-settings','callback'=>'save_settings','method'=>'POST'],
                    ['slug'=>'get-settings','callback'=>'get_settings','method'=>'GET'],
                    ['slug'=>'add-message','callback'=>'send_message','method'=>'POST'],
                    ['slug'=>'get-order-detail','callback'=>'get_order_by_id','method'=>'GET'],
                    ['slug'=>'reset-unread','callback'=>'reset_unread','method'=>'POST'],
                    ['slug'=>'set-order-starred','callback'=>'set_order_starred','method'=>'POST'],
                    ['slug'=>'set-order-unstarred','callback'=>'set_order_unstarred','method'=>'POST'],
                    ['slug'=>'get-orders','callback'=>'get_orders','method'=>'GET'],
                    ['slug'=>'get-unread-orders','callback'=>'get_unread_orders','method'=>'GET'],
                    ['slug'=>'upload-file','callback'=>'upload_file','method'=>'POST'],
                    ['slug'=>'download-file','callback'=>'download_file','method'=>'GET'],
                    ['slug'=>'upload-images-thumb','callback'=>'upload_images_thumb','method'=>'POST'],
                    ];
                    
    return apply_filters('wooconvo_rest_endpoints', $endpionts);
}


function wooconvo_get_settings_meta() {
    
    $settings_meta = [
            [
                'tab'       => __("General Settings","wooconvo"),
                'is_addon'  => false,
                'fields'    => [
                                    [
                                        'label'	    => __('Enable message count display', 'wooconvo'),
        						        'desc'		=> __('Show message count in the header', 'wooconvo'),
            						    'id'		=> 'enable_msg_count_display',
            						    'type'		=> 'boolean',
            						    'width'     => 4,
            						    'tab'       =>'general',
            						    'default'	=> TRUE,
            						    'is_pro'    => true
                                    ],
                                    [
                                        'label'	    => __('Enable message search', 'wooconvo'),
        						        'desc'		=> __('Show search input in the header', 'wooconvo'),
            						    'id'		=> 'enable_msg_search',
            						    'type'		=> 'boolean',
            						    'width'     => 4,
            						    'tab'       =>'general',
            						    'default'	=> TRUE,
            						    'is_pro'    => true
                                    ],
                                    [
                                        'label'	    => __('Enable order notices', 'wooconvo'),
        						        'desc'		=> __('Show order notice messages in the conversation', 'wooconvo'),
            						    'id'		=> 'enable_order_notices',
            						    'type'		=> 'boolean',
            						    'width'     => 4,
            						    'tab'       =>'general',
            						    'default'	=> TRUE,
            						    'is_pro'    => false
                                    ],
                                    [
                                        'label'	    => __('Reverse message display order', 'wooconvo'),
        						        'desc'		=> __('Last message will be display at top', 'wooconvo'),
            						    'id'		=> 'reverse_message_display_order',
            						    'type'		=> 'boolean',
            						    'width'     => 4,
            						    'tab'       =>'general',
            						    'default'	=> false,
            						    'is_pro'    => false
                                    ],
                                    [
                                        'label'	    => __('Disable communication when completed', 'wooconvo'),
        						        'desc'		=> __('Disable communication when order status completed (wc-completed)', 'wooconvo'),
            						    'id'		=> 'disable_on_completed',
            						    'type'		=> 'boolean',
            						    'width'     => 4,
            						    'tab'       =>'general',
            						    'default'	=> false,
            						    'is_pro'    => false
                                    ],
                                    [
                                        'label'	    => __('Show only firstname?.', 'wooconvo'),
        						        'desc'		=> __('In message user full name is display by default.', 'wooconvo'),
            						    'id'		=> 'firstname_display',
            						    'type'		=> 'boolean',
            						    'width'     => 4,
            						    'tab'       =>'general',
            						    'default'	=> false,
            						    'is_pro'    => false
                                    ],
                                    [
                                        'label'	    => __('My account tab label', 'wooconvo'),
        						        'desc'		=> __('Tab label inside my account for messages', 'wooconvo'),
            						    'id'		=> 'myaccount_tab_label',
            						    'type'		=> 'text',
            						    'width'     => 4,
            						    'tab'       =>'general',
            						    'default'	=> 'Messages',
            						    'is_pro'    => false
                                    ],
                                    [
                                        'label'	    => __('Show textarea to reply message', 'wooconvo'),
        						        'desc'		=> __('Instead a single line text input show multiline box', 'wooconvo'),
            						    'id'		=> 'show_textarea_reply',
            						    'type'		=> 'boolean',
            						    'width'     => 4,
            						    'tab'       =>'general',
            						    'is_pro'    => false
                                    ],
                                ]
            ],
            [
                'tab'       => __("Attachments Settings","wooconvo"),
                'is_addon'  => false,
                'fields'    => [
                                    [
                                        'label'	    => __('Enable file attachments', 'wooconvo'),
        						        'desc'		=> __('', 'wooconvo'),
            						    'id'		=> 'enable_file_attachments',
            						    'type'		=> 'boolean',
            						    'width'     => 12,
            						    'tab'       =>'Attachments',
            						    'default'	=> true,
            						    'is_pro'    => true
                                    ],
                                    [
                                        'label'	    => __('Maximum files allowed', 'wooconvo'),
        						        'desc'		=> __('Max. files allowed per message (e.g 1)', 'wooconvo'),
            						    'id'		=> 'max_files_allowed',
            						    'type'		=> 'text',
            						    'width'     => 4,
            						    'tab'       =>'Attachments',
            						    'default'	=> 1,
            						    'is_pro'    => true
                                    ],
                                     [
                                        'label'	    => __('Maximum file size', 'wooconvo'),
        						        'desc'		=> __('Max. file size to upload in kb (e.g 200)', 'wooconvo'),
            						    'id'		=> 'max_file_size',
            						    'type'		=> 'text',
            						    'width'     => 4,
            						    'tab'       =>'Attachments',
            						    'default'	=> 200,
            						    'is_pro'    => true
                                    ],
                                    [
                                        'label'	    => __('File types allowed', 'wooconvo'),
        						        'desc'		=> __('File types separated by comma, (e.g jpg,png,pdf)', 'wooconvo'),
            						    'id'		=> 'file_types_allowed',
            						    'type'		=> 'text',
            						    'width'     => 4,
            						    'tab'       =>'Attachments',
            						    'default'	=> 'jpg,png,gif,pdf',
            						    'is_pro'    => true
                                    ],
                                    [
                                        'label'	    => __('Restricted file types', 'wooconvo'),
        						        'desc'		=> __('For security (e.g php,exe)', 'wooconvo'),
            						    'id'		=> 'restricted_file_types',
            						    'type'		=> 'text',
            						    'width'     => 4,
            						    'tab'       =>'Attachments',
            						    'default'	=> 'php,php4,php5,php6,php7,phtml,exe,shtml',
            						    'is_pro'    => true
                                    ],
                                    [
                                        'label'	    => __('File thumb size', 'wooconvo'),
        						        'desc'		=> __('Image thumb size for preview (e.g 200)', 'wooconvo'),
            						    'id'		=> 'thumb_size',
            						    'type'		=> 'text',
            						    'width'     => 4,
            						    'tab'       =>'Attachments',
            						    'default'	=> '100',
            						    'is_pro'    => true
                                    ],
                                    [
                                        'label'	    => __('Attachment are required?', 'wooconvo'),
        						        'desc'		=> __('', 'wooconvo'),
            						    'id'		=> 'attachments_required',
            						    'type'		=> 'boolean',
            						    'width'     => 4,
            						    'tab'       =>'Attachments',
            						    'default'	=> true,
            						    'is_pro'    => true
                                    ],
                                    [
                                        'label'	    => __('Send attachments in email', 'wooconvo'),
        						        'desc'		=> __('Send attached files in email notification', 'wooconvo'),
            						    'id'		=> 'attachments_in_email',
            						    'type'		=> 'boolean',
            						    'width'     => 4,
            						    'tab'       =>'Attachments',
            						    'default'	=> true,
            						    'is_pro'    => true
                                    ],
                                    [
                                        'label'	    => __('Image open in new tab?', 'wooconvo'),
        						        'desc'		=> __('Image will be opened in new tab instead download', 'wooconvo'),
            						    'id'		=> 'image_open_click',
            						    'type'		=> 'boolean',
            						    'width'     => 4,
            						    'tab'       =>'Attachments',
            						    'default'	=> true,
            						    'is_pro'    => true
                                    ],
                                  
                                ]
            ],
            [
                'tab'       => __("Design","wooconvo"),
                'is_addon'  => false,
                'fields'    => [
                                    [
                                        'label'	    => __('Top header BG color', 'wooconvo'),
        						        'desc'		=> __('Set BG color top header', 'wooconvo'),
            						    'id'		=> 'bg_color_top_header',
            						    'type'		=> 'color',
            						    'width'     => 4,
            						    'tab'       =>'design',
            						    'default'	=> '',
            						    'is_pro'    => true
                                    ],
                                    [
                                        'label'	    => __('Messag header BG color', 'wooconvo'),
        						        'desc'		=> __('Set BG color of header', 'wooconvo'),
            						    'id'		=> 'bg_color_message_header',
            						    'type'		=> 'color',
            						    'width'     => 4,
            						    'tab'       =>'design',
            						    'default'	=> '',
            						    'is_pro'    => true
                                    ],
                                    [
                                        'label'	    => __('Order notices BG color', 'wooconvo'),
        						        'desc'		=> __('Set BG color of order notice messages', 'wooconvo'),
            						    'id'		=> 'bg_color_order_notices',
            						    'type'		=> 'color',
            						    'width'     => 4,
            						    'tab'       =>'design',
            						    'default'	=> '',
            						    'is_pro'    => true
                                    ],
                                    [
                                        'label'	    => __('Message BG color', 'wooconvo'),
        						        'desc'		=> __('Set BG color of order messages', 'wooconvo'),
            						    'id'		=> 'bg_color_order_messages',
            						    'type'		=> 'color',
            						    'width'     => 4,
            						    'tab'       =>'design',
            						    'default'	=> '',
            						    'is_pro'    => true
                                    ],
                                    [
                                        'label'	    => __('Upload button icon color', 'wooconvo'),
        						        'desc'		=> __('', 'wooconvo'),
            						    'id'		=> 'icon_color_upload_button',
            						    'type'		=> 'color',
            						    'width'     => 4,
            						    'tab'       =>'design',
            						    'default'	=> '',
            						    'is_pro'    => true
                                    ],
                                    [
                                        'label'	    => __('Send button icon color', 'wooconvo'),
        						        'desc'		=> __('', 'wooconvo'),
            						    'id'		=> 'icon_color_send_button',
            						    'type'		=> 'color',
            						    'width'     => 4,
            						    'tab'       =>'design',
            						    'default'	=> '',
            						    'is_pro'    => true
                                    ],
                                    
                                ]
            ]
        ];
        
    return apply_filters('wooconvo_settings_meta', $settings_meta);
}