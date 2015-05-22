<?php
use \SWP\ApiClient as ShopelloAPI;
use \SWP\Listing;

use \Shopello\ListingManager;

/**
 * SWP is a class that contains listing of these SWP\Listing
 * Accessible as:
 * - SWP::get_items( x ) for single item
 * - SWP::get_items()  for all items
 * - SWP::add( SWP\Listing ) to add an item to the storage
 */
class SWP
{
    private static $instance;

    public static function Instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /** @var ListingManager */
    private $listingManager;
    private $active_item = false;

    /**
     * private constructor = uncallable. Initialize by assigning items from session value
     */
    private function __construct()
    {
        $this->listingManager = ListingManager::getInstance();
    }

    /**
     * Get Listing/s
     */
    public function get_items($id = false)
    {
        if ($id === false) {
            return $this->listingManager->getAllListings();
        }

        return $this->listingManager->getListingById($id);
    }

    /**
     * Add Listing
     */
    public function add($listing)
    {
        return $this->listingManager->addListing($listing);
    }

    /**
     * Edit Listing
     */
    public function edit($id, $props)
    {
        $object = (object) $props;

        return $this->listingManager->editListing($id, $object);
    }

    /**
     * Remove Listing
     */
    public function remove($id)
    {
        return $this->listingManager->removeListing($id);
    }



    /**
     * Random old methods that are used
     */
    public function get_serialized_items()
    {
        $array = array();

        foreach ($this->listingManager->getAllListings() as $item) {
            $array[] = $item->exportSettings();
        }

        return json_encode($array);
    }

    public function run_query($params = false)
    {
        // Set passed item as active
        if ($params == false || empty($params)) {
            $params = $this->get_active_params();

            // Check that active item is valid
            if ($params == false || empty($params)) {
                throw new Exception(
                    sprintf(
                        'You cannot run_query %1$s unless you pass some %2$s',
                        $active_item,
                        $params
                    )
                );
            }

            $params = shopello_sanitize_params($params);
        }

        // Get API instance
        $shopelloApi = ShopelloAPI::getInstance();

        // Run API query
        try {
            $apiResult = $shopelloApi->getProducts($params);
        } catch (Exception $e) {
            $apiResult = false;
        }

        // Cache and return result if successful
        if ($apiResult) {
            // return results
            return $apiResult;
        } else {
            return false;
        }
    }

    public function set_active_item(Listing $item)
    {
        $this->active_item = $item;
    }

    public function get_active_params($item = false)
    {
        // Set passed item as active
        if ($item !== false && $item instanceof Listing) {
            $this->set_active_item($item);
        }

        // Check that active item is valid
        if (!$this->active_item || !$this->active_item instanceof Listing) {
            return false;
        }

        // Get basic query info from active item
        $item = $this->active_item;
        $keyword = $item->keyword;
        $pagesize = $item->pagesize;
        $filters  = $item->filters;
        $categories = is_array($item->categories) ? implode(',', $item->categories) : '';

        // Query parameters for shopello API
        $params = array(
            'query'       => $item->keyword,
            'pagesize'    => $item->pagesize,
            'category_id' => join($item->categories, ','),
            'filters'     => $item->filters,
            'offset'      => '',
            'limit'       => '',
            'color'       => '',
            'order_by'    => $item->sort,
            'order'       => $item->sort_order,
            'extra'       => 'max_price,stores,categories,brands,has_reduction'
        );

        // Return this parameter object
        return $params;
    }

    public function frontend_dependencies()
    {
        // Shopello css
        wp_enqueue_style('shopello-css', SHOPELLO_PLUGIN_URL.'assets/css/shopello-all.css', array(), 1.0);

        // Shopello custom stuff
        wp_enqueue_script('shopello-frontend', SHOPELLO_PLUGIN_URL.'assets/js/frontend.js', false, 1.0, true);
    }
}
