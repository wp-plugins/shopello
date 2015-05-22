<?php

use \SWP\Listing;

$is_admin_ajax = false;

/**
 * Method to read template part tp variable
 */
function load_template_part($template_name, $part_name = null)
{
    ob_start();
    get_template_part($template_name, $part_name);
    $var = ob_get_contents();
    ob_end_clean();
    return $var;
}

function get_post_swp_item($post_id = false)
{
    if (!$post_id) {
        $post_id = get_the_ID();

        if (!$post_id) {
            $post_id = post('post_id');
        }

        if (!$post_id) {
            return false;
        }
    }

    // Get SWP\Listing ID from post meta
    $swp_list_id = get_post_meta($post_id, '_swp_selected_list');
    $swp_list_id = intval(is_array($swp_list_id) ? $swp_list_id[0] : $swp_list_id);

    // Try to fetch SWP\Listing's through SWP Class
    return \Shopello\ListingManager::getInstance()->getListingById($swp_list_id);
}

/**
 * This method appends the get and post parameters possibly passed to this request
 * @return [type] [description]
 */
function shopello_sanitize_params($params, $full = false)
{
    // Querystring?
    if (request('keyword')) {
        $params['keyword'] = request('keyword');
        $params['query'] = strlen($params['query']) > 0 ? ' '.trim(request('keyword')) : request('keyword');
    }

    if (request('color')) {
        $params['color'] = request('color');
        $params['query'] .= strlen($params['query']) > 0 ? ' '.request('color') : request('color');
    }

    // Category-limitation?
    if (request('categories')) {
        $params['category_id'] = request('categories');
    }

    // Sorting order requested?
    if (request('sort_order')) {
        $params['order'] = request('sort_order');
    }

    // Sort by something special?
    if (request('sort')) {
        $params['order_by'] = request('sort');
    }

    if (request('pricemax')) {
        $params['price_max'] = intval(request('pricemax'));
    }

    // Paging stuff
    if (request('pagesize')) {
        $params['pagesize'] = intval(request('pagesize'));
    }

    // Calculate pagination variables
    if (request('page')) {
        $params['page'] = request('page');
        $offset = intval(request('page')) - 1;
    } else {
        $params['page'] = 1;
        $offset = 0;
    }

    $params['offset'] = $params['pagesize'] * $offset;
    $params['limit']  = $params['pagesize'];

    /*// Cleanup junk data
      if( ! $full ) {
      if( isset($params['categories'])) unset($params['categories']); // category_id is used
      if( isset($params['filters'])) unset($params['filters']);
      if( isset($params['page'])) unset($params['page']);
      if( isset($params['pagesize'])) unset($params['pagesize']);
      if( isset($params['keyword'])) unset($params['keyword']);
      }*/

    return $params;
}



/**
 * Product rendering metod
 */
function shopello_render_products($api_result, $params = false)
{
    global $is_admin_ajax;

    // Get option values for result text
    $title = get_option('swp_result_title');

    // Make sure we have the params that were used this request
    if (!$params) {
        $params = shopello_sanitize_params(SWP::Instance()->get_active_params());
    }

    // Insert variables to rubrik
    $productcount = $api_result->total_found ? intval($api_result->total_found) : 'inga';
    $productcount = is_int($productcount) ? number_format(floatval($productcount), 0, '.', ' ') : $productcount;

    $querystring  = $params['keyword'];

    if (strlen($querystring) > 0 && strlen(get_option('swp_keyword_title')) > 0) {
        $title .= ' '. get_option('swp_keyword_title');
    }

    $prod_count_suffix_label = ($productcount == 1) ? __('product', 'shopello') : __('products', 'shopello');
    $title = preg_replace("#".__('products', 'shopello')."#", $prod_count_suffix_label, $title);
    $title = preg_replace("#\%amount\%#", $productcount, $title);
    $title = preg_replace("#\%phrase\%#", $querystring, $title);

    // Get predefined markup parts
    $html_start_tag = load_swp_template('result/pre_result');
    $html_end_tag   = load_swp_template('result/post_result');

    // Get markup for product lists ( or empty result )
    if ($api_result->status == 1) {
        // defined som reusable variables
        $tmpl_path = SHOPELLO_PLUGIN_TEMPLATE_DIR.'result/product_grid.php'; // item file to load
        $ul_start = "<ul class='shopello_product_list'>"; // start-part of a row / list
        $ul_end   = "</ul>";
        $product_html = $ul_start; // html-variable to return later

        // Make globals accesssible
        global $product;

        $counter = 1;

        foreach ($api_result->data as $product) {
            // Load template into $product_html string
            ob_start();
            include($tmpl_path);
            $product_html .= ob_get_contents();
            ob_end_clean();

            // Split list rows
            if ($counter % 4 == 0) {
                $product_html .= $ul_end . $ul_start;
            }

            // Keep count
            $counter++;
        }

        $product_html.= '</ul>';
    } else {
        $product_html = load_template_part(SHOPELLO_PLUGIN_TEMPLATE_DIR.'result/no_hits');
    }

    // Setup and generate paging markup
    $pages = new Paginator;
    $pages->items_total = min($api_result->total_found, (1000 - $params['pagesize']));
    $pages->items_per_page = $params['pagesize'];
    $pages->mid_range = 9;
    $pages->current_page = $params['page'] ? $params['page'] : 1;
    $pages->paginate();

    $title = '<div class="shopello_result_message">'.$title.'</div>';

    $pagination = "<div class='shopello_paging'><ul class='pagination'>";
    $pagination.= $pages->display_pages();
    $pagination.= "</ul></div>";

    // The full markup
    return $html_start_tag
        //. '<pre>' . print_r($params, true) . '</pre>'
        . $title
        . $product_html
        . $pagination
        . $html_end_tag;
}


/*
  Determine if it's a frontend request
*/
function has_swp_item()
{
    global $is_admin_ajax;

    $post_id = intval(get_the_ID() ? get_the_ID() : post('post_id'));
    $item = get_post_swp_item($post_id);

    return ($item ? true : false );//$is_admin_ajax == true ? true : false);
}

function get_swp_item()
{
    $post_id = intval(get_the_ID() ? get_the_ID() : post('post_id'));
    $item = get_post_swp_item($post_id);

    return $item ? $item : new Listing();
}


function shopello_render_filters($params)
{
    global $is_admin_ajax;

    // Get API data from last/this page's listing
    $api_response = SWP::Instance()->run_query($params);

    // Put array in variables, for template rendering
    extract($params);

    // bullet proofing the variable.
    if (!$filters) {
        $filters = array();
    }

    $fallback_roof = 5000;
    $max_price = is_int(intval($api_response->extra->max_price)) ? intval($api_response->extra->max_price) : $fallback_roof;

    // All categories in the system
    $api_categories = swp_get_category_list();

    // Set available brands from response
    $api_brands = $api_response->extra->brands;

    // Load template into $html string
    $tmpl_path = SHOPELLO_PLUGIN_TEMPLATE_DIR.'result/filters.php'; // item file to load

    // Read in everything
    ob_start();
    include( $tmpl_path );
    $html = ob_get_contents();
    ob_end_clean();


    // Set selected status on selected categories
    $selected_categories = $params['categories'];
    if (is_object($selected_categories)) {
        foreach ($selected_categories as $cat_id) {
            $api_categories->$cat_id->selected = true;
        }
    }

    return $html;
}

/**
 * truncate a string only at a whitespace (by nogdog)
 */
function truncate($text, $length, $hard = false)
{
    $length = abs((int)$length);
    if (strlen($text) > $length) {
        if ($hard) {
            $text = substr($text, 0, $length). '...';
        } else {
            $text = preg_replace("/^(.{1,$length})(\s.*|$)/s", '\\1...', $text);
        }
    }
    return($text);
}


/**
 * Load a template from this plugin
 */
function load_swp_template($template_path)
{
    $full_path = SHOPELLO_PLUGIN_TEMPLATE_DIR.$template_path;
    if (!file_exists($full_path)) {
        $full_path .= '.php';

        if (!file_exists($full_path)) {
            return false;
        }
    }

    ob_start();
    include($full_path);
    return ob_get_clean();
}


function swp_get_category_list()
{
    global $wpdb;

    $categories_table = $wpdb->prefix . SHOPELLO_PLUGIN_TABLE_CATEGORIES;
    $relations_table  = $wpdb->prefix . SHOPELLO_PLUGIN_TABLE_RELATIONS;

    $categories = $wpdb->get_results(
        "SELECT cat.name AS category_name, "
        ."cat.category_id AS category_id, "
        ."case (rel.parent_id is NULL) when 1 then 0 else rel.parent_id end AS category_parent "
        ." FROM $categories_table AS cat"
        ." LEFT JOIN $relations_table AS rel ON cat.category_id = rel.category_id"
        ." ORDER BY cat.name",
        OBJECT
    );
    return $categories;
}


/**
 * Get categories that belongs to list
 */
function swp_get_active_categories($params)
{
    global $wpdb;

    if (!$params['category_id']) {
        return array();
    }

    $categories_table = $wpdb->prefix . SHOPELLO_PLUGIN_TABLE_CATEGORIES;
    $relations_table  = $wpdb->prefix . SHOPELLO_PLUGIN_TABLE_RELATIONS;

    $categories = $wpdb->get_results(
        "SELECT cat.name AS category_name, "
        ."cat.category_id AS category_id, "
        ."case (rel.parent_id is NULL) when 1 then 0 else rel.parent_id end AS category_parent "
        ." FROM $categories_table AS cat"
        ." LEFT JOIN $relations_table AS rel ON cat.category_id = rel.category_id"
        ." WHERE cat.category_id IN (".$params['category_id'].")"
        ." GROUP BY cat.category_id"
        ." ORDER BY cat.name",
        OBJECT
    );

    return $categories;
}

/**
 * Get hierarchical category tree
 */
function swp_get_category_tree()
{
    $categories = swp_get_category_list();
    $sorted = swp_get_category_children(0, $categories);
    return $sorted;
}

/**
 * part of swp_get_category_tree
 */
function swp_get_category_children($pid, $categories)
{
    $children = array();

    foreach ($categories as $c) {
        if ($c->category_parent == $pid) {
            $c->children = swp_get_category_children($c->category_id, $categories);
            $children[] = $c;
        }
    }
    return $children;
}

/**
 * Read POST and GET vars
 */
function post($p)
{
    if (!isset($_POST[$p])) {
        return false;
    } else {
        return $_POST[$p];
    }
}

function get($p)
{
    if (!isset($_GET[$p])) {
        return false;
    } else {
        return $_GET[$p];
    }
}
function request($p)
{
    if (post($p)) {
        return post($p);
    } elseif (get($p)) {
        return get($p);
    } else {
        return false;
    }
}
