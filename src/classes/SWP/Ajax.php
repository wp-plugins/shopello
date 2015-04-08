<?php
namespace SWP;

use \SWP;

use \Shopello\API\ApiClient as ShopelloAPI;
use \Curl\Curl;

class Ajax
{
    public function sapiGetListingAdmin()
    {
        // Make globals accesssible
        global $is_admin_ajax;
        $is_admin_ajax = true;

        return $this->sapiGetListing();
    }

    public function sapiGetListing()
    {
        // Make globals accesssible
        global $is_admin_ajax;

        $shopelloApi = new ShopelloAPI(new Curl());
        $shopelloApi->setApiKey(get_option('swp_api_key'));
        $shopelloApi->setApiEndpoint(get_option('swp_api_endpoint'));

        // Admin uses a flag which means we wont use a predefined SWP Item
        // Therefore we create a new temporary SWP_item for this request
        SWP::Instance()->set_active_item(get_swp_item());

        // Get params from whichever object we're using
        $params = SWP::Instance()->get_active_params();
        $params = shopello_sanitize_params($params);

        $result = $shopelloApi->getProducts($params);

        $response = (object) array(
            'status' => $result->status,
            'products' => shopello_render_products($result)
        );

        // Possible to request only html
        wp_die(json_encode($response));
    }
}
