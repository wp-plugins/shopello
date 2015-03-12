<?php

session_start();
$is_admin_ajax = true;

define('DESC_DELIMITER','&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');

// Message-variables
global $swp_notices, $swp_success, $swp_warnings;
$swp_notices = array();
$swp_success = array();
$swp_warnings = array();

// Max number of shortcodes to save
define('swp_SC_MAX', 6);



/**
 * Hook admin menu loading
 */
add_action('admin_menu', (function () {
    //            Page Title,                 Menu Title,                 Permissions,      Menu SLUG,          Callback Function,        Icon, Sort
    add_menu_page(__('Shopello', 'shopello'), __('Shopello', 'shopello'), 'manage_options', 'shopello_options', 'shopello_api_adminpage', '',   76);

    //               Parent SLUG,        Page Title,                     Menu Title,                         Permissions,      Menu SLUG,                  Callback Function
    add_submenu_page('shopello_options', __('Shopello', 'shopello'),     __('Product Listings', 'shopello'), 'manage_options', 'shopello_options',         'shopello_api_adminpage');
    add_submenu_page('shopello_options', __('API-Settings', 'shopello'), __('API-Settings', 'shopello'),     'manage_options', 'shopello_options_account', 'shopello_api_adminpage_account');
    add_submenu_page('shopello_options', __('System Test', 'shopello'),  __('System Test', 'shopello'),      'manage_options', 'shopello_system_test',     'shopello_api_adminpage_system_test');
}));



/**
 * Hook styles/script loading
 */
add_action('admin_enqueue_scripts', (function () {
    wp_enqueue_style('shopello_css', SHOPELLO_PLUGIN_URL.'css/shopello_all.css', false, '1.0.0');
    wp_enqueue_script('jquery_form', SHOPELLO_PLUGIN_URL.'js/jquery.form.min.js', false, '1.0', true);
    wp_enqueue_script('generator_js', SHOPELLO_PLUGIN_URL.'js/swp_api_generator.js', false, '1.0.0', true);
    wp_enqueue_script('admin_js', SHOPELLO_PLUGIN_URL.'js/admin.js', false, '1.0.0', true);

    wp_localize_script('admin_js', 'adminL10n', array(
        'working_button' => __('Working... This can take several minutes...', 'shopello'),
        'max_listings' => __('You have reached max number of listings (%d st). Remove one to be able to save this new one.', 'shopello'),
        'save_error' => __('Error while saving. Reload the page and try again.', 'shopello'),
        'save_prompt' => __('Choose a name for your listing:', 'shopello')
    ));
}));



/**
 * Hook default settings
 */
add_action('admin_init', (function () {
    // Define options & defaults
    add_option('swp_api_key', '');
    add_option('swp_api_endpoint', 'https://se.shopelloapi.com/1/');
    add_option('swp_settings_status', '');
    add_option('swp_list', '');
    add_option('swp_result_title', '');
    add_option('swp_keyword_title', '');

    // Register settings and eventual sanitizing callbacks (when saving)
    register_setting('default', 'swp_api_key');
    register_setting('default', 'swp_api_endpoint');
    register_setting('default', 'swp_settings_status');
    register_setting('default', 'swp_list', 'sanitize_swp_items');
    register_setting('default', 'swp_result_title');
    register_setting('default', 'swp_keyword_title');


    if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
        // plugin settings have been saved. Here goes your code
        // ...
    }
}));



/**
 * Printing methods for different notices/messages in wp admin
 */
function swp_print_success()
{
    global $swp_success;

    if ($swp_success) {
        foreach ($swp_success as $msg) {
            echo '<div class="updated"><p>'.$msg.'</p></div>';
        }
    }
}



function swp_print_notices()
{
    global $swp_notices;

    if ($swp_notices) {
        foreach ($swp_notices as $msg) {
            echo '<div class="update-nag"><p>'.$msg.'</p></div>';
        }
    }
}



function swp_print_warnings()
{
    global $swp_warnings;

    if ($swp_warnings) {
        foreach ($swp_warnings as $msg) {
            echo '<div class="error"><p>'.$msg.'</p></div>';
        }
    }
}



/**
 * Method to serialize SWP_Items list to json and store in plugin settings.
 * @return string - json representation of stored SWP_Items
 */
function sanitize_swp_items()
{
    return SWP::Instance()->get_serialized_items();
}



add_action('wp_ajax_edit_item', (function () {
    $id = isset($_POST['id']) ? intval($_POST['id']) : false;
    $done = false;

    if ($id) {
        $changes = array();
        $possible = array('name', 'pagesize', 'categories', 'keyword');

        foreach($possible as $key) {
            if (isset($_POST[$key])) {
                $changes[$key] = $_POST[$key];
            }
        }
        $done = SWP::Instance()->edit($id, $changes);
    }


    // JSON Response
    $resp = new SWPAjaxResponse();
    $resp->success = $done;
    $resp->message = 'Item '.$id.' edited.';
    $resp->serialized = SWP::Instance()->get_serialized_items();

    echo $resp->json();

    die();
}));



add_action('wp_ajax_remove_item', (function () {
    $id = $_POST['id'];
    $removed = SWP::Instance()->remove($id);

    // JSON Response
    $resp = new SWPAjaxResponse();
    $resp->success = $removed;
    $resp->message = sprintf(__('Item %d removed', 'shopello'), $id);
    $resp->serialized = SWP::Instance()->get_serialized_items();

    echo $resp->json();

    die();
}));



/**
 * Include scripts for ajax-saving the options page.
 */
add_action('admin_init', (function () {
    if (is_admin()) { // for Admin Dashboard Only
        // Embed the Script on our Plugin's Option Page Only
        if (isset($_GET['page']) && $_GET['page'] == 'shopello_api_options') {
            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-form');
        }
    }
}));



add_action('swpsynccategories', (function () {
    if(get_option('swp_settings_status') == true) {
        $lib = new category_lib();
        $lib->synchronize_categories_from_server();
    }
}));



/**
 * Create the options page
 */
function shopello_api_adminpage_account()
{
    if (!current_user_can('manage_options'))  {
        wp_die(__('You do not have sufficient permissions to access this page.', 'shopello'));
    }

    include(SHOPELLO_PLUGIN_TEMPLATE_DIR.'admin/account.php');
}



/**
 * Create the Product listing page
 */
function shopello_api_adminpage()
{
    global $is_admin_ajax;

    if (!current_user_can('manage_options'))  {
        wp_die(__('You do not have sufficient permissions to access this page.', 'shopello'));
    }

    $is_admin_ajax = true;
    $api_categories = swp_get_category_list();

    include(SHOPELLO_PLUGIN_TEMPLATE_DIR.'admin/product-lists.php');
}



/**
 * System test page
 */
function shopello_api_adminpage_system_test()
{
    if (!current_user_can('manage_options'))  {
        wp_die(__('You do not have sufficient permissions to access this page.', 'shopello'));
    }

    include(SHOPELLO_PLUGIN_TEMPLATE_DIR.'admin/system-test.php');
}
