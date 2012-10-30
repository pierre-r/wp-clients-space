<?php

/*
  Plugin Name: WP Clients Space
  Description: Easy management of spaces for customers in Wordpress. <strong>You can add <code>single-customers.php</code> in your theme directory to create your own client space layout</strong>
  Version: 2.0
  Author: Greenpig
  Author URI: http://www.greenpig.be
 */
add_action('init', 'wpcs_init');
add_action('plugins_loaded', 'wpcs_translation');
add_action('add_meta_boxes', 'customers_metaboxes');
add_action('save_post', 'customers_savepost', 10, 2);
add_action('deleted_post', 'wpcs_deleteclient'); // runs AFTER : "trashed_post" ==> moveD to trash || "deleted_post" => deleteD permanently

function wpcs_translation() {
    // Make plugin available for translation
    load_plugin_textdomain('wpcs', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

// Includes
require_once(dirname(__FILE__) . '/wpcs-actions.php');
require_once(dirname(__FILE__) . '/wpcs-widget.php');
?>