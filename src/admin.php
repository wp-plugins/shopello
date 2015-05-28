<?php

$is_admin_ajax = true;

define('DESC_DELIMITER', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');

// Max number of shortcodes to save
define('SWP_SC_MAX', 6);

$admin = new \SWP\AdminPages(\SWP\View::getInstance(), new \SWP\SystemTests(new \Curl\Curl()));
$admin->registerActions();



/**
 * Method to serialize SWP\Listing list to json and store in plugin settings.
 * @return string - json representation of stored SWP\Listing
 */
function sanitize_swp_items()
{
    return json_encode((object) \Shopello\ListingManager::getInstance()->getAllListings());
}



/**
 * Used from the cronjob to sync categories
 */
add_action('swpsynccategories', (function () {
    if (get_option('swp_settings_status') == true) {
        $lib = new category_lib();
        $lib->synchronize_categories_from_server();
    }
}));
