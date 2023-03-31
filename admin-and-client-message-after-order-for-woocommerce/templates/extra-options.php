<?php
/**
 * Template for Render Extra Options Fields
 * Created Date: November 2, 2022
 * Created by: Ben Rider
 **/
 
if( ! defined('ABSPATH' ) ){ exit; }

$you_have_img = '';
?>

<div class="wrap" id="wpbody-content">
    <h1><?php _e("WCForce Extra Options for WooCommerce", "wcforce-eo");?></h1>
    
    <div id="wcforce-root"></div>
    
    <div id="wpmedia-box">
        <div class="custom-img-container"></div>
        <p class="hide-if-no-js">
        <a class="upload-custom-img <?php if ( $you_have_img  ) { echo 'hidden'; } ?>" 
           href="<?php echo $upload_link ?>">
            <?php _e('Set custom image') ?>
        </a>
        <a class="delete-custom-img <?php if ( ! $you_have_img  ) { echo 'hidden'; } ?>" 
          href="#">
            <?php _e('Remove this image') ?>
        </a>
    </p>
    </div>
    <button id="wcforce-test">Media</button>
    
</div>