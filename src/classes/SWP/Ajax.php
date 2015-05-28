<?php
namespace SWP;

use \SWP;
use \category_lib;
use \SWP\ApiClient as ShopelloAPI;
use \WpWrappers;

class Ajax
{
    /** @var ShopelloAPI */
    private $shopelloApi;

    /** @var category_lib */
    private $categoryLib;

    public function __construct(ShopelloAPI $shopelloApi, category_lib $categoryLib)
    {
        $this->shopelloApi = $shopelloApi;
        $this->categoryLib = $categoryLib;

        $actions = array(
            'wp_ajax_sync_categories' => 'syncCategories',
            'wp_ajax_sapi_get_listing' => 'sapiGetListingAdmin',
            'wp_ajax_nopriv_sapi_get_listing' => 'sapiGetListing',
            'wp_ajax_sapi_get_filters' => 'sapiGetFiltersAdmin',
            'wp_ajax_nopriv_sapi_get_filters' => 'sapiGetFilters',
            'wp_ajax_test_api_settings' => 'testApiSettingsAdmin',
            'wp_ajax_save_item' => 'saveItem',
            'wp_ajax_edit_item' => 'editItem',
            'wp_ajax_remove_item' => 'removeItem'
        );

        foreach ($actions as $action => $method) {
            WpWrappers::addAction($action, array($this, $method));
        }
    }

    /**
     * Request new list of categories from shopello servers
     *
     * @action wp_ajax_sync_categories
     */
    public function syncCategories()
    {
        if (get_option('swp_settings_status') == true) {
            if ($this->categoryLib->synchronize_categories_from_server()) {
                echo '<em class="valid">'.__('The categories has been updated!', 'shopello').'</em>';
            } else {
                echo '<em class="invalid">'.__('Could not update, try again later or contact the support.', 'shopello').'</em>';
            }
        } else {
            echo '<em class="invalid">'.__('You have to configure the API-Settings first.', 'shopello').'</em>';
        }

        wp_die();
    }

    /**
     * Get product listing from admin
     *
     * @action wp_ajax_sapi_get_listing
     */
    public function sapiGetListingAdmin()
    {
        // Make globals accesssible
        global $is_admin_ajax;
        $is_admin_ajax = true;

        return $this->sapiGetListing();
    }

    /**
     * Get product listing without privilegies
     *
     * @action wp_ajax_nopriv_sapi_get_listing
     */
    public function sapiGetListing()
    {
        // Make globals accesssible
        global $is_admin_ajax;

        // Admin uses a flag which means we wont use a predefined SWP Item
        // Therefore we create a new temporary SWP_item for this request
        SWP::Instance()->set_active_item(get_swp_item());

        // Get params from whichever object we're using
        $params = SWP::Instance()->get_active_params();
        $params = shopello_sanitize_params($params);

        $result = $this->shopelloApi->getProducts($params);

        $response = (object) array(
            'status' => $result->status,
            'products' => shopello_render_products($result)
        );

        // Possible to request only html
        wp_die(json_encode($response));
    }


    /**
     * Admin Get Filters
     *
     * @action wp_ajax_sapi_get_filters
     */
    public function sapiGetFiltersAdmin()
    {
        // Make globals accessible
        global $is_admin_ajax;
        $is_admin_ajax = true;

        return $this->sapiGetFilters();
    }

    /**
     * Get Filters
     *
     * @action wp_ajax_nopriv_sapi_get_filters
     * @note: This is used, don't know for what though.
     */
    public function sapiGetFilters()
    {
        // Make globals accesssible
        global $is_admin_ajax;

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
    }

    /**
     * Method to test the API settings provided in options page
     *
     * @action wp_ajax_test_api_settings
     */
    public function testApiSettingsAdmin()
    {
        // Break and fail if key or endpoint not specified.
        if (post('key') === false && post('endpoint') === false) {
            die(__('Api Key or Api Endpoint are missing.', 'shopello'));
        }

        $this->shopelloApi->setApiKey(post('key'));
        $this->shopelloApi->setApiEndpoint(post('endpoint'));
        $this->shopelloApi->cache(false);

        try {
            $testCall = $this->shopelloApi->getProducts(array(
                'offset' => 0,
                'limit' => 1
            ));
        } catch (Exception $e) {
            $testCall = $e;
        }

        $response = (object) array(
            'status' => (isset($testCall->status) && $testCall->status === true)
        );

        wp_die(json_encode($response));
    }

    /**
     * Ajax method to store item in admin
     *
     * @action wp_ajax_save_item
     */
    public function saveItem()
    {
        $name       = post('name');
        $pagesize   = post('pagesize');
        $keyword    = post('keyword');
        $pricemax   = post('pricemax');
        $filters    = post('filters');
        $sort       = post('sort');
        $sort_order = post('sort_order');
        $color      = post('color');
        $categories = explode(',', post('categories', ''));


        $item = new Listing($name, $pagesize, $keyword, $categories);
        $item->sort       = $sort;
        $item->sort_order = $sort_order;
        $item->pricemax   = $pricemax;
        $item->filters    = $filters;
        $item->color      = $color;
        $saved = SWP::Instance()->add($item);

        // Display-version of added item
        $data = (object) array(
            'name' => $item->name,
            'description' => $item->get_description(DESC_DELIMITER),
            'id' => $item->get_id()
        );

        // JSON Response
        $resp = new \SWPAjaxResponse();
        $resp->success = $saved;
        $resp->message = __('New item stored', 'shopello');
        $resp->serialized = SWP::Instance()->get_serialized_items();
        $resp->data = $data;

        wp_die($resp->json());
    }

    /**
     * Admin page: Edit item
     *
     * @action wp_ajax_edit_item
     */
    public function editItem()
    {
        $id = isset($_POST['id']) ? intval($_POST['id']) : false;
        $done = false;

        if ($id) {
            $changes = array();
            $possible = array('name', 'pagesize', 'categories', 'keyword');

            foreach ($possible as $key) {
                if (isset($_POST[$key])) {
                    $changes[$key] = $_POST[$key];
                }
            }

            $done = SWP::Instance()->edit($id, $changes);
        }

        // JSON Response
        $resp = new \SWPAjaxResponse();
        $resp->success = $done;
        $resp->message = 'Item '.$id.' edited.';
        $resp->serialized = SWP::Instance()->get_serialized_items();

        die($resp->json());
    }

    /**
     * Admin page: Remove Item
     *
     * @action wp_ajax_remove_item
     */
    public function removeItem()
    {
        $id = $_POST['id'];
        $removed = SWP::Instance()->remove($id);

        // JSON Response
        $resp = new \SWPAjaxResponse();
        $resp->success = $removed;
        $resp->message = sprintf(__('Item %d removed', 'shopello'), $id);
        $resp->serialized = SWP::Instance()->get_serialized_items();

        die($resp->json());
    }
}
