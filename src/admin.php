<?php

session_start();
$is_admin_ajax = true;

define('DESC_DELIMITER', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');

// Message-variables
global $swp_notices, $swp_success, $swp_warnings;
$swp_notices = array();
$swp_success = array();
$swp_warnings = array();

// Max number of shortcodes to save
define('SWP_SC_MAX', 6);

$admin = new \SWP\AdminPages(\SWP\View::getInstance(), new \SWP\SystemTests(new \Curl\Curl()));
$admin->registerActions();


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



/**
 * Used from the cronjob to sync categories
 */
add_action('swpsynccategories', (function () {
    if (get_option('swp_settings_status') == true) {
        $lib = new category_lib();
        $lib->synchronize_categories_from_server();
    }
}));
