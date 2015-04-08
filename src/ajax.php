<?php

use \SWP\Ajax;

use \Shopello\API\ApiClient as ShopelloAPI;
use \Curl\Curl;

global $is_admin_ajax;
$is_admin_ajax = false;



add_action('wp_ajax_sync_categories', (function () {
    if (get_option('swp_settings_status') == true) {
        $lib = new category_lib();

        if ($lib->synchronize_categories_from_server()) {
            echo '<em class="valid">'.__('The categories has been updated!', 'shopello').'</em>';
        } else {
            echo '<em class="invalid">'.__('Could not update, try again later or contact the support.', 'shopello').'</em>';
        }
    } else {
        echo '<em class="invalid">'.__('You have to configure the API-Settings first.', 'shopello').'</em>';
    }
    die();
}));



/**
 * Hooks for doing ajax-magic  - a port to the api
 * Basically an API for the Shopello API
 */
$ajax = new Ajax();

add_action('wp_ajax_sapi_get_listing', array($ajax, 'sapiGetListingAdmin'));
add_action('wp_ajax_nopriv_sapi_get_listing', array($ajax, 'sapiGetListing'));



/**
 * Hooks for doing ajax-magic  - a port to the api
 * Basically an API for the Shopello API
 *
 * @note: This is used, don't know for what though.
 */
add_action('wp_ajax_sapi_get_filters', (function () {
    // Make globals accesssible
    global $is_admin_ajax;
    $is_admin_ajax = true;

    // Flag to ignore json and return html
    $html_only = post('html_only');

    // Admin uses a flag which means we wont use a predefined SWP Item
    // Therefore we create a new temporary SWP_item for this request
    SWP::Instance()->set_active_item(get_swp_item());

    // Get default filters from swp item, and append post/get filters with sanitizer
    $params = SWP::Instance()->get_active_params();
    $params = shopello_sanitize_params($params);

    // Run filter-code
    $html = shopello_render_filters($params);

    $response = (object) array(
        'status' => true,
        'params' => $params,
        'html' => $html
    );

    // Return generated markup
    die(json_encode($response));
}));



/**
 * Method to test the API settings provided in options page
 */
add_action('wp_ajax_test_api_settings', (function () {
    $shopelloApi = new ShopelloAPI(new Curl());

    // Break and fail if key or endpoint not specified.
    if (post('key') === false && post('endpoint') === false) {
        die(__('Api Key or Api Endpoint are missing.', 'shopello'));
    }

    $shopelloApi->setApiKey(post('key'));
    $shopelloApi->setApiEndpoint(post('endpoint'));

    try {
        $testCall = $shopelloApi->getProducts(array(
            'offset' => 0,
            'limit' => 1
        ));
    } catch (Exception $e) {
        $testCall = $e;
    }

    $response = (object) array(
        'status' => (isset($testCall->status) && $testCall->status === true)
    );

    die(json_encode($response));
}));



/**
 *  Ajax method to store item in admin
 */
add_action('wp_ajax_save_item', (function () {
    $name       = post('name');
    $pagesize   = post('pagesize');
    $keyword    = post('keyword');
    $pricemax   = post('pricemax');
    $filters    = post('filters');
    $sort       = post('sort');
    $sort_order = post('sort_order');
    $color      = post('color');
    $categories = explode(',', post('categories', ''));


    $item = new SWP_Item($name, $pagesize, $keyword, $categories);
    $item->sort       = $sort;
    $item->sort_order = $sort_order;
    $item->pricemax   = $pricemax;
    $item->filters    = $filters;
    $item->color      = $color;
    $saved = SWP::Instance()->add($item);

    update_option('swp_list', $_SESSION['SWP_last_saved']);

    // Display-version of added item
    $data              = new stdClass();
    $data->name        = $item->name;
    $data->description = $item->get_description(DESC_DELIMITER);
    $data->id          = $item->get_id();

    // JSON Response
    $resp = new SWPAjaxResponse();
    $resp->success = $saved;
    $resp->message = "New item stored";
    $resp->serialized = SWP::Instance()->get_serialized_items();
    $resp->data = $data;

    die($resp->json());
}));
