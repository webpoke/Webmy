<?php
/**
 * Migration related stuff, check if old pro or addons are installed.
 * Created Date: November 2, 2022
 * Created By: Ben Rider
 * */
 
class WOOCONVO_Migration {
    
    private static $ins = null;
	
	public static function __instance()
	{
		// create a new object if it doesn't exist.
		is_null(self::$ins) && self::$ins = new self;
		return self::$ins;
	}
    
    function __construct(){
        
        // First thing is first.
        if( $this->is_old_pro_addon_version_installed() ) {
            add_action( 'admin_notices', array($this, 'show_admin_notice') );
            return '';
        }
    
    }
    
    public function migrate_threads(){
        
        if( ! $this->checkTableExists() ) return;
        
        $is_migrated = get_option('wooconvo_migrated');
        if( $is_migrated ) return;
        
        $results = $this -> getAllConversations();
        foreach($results as $result) {
            
            if( ! $this->checkOrderExists($result->order_id) ) continue;
            $order_id = $result->order_id;
            $threads = array();
            $threads_new = array();
            $threads = json_decode($result->convo_thread);
            foreach ($threads as $thread) {
                $threads_new = [...$threads_new, $this->createThreadArray($order_id, $thread)];
            }
            
            // remove nulled items
            $threads_new = array_filter($threads_new);
            
            // now update order meta
            update_post_meta($order_id , 'wooconvo_thread', $threads_new);
            update_post_meta($order_id, 'is_starred', 0);
            update_post_meta($order_id, 'wooconvo_unread_vendor', 0);
            update_post_meta($order_id, 'wooconvo_unread_customer', 0);
            
            // wooconvo_logger($threads_new);
        }
        
        update_option('wooconvo_migrated', 'done');
        
    }
    
    function getAllConversations() {
        global $wpdb;
        $table_name = $wpdb->prefix . "nm_wooconvo";
        $query = "SELECT * FROM $table_name";
        $results = $wpdb->get_results($query);
        return $results;
    }
    
    function createThreadArray($order_id, $thread) {
        
        $user = get_user_by('email', $thread->user);
        if( !$user ) return null;
        
        $context = 'myaccount';
        if (in_array('administrator', $user->roles)) {
           $context = 'wp_admin';
        }
        $user_type = wooconvo_get_member_type_by_context($context);
        $thread_new = array();
        $thread_new['user_id'] = $user->ID;
        $thread_new['user_name'] = $user->display_name;
        $thread_new['first_name'] = $user->first_name;
        $thread_new['last_name'] = $user->last_name;
        $thread_new['user'] = array('data' => $user->data, 'roles' => $user->roles);
        $thread_new['message'] = sanitize_text_field($thread->message);
        $thread_new['date'] = $thread->senton;
        $thread_new['attachments'] = $this->moveAttachments($thread->files,$order_id);
        $thread_new['status'] = 'new';
        $thread_new['type'] = 'message';
        $thread_new['user_type'] = $user_type;
        $thread_new['context'] = $context;
        return $thread_new;
    }
    
    function moveAttachments($attachments,$order_id){
        
        $new_attachments = array();
        
        $upload_dir = wp_upload_dir ();
        $old_path = $upload_dir ['basedir'] . '/order_files';
        $new_path = wooconvo_file_directory($order_id);
        // wooconvo_logger($old_path);
        foreach($attachments as $attachment){
            $old_path = "{$old_path}/".$attachment;
            $new_path = "{$new_path}".$attachment;
            if(file_exists($old_path) && copy($old_path,$new_path)){
                $old_path = $upload_dir ['basedir'] . '/order_files';
                $old_thumb_path = "{$old_path}/thumbs/".$attachment;
                $new_thumb_path = wooconvo_file_directory().'thumbs/'.$attachment;
                $thumbnail_url = '';
                if(file_exists($old_thumb_path) && copy($old_thumb_path,$new_thumb_path)){
                    $thumbnail_url = $upload_dir ['baseurl'] . '/wooconvo/thumbs/'.$attachment;
                }
                    
                $arr = ['filename'=>$attachment,'is_image'=>wooconvo_is_image($attachment),
                                    'location'=>'local','thumbnail'=>$thumbnail_url];
                // wooconvo_logger($arr);
                $new_attachments = [...$new_attachments, $arr];
            }
        }
        
        return $new_attachments;
    }
    
    function checkTableExists() {
        global $wpdb;
        $table_name = $wpdb->prefix . "nm_wooconvo";
        return $wpdb->query("SHOW TABLES LIKE '$table_name'") == 1;
    }
    
    function checkOrderExists($order_id) {
        $order = wc_get_order( $order_id );
        return $order ? true : false;
    }
    



    function is_old_pro_addon_version_installed(){
            
        $return = false;
        // if pro version installed and active
        if(defined('WOOCONVO_PRO_PATH_PRO')){
            $data = get_plugin_data(WOOCONVO_PRO_PATH_PRO.'/nm-wooconvo-pro.php');
            if( isset($data['Version']) && intval($data['Version']) < 8 ){
                $return = true;
            }
        }
        
        // if aws addon installed and active
        if(defined('WOOCONVO_AWS_PATH')){
            $data = get_plugin_data(WOOCONVO_AWS_PATH.'/wooconvo-addon-amazon-s3.php');
            if( isset($data['Version']) && intval($data['Version']) < 8 ){
                $return = true;
            }
        }
        
        // if revision installed and active
        if(defined('WOOCONVO_REV_PATH')){
            $data = get_plugin_data(WOOCONVO_REV_PATH.'/wooconvo-addon-revisions.php');
            if( isset($data['Version']) && intval($data['Version']) < 8 ){
                $return = true;
            }
        }
        
        // if quick msg addon installed and active
        if(defined('WOOCONVO_QM_PATH')){
            $data = get_plugin_data(WOOCONVO_QM_PATH.'/wooconvo-addon-quickmsg.php');
            if( isset($data['Version']) && intval($data['Version']) < 8 ){
                $return = true;
            }
        }
        
        return $return;
        
    }
    
    // Admin notices
	function show_admin_notice() {
	    
	    $wpfm_install_url = 'https://clients.najeebmedia.com';
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e( 'You have installed an older version of OrderConvo PRO or Addons, please udpate to version 8+ from client portal..', 'wooconvo' ); ?>
            <a class="button" href="<?php echo esc_url($wpfm_install_url)?>"><?php _e('Get updated','wooconvo')?></a></p>
        </div>
        <?php
    }
    
}


function init_wooconvo_migration(){
	return WOOCONVO_Migration::__instance();
}