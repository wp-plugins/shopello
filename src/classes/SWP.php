<?php

/**
 * SWP is a class that contains listing of these SWP_Items
 * Accessible as:
 * - SWP::get_items( x ) for single item
 * - SWP::get_items()  for all items
 * - SWP::add( SWP_Item ) to add an item to the storage
 */
class SWP
{
    private static $instance;
    private $items           = false;
    private $option_list_key = "swp_list";
    private $session_id      = "SWP_last_saved";
    private $max_items       = 15;
    private $active_item     = false;

    /*
      Method to retrieve Singleton instance
    */
    public static function Instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * private constructor = uncallable. Initialize by assigning items from session value
     */
    private function __construct()
    {
        // Get options from admin page storage
        $this->load();
    }

    /*
      Manually set items of SWP_Items. Be careful here
    */
    public function set_items($a)
    {
    	if(count( $a ) > 0) {
            $this->items = $a;
            $this->save();
    	}
    }

    /**
     *
     * Returns either a specific item based on index (or ID), or the whole array of items
     */
    public function get_items($id = false)
    {
    	if($id === false) {
            // return all
            return $this->items;
        }

        // return single item or false
        return $this->find($id);
    }

    /**
     * Method for validating and appending an item to list of items
     */
    public function add($item)
    {
    	// Try to validate and push item to storage
    	if (!$item instanceof SWP_Item) {
            return false;
        } elseif (count($this->items) >= $this->max_items) {
            return false;
        } else {
            $arr = $this->items;

            $id = $this->generate_id();
            $item->set_id($id);

            array_push($arr, $item);

            $this->items = $arr;
            $this->save();

            return true;
    	}

    	return false;
    }

    public function remove($id)
    {
    	$id = intval($id);
    	$arr = $this->items;
    	$rem = false;
    	$i = 0;

    	if (count($arr)) {
            for ($i=0;$i<count($arr); $i++) {
                if ($arr[$i]->get_id() == $id) {
                    array_splice($arr, $i, 1);
                    $rem = true;
                    break;
                }
            }
            $this->items = $arr;
            $this->save();
        }

    	return $rem;
    }

    public function save()
    {
    	// Store changes in database option
    	$serialized = serialize($this->items);
    	$success = update_option( $this->option_list_key , $serialized);
    }

    public function get_serialized_items()
    {
    	if (count( $this->items ) > 0) {
            return serialize($this->items);
        } else {
            return '';
        }
    }

    public function load()
    {
        // load from options
        $opt = get_option($this->option_list_key);

        if (strlen($opt) == 0) {
            $this->items = array();
        } else {
            $this->items = unserialize(get_option($this->option_list_key));
        }
    }

    public function edit($id, $props)
    {
    	$id = intval($id);
    	$arr = $this->items;
    	$done = false;

    	for ($i=0;$i<count($arr);$i++) {
            // Locate the item by id
            if ($arr[$i]->get_id() == $id) {
                // Iterate all properties on $props, apply them to $arr[$i] if suitable
                foreach ($props as $key=>$val) {
                    // Property must exist to be set
                    if (isset($arr[$i]->$key)) {
                        $arr[$i]->$key = $val;
                    }

                    // All went ok
                    $done = true;
                }

                // Store changes
                $this->items = $arr;
                $this->save();

                // Dont loop more than necessary
                break;
            }
    	}

    	// Tell if all went ok
    	return $done;
    }

    public function set_active_item(SWP_Item $item)
    {
    	$this->active_item = $item;
    }

    public function run_query($params = false)
    {
    	// Set passed item as active
    	if ($params == false || empty($params)) {
            $params = $this->get_active_params();

            // Check that active item is valid
            if ($params == false || empty($params)) {
                throw new Exception(sprintf(__('You cannot run_query %1$s is set or you pass some %2$s!   // Love, SWP', 'shopello'), $active_item, $params));
                return false;
            }

            $params = shopello_sanitize_params($params);
        }

        // Get endpoint settings
        $api_key      = get_option('swp_api_key');
        $api_endpoint = get_option('swp_api_endpoint');

        // Setup API instance
        $shopello = new Shopello();
        $shopello->set_api_key($api_key);
        $shopello->set_api_endpoint($api_endpoint);

        // Run API query
        $api_result = $shopello->call('products', $params);

        // Cache and return result if successful
        if ($api_result) {
            // return results
            return $api_result;
        } else {
            return false;
        }
    }

    public function get_active_params($item = false)
    {
        // Set passed item as active
        if ($item !== false && $item instanceof SWP_Item) {
            $this->set_active_item($item);
        }

    	// Check that active item is valid
    	if (!$this->active_item || !$this->active_item instanceof SWP_Item) {
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

    private function find($id)
    {
    	$arr = $this->items;

    	for ($i=0;$i<count($arr);$i++) {
            if ($arr[$i]->get_id() === $id) {
                return $arr[$i];
            }
    	}

    	return false;
    }

    private function generate_id()
    {
        // Fetch all ID's to compare new ID with
        $ids = array();
        foreach ($this->items as $i) {
            $ids[] = $i->get_id();
        }

        // Randomize number ID until we have a unique one
        do {
            $id = rand(1000,9999);
        } while(in_array($id, $ids));

        // Return new valid ID
        return $id;
    }

    public function get_active_item_categories()
    {
        $response = $this->run_query($this->get_active_params());
        return $response->extra->categories;
    }

    /*
    public function get_filters($post_id)
    {
        if ($post_id) {
            $x = 0;
        }

        $atts = get_post_attributes();

        // Make globals accesssible
        global $product;

        // Only add css when needed.
        wp_enqueue_style('shopello_css');

        global $shopelloResponse;

        $categories = $shopelloResponse->extra->categories;

        // Load template into $html string
        $tmpl = SHOPELLO_PLUGIN_TEMPLATE_DIR.'result/categories_list.php'; // item file to load

        // Read in everything
        ob_start();
        include( $tmpl );
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }
    //*/
    public function frontend_dependencies()
    {
        // Shopello css
        wp_enqueue_style( 'shopello_css', SHOPELLO_PLUGIN_URL.'css/shopello_all.css');

        // Shopello custom stuff
        wp_enqueue_script('jquery_form', SHOPELLO_PLUGIN_URL."js/jquery.form.min.js", false, '1.0', true);
        wp_enqueue_script('generator_js', SHOPELLO_PLUGIN_URL."js/swp_api_generator.js", false, '1.0.1', true);
        wp_enqueue_script('shopello-frontend', SHOPELLO_PLUGIN_URL.'js/frontend.js', false, '0.1', true);
    }
}
