<?php

session_start();

/**
 * @package Shopello API
 * @version 1.0
 */
/*
Plugin Name: Shopello API
Plugin URI: http://shopello.se/api/wordpress
Description: This plugin was created to allow wordpress blogs and websites to in a simple manner include listings of products from Shopello.se.
Version : 1.0
Author: 203 Creative AB
*/




// Define base folder and url for this plugin

define('SHOPELLO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SHOPELLO_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include the controller-class SWP
require_once(SHOPELLO_PLUGIN_DIR . 'classes/SWP.php');

// Methods for getting data, API access and rendering / parsing
include(SHOPELLO_PLUGIN_DIR . 'helpers/methods.php');

// Setup widget and admin for widget
include(SHOPELLO_PLUGIN_DIR . 'widget.php');

// Setup adminpage for the plugin
include(SHOPELLO_PLUGIN_DIR . 'admin.php');

// Include Ajax handling
include(SHOPELLO_PLUGIN_DIR . 'ajax.php');

// Include all the shortcode-codes
include(SHOPELLO_PLUGIN_DIR . 'shortcodes.php');


include(SHOPELLO_PLUGIN_DIR . 'metabox.php');


// Load dependencies if not in admin-page
if( ! is_admin()) {
	SWP::Instance()->frontend_dependencies();
}