<?php
session_start();

/**
 * @package Shopello API
 *
 * Plugin Name: Shopello API
 * Plugin URI: http://shopello.se/api/wordpress
 * Description: This plugin was created to allow wordpress blogs and websites to in a simple manner include listings of products from Shopello.se.
 * Version: 2.0.2
 * Author: Shopello AB
 */

// Define base folder and url for this plugin
define('SHOPELLO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SHOPELLO_PLUGIN_URL', plugin_dir_url(__FILE__));

// Define constant for templates dir
define('SHOPELLO_PLUGIN_TEMPLATE_DIR', SHOPELLO_PLUGIN_DIR.'src/templates/');

// Define constants with table names
define('SHOPELLO_PLUGIN_TABLE_CATEGORIES', 'swp_categories');
define('SHOPELLO_PLUGIN_TABLE_RELATIONS', 'swp_category_parents');

// Add hook to init language support
add_action('plugins_loaded', (function () {
    load_plugin_textdomain('shopello', false, dirname(plugin_basename(__FILE__)).'/src/lang/');
}));

require_once(SHOPELLO_PLUGIN_DIR.'vendor/autoload.php');

// Include the install script for database tables
require_once(SHOPELLO_PLUGIN_DIR.'src/dbinstall.php');

// Methods for getting data, API access and rendering / parsing
require_once(SHOPELLO_PLUGIN_DIR.'src/helpers.php');
require_once(SHOPELLO_PLUGIN_DIR.'src/rendering_helpers.php');

// Setup widget and admin for widget
require_once(SHOPELLO_PLUGIN_DIR.'src/widget.php');

// Setup adminpage for the plugin
require_once(SHOPELLO_PLUGIN_DIR.'src/admin.php');



// Setup Ajax handling
global $is_admin_ajax;
$is_admin_ajax = false;

$shopelloApi = new \Shopello\API\ApiClient(new \Curl\Curl());
$shopelloApi->setApiKey(get_option('swp_api_key'));
$shopelloApi->setApiEndpoint(get_option('swp_api_endpoint'));

$swpAjax = new \SWP\Ajax($shopelloApi, new \category_lib());



// Include all the shortcode-codes
require_once(SHOPELLO_PLUGIN_DIR.'src/shortcodes.php');

// Include productlist metabox for posts/pages
require_once(SHOPELLO_PLUGIN_DIR.'src/metabox.php');

// Load dependencies if not in admin-page
if (!is_admin()) {
    SWP::Instance()->frontend_dependencies();
}
