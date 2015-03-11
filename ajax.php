<?php

global $is_admin_ajax;
$is_admin_ajax = false;


add_action('wp_ajax_sync_categories','sync_categories');
function sync_categories()
{
    if (get_option('swp_settings_status') == true) {
        $lib = new category_lib();
        if ($lib->synchronize_categories_from_server()) {
            echo '<em class="valid">Kategorierna har uppdaterats!</em>';
        } else {
            echo '<em class="invalid">Kunde inte uppdatera. Försök igen senare eller kontakta supporten.</em>';
        }
    } else {
        echo '<em class="invalid">Du måste ställa in API-inställningarna först.</em>';
    }
    die();
}

/**
 * Hooks for doing ajax-magic  - a port to the api
 * Basically an API for the Shopello API
 */
add_action('wp_ajax_nopriv_sapi_get_listing', 'ajax_get_listing');
add_action('wp_ajax_sapi_get_listing', 'ajax_get_listing_admin');

function ajax_get_listing_admin()
{
    global $is_admin_ajax;
    $is_admin_ajax = true;
    return ajax_get_listing();
}
function ajax_get_listing()
{
    global $is_admin_ajax;

    // Make globals accesssible
    $shopello     = new Shopello();
    $api_key      = get_option('swp_api_key');
    $api_endpoint = get_option('swp_api_endpoint');

    $shopello->set_api_key($api_key);
    $shopello->set_api_endpoint($api_endpoint);

    // Admin uses a flag which means we wont use a predefined SWP Item
    // Therefore we create a new temporary SWP_item for this request
    SWP::Instance()->set_active_item(get_swp_item());

    // Get params from whichever object we're using
    $params = SWP::Instance()->get_active_params();
    $params = shopello_sanitize_params($params);

    $response = new stdClass();
    $result = $shopello->call('products', $params);

    $response->status = $result->status;
    $response->products = shopello_render_products($result);

    // Possible to request only html
    echo json_encode($response);

    // this is required to return a proper result
    die();
}


/**
 * Hooks for doing ajax-magic  - a port to the api
 * Basically an API for the Shopello API
 */
add_action('wp_ajax_nopriv_sapi_get_filters', 'ajax_get_filters');
add_action('wp_ajax_sapi_get_filters', 'ajax_get_filters_admin');
function ajax_get_filters_admin()
{
    global $is_admin_ajax;
    $is_admin_ajax = true;
    return ajax_get_filters();
}

function ajax_get_filters()
{
    global $is_admin_ajax;

    // Make globals accesssible
    $shopello     = new Shopello();
    $api_key      = get_option('swp_api_key');
    $api_endpoint = get_option('swp_api_endpoint');

    $shopello->set_api_key($api_key);
    $shopello->set_api_endpoint($api_endpoint);

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

    $response = new stdClass();
    $response->status = true;
    $response->params = $params;
    $response->html   = $html;

    // Return generated markup
    echo json_encode($response);

    die();
    // this is required to return a proper result
}


/**
 * Method to test the API settings provided in options page
 */
add_action('wp_ajax_test_api_settings', 'swp_test_api_settings');
function swp_test_api_settings()
{
    // Make globals accesssible
    $shopello     = new Shopello();
    $api_key      = post('key'); //, false);
    $api_endpoint = post('endpoint'); //, false);

    // Break and fail if key or endpoint not specified.
    if($api_key === false && $api_endpoint === false) {
        die("Api Key or Api Endpoint are missing.");
    }

    // Do a test request
    $shopello->set_api_key($api_key);
    $shopello->set_api_endpoint($api_endpoint);

    $params = array();
    $params['query']  = 'skor';
    $params['offset'] = 0;
    $params['limit']  = 1;

    try {
        $test_call = $shopello->call('products', $params);
    } catch(Exception $e) {
        $test_call = $e;
    }

    $response = new stdClass();
    $response->status = ($test_call && $test_call->status && $test_call->status == true);

    echo json_encode($response);

    die();
}


/**
 *  Ajax method to store item in admin
 */
add_action('wp_ajax_save_item', 'swp_save_item');
function swp_save_item()
{
    $name        = post('name');
    $pagesize    = post('pagesize');
    $keyword     = post('keyword');
    $pricemax    = post('pricemax');
    $filters     = post('filters');
    $sort        = post('sort');
    $sort_order  = post('sort_order');
    $color       = post('color');
    $categories  = explode(',', post('categories',''));


    $item = new SWP_Item($name, $pagesize, $keyword, $categories);
    $item->sort        = $sort;
    $item->sort_order  = $sort_order;
    $item->pricemax   = $pricemax;
    $item->filters     = $filters;
    $item->color       = $color;
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

    echo $resp->json();

    die();
}
