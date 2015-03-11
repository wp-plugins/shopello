<?php

session_start();

/**
 * @package Shopello API
 */
/**
Plugin Name: Shopello API
Plugin URI: http://shopello.se/api/wordpress
Description: This plugin was created to allow wordpress blogs and websites to in a simple manner include listings of products from Shopello.se.
Version: 1.9.8
Author: Shopello AB
*/

// Define base folder and url for this plugin
define('SHOPELLO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SHOPELLO_PLUGIN_URL', plugin_dir_url(__FILE__));

// Define constant for templates dir
define('SHOPELLO_PLUGIN_TEMPLATE_DIR', SHOPELLO_PLUGIN_DIR.'src/templates/');

// Define constants with table names
define('SHOPELLO_PLUGIN_TABLE_CATEGORIES', 'swp_categories');
define('SHOPELLO_PLUGIN_TABLE_RELATIONS', 'swp_category_parents');

require_once(SHOPELLO_PLUGIN_DIR.'vendor/autoload.php');

// Include the install script for database tables
require_once(SHOPELLO_PLUGIN_DIR.'dbinstall.php');

// Methods for getting data, API access and rendering / parsing
include(SHOPELLO_PLUGIN_DIR.'src/helpers.php');

// Setup widget and admin for widget
include(SHOPELLO_PLUGIN_DIR.'widget.php');

// Setup adminpage for the plugin
include(SHOPELLO_PLUGIN_DIR.'admin.php');

// Include Ajax handling
include(SHOPELLO_PLUGIN_DIR.'ajax.php');

// Include all the shortcode-codes
include(SHOPELLO_PLUGIN_DIR.'shortcodes.php');

// Include productlist metabox for posts/pages
include(SHOPELLO_PLUGIN_DIR.'metabox.php');


// Load dependencies if not in admin-page
if (!is_admin()) {
    SWP::Instance()->frontend_dependencies();
}
