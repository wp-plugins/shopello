<?php
namespace SWP;

use \Curl\Curl;
use \Shopello\API\ApiClient as ShopelloAPI;

class ApiClient
{
    /** @var ShopelloAPI */
    private $shopelloApi;

    // Cache status
    private $cache = true;

    // Instance
    private static $instance;

    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->shopelloApi = new ShopelloAPI(new Curl());

        $this->shopelloApi->setApiKey(get_option('swp_api_key'));
        $this->shopelloApi->setApiEndpoint(get_option('swp_api_endpoint'));
    }

    public function __call($method, $params)
    {
        $cacheKey = 'swp_'.$method.'_'.md5(json_encode($params));

        $data = get_transient($cacheKey);

        if (false === $data || false === $this->cache) {
            $data = call_user_func_array(array($this->shopelloApi, $method), $params);

            set_transient($cacheKey, $data, 3600);
        }

        return $data;
    }

    /**
     * Set API Key
     *
     * @param $apiKey string
     * @return void
     */
    public function setApiKey($apiKey)
    {
        return $this->shopelloApi->setApiKey($apiKey);
    }

    /**
     * Set API Endpoint
     *
     * @param $apiEndpoint string
     * @return void
     */
    public function setApiEndpoint($apiEndpoint)
    {
        return $this->shopelloApi->setApiEndpoint($apiEndpoint);
    }

    /**
     * Cache control, disable or enable cache.
     *
     * @param $status bool
     * @return void
     */
    public function cache($status)
    {
        $this->cache = $status;
    }
}
