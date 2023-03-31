<?php
/**
 * WooConvo Orders Class
 * Singlton class to handle:
 * add_message(thread[])
 * get_new_message() return message[]
 * set_unread_count_by_user_type() // order meta key {wooconvo_unread_$type + 1}
 */

class WOOCONVO_Order {
    
    
    // class properties
    protected static $orderid;
    
    protected static $order_meta;
    
    function __construct( $order_id=null ) {
        
        self::$orderid = $order_id;
        
        // getting all meta at once
        self::$order_meta = get_post_meta($order_id);
        
        $this->thread           = $this->get_meta_by_key('wooconvo_thread');
        $this->is_starred       = (int)$this->get_meta_by_key('wooconvo_starred');
        $this->unread_vendor    = (int)$this->get_meta_by_key('wooconvo_unread_vendor');
        $this->unread_customer  = (int)$this->get_meta_by_key('wooconvo_unread_customer');
        // revision addon
        $this->revisions_limit  = (int)$this->get_meta_by_key('wooconvo_revisions_limit');
        $this->order_date       = $this->get_meta_by_key('_completed_date');
        $this->first_name       = $this->get_meta_by_key('_billing_first_name');
        $this->last_name        = $this->get_meta_by_key('_billing_last_name');
        $this->order_id         = $order_id;
    }
    
    // return order info and thread
    public function get() {
        return ['order'             => $this->order, 
                'thread'            => $this->thread,
                'is_starred'        => $this->is_starred,
                'unread_vendor'     => $this->unread_vendor,
                'unread_customer'   => $this->unread_customer,
                ''
                ];
    }
    
    // this is the message sent by user
    public function add_message($user_id, $message, $attachments, $context){
        
        // wp_send_json_success
        
        $user       = get_userdata($user_id);
        $user_type  = wooconvo_get_member_type_by_context($context);
        
        $display_name   = !$user->display_name ? $user->user_login : $user->display_name;
        $first_name     = !$user->first_name ? $user->user_login : $user->first_name;
        $last_name      = !$user->last_name ? $user->user_login : $user->last_name;
        
        $thread = [ 'user_id'   => $user->ID,
                    'user_name' => $display_name,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'user'      => ['data'=>$user->data,'roles'=>$user->roles],
                    'message'   => sanitize_textarea_field($message),
                    'date'      => date('Y-m-d H:i:s'),
                    'attachments'=> $attachments,
                    'status'    => 'new',
                    'type'      => 'message',
                    'user_type' => $user_type,
                    'context'  => $context,
                  ];
        
        $this->add_thread($thread);
        $this->set_unread_count_by_user_type(1, $user_type);
        
        do_action('wooconvo_after_message_added', $user, $context, $message, $attachments, $thread, self::$orderid);
        
        return $this;
    }
    
    // add notices like order placed, or any notice added in orders
    public function add_notice($message, $type){
        
        $thread = [ 'message'   => sanitize_text_field($message),
                    'date'      => date('Y-m-d H:i:s'),
                    'status'    => 'new',
                    'type'      => $type,
                  ];
        // wooconvo_logger($thread);
        $this->add_thread($thread);
        return $this;
        
    }
    
    // adding thread into order meta
    private function add_thread($thread){
        
        // DEBUGGIN
        // delete_post_meta(self::$orderid , 'wooconvo_thread');
        
        // FILTER: wooconvo_new_message($thread, $order_id)
        $thread = apply_filters('wooconvo_new_message_thread', $thread, self::$orderid);
        // existing thread
        $existing_thread = get_post_meta(self::$orderid , 'wooconvo_thread', true);
        
        $wooconvo_thread = [];
        if( ! $existing_thread ){
            $wooconvo_thread = [$thread];
        }else{
            $wooconvo_thread = [...$existing_thread, $thread];
        }
        
        // wooconvo_logger(self::$orderid);  
        // wooconvo_logger($wooconvo_thread);  
        $this->thread = $wooconvo_thread;
        update_post_meta(self::$orderid , 'wooconvo_thread', $wooconvo_thread);
        return $this;
    }

    
    // increase unread count by user type
    public function set_unread_count_by_user_type($count, $type){
        
        // if sentby by type=customer then set undread for wooconvo_unread_vendor
        // and vice versa
        $type = $type == 'customer' ? 'vendor' : 'customer';
        $unread_key = "wooconvo_unread_{$type}";
        
        $unread = $type === 'vendor' ? intval($this->unread_vendor) : intval($this->unread_customer);
        
        if( ! $unread ){
            $unread = $count;
        }else{
            $unread += $count;
        }
        
        if( $type === 'vendor' ){
            $this->unread_vendor = $unread;
        }else{
            $this->unread_customer = $unread;
        }
        
        update_post_meta(self::$orderid , $unread_key, $unread);
        return $this;
    }
    
    // reset unread count if read by user_type (mark as all read)
    function reset_unread($user_type){
        
        $unread = 0;
        if( $type === 'vendor' ){
            $this->unread_vendor = $unread;
        }else{
            $this->unread_customer = $unread;
        }
        
        $unread_key = "wooconvo_unread_{$user_type}";
        update_post_meta(self::$orderid , $unread_key, $unread);
        return $this;
    }
    
    
    // set order as starred by admin/vendor
    public function set_starred(){
        update_post_meta(self::$orderid , "wooconvo_starred", 1);
        $this->is_starred = 1;
    }
    
    // et order as un-starred by admin/vendor
    public function set_unstarred(){
        update_post_meta(self::$orderid , "wooconvo_starred", 0);
        $this->is_starred = 0;
    }
    
    
    // get post meta by key
    private function get_meta_by_key($key, $single=false){
        
        if( !isset(self::$order_meta[$key]) ) return null;
        return maybe_unserialize( self::$order_meta[$key][0] );
    }
    
}