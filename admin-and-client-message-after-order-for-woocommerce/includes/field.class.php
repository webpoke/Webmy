<?php
/**
 * WCForce Extra Field Class
 * Singlton class to hanle:
 * add_field(title, meta[], settings=[]);
 * delete_field(id)
 */

class WCForce_Field {
    
    
    // class properties
    protected static $id;
    
    function __construct( $id=null ) {
        
        if( !$id ) return $this->init_post();
        
        self::$id = $id;
        
        $this->post_type = apply_filters('wcfoce_eo_post_type', 'wcfoce_eo');
        
        $this->title    = self::get_title();
        $this->meta     = self::get_meta();
        $this->settings = self::get_settings();
    }
    
    // initing a new field post
    private function init_post(){
        
        $user   = wp_get_current_user();
        $args = array(
          'post_title'      => __("__INIT_POST__","wcfoce_eo"),
          'post_status'     => 'draft',
          'post_author'     => $user->ID,
          'post_type'       => 'wcfoce_eo'
        );
        
        $return_err_on_fail = true;
        $fire_after_hooks   = false;
        self::$id = wp_insert_post($args, $return_err_on_fail);
    }
    
    // adding new field
    public function add_field($title, $meta, $settings=null){
        
        $user   = wp_get_current_user();
        $args = array(
            'ID'        => self::$id,
            'post_title'    => wp_strip_all_tags( $title ),
            'post_status'   => 'publish',
        );
        
        update_post_meta(self::$id, 'meta', $meta);
        if($settings) {
            update_post_meta(self::$id, 'settings', $settings);
        }
        
        $return_err_on_fail = true;
        $fire_after_hooks   = false;
        $response = wp_update_post($args, $return_err_on_fail, $fire_after_hooks);
        
        if( ! is_wp_error($response) ) {
            return new WCForce_Field(self::$id);
        }
        
        
    }
    
}