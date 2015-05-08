<?php

function shopello_render_products_v2($api_result, $params = false)
{
    $view = \SWP\View::getInstance();



    // Make sure we have the params that were used in this request
    if (empty($params)) {
        $params = shopello_sanitize_params(SWP::Instance()->get_active_params());
    }



    $data = array(
        'title' => get_option('swp_result_title'),
        'params' => $params,
        'api_result' => $api_result,
    );



    // Save amount of products found as text
    $productcount = $api_result->total_found ? intval($api_result->total_found) : __('none', 'shopello');
    $data['productcount'] = is_int($productcount) ? number_format(floatval($productcount), 0, '.', ' ') : $productcount;



    // Build Title
    $querystring = $params['keyword'];

    if (strlen($querystring) > 0 && strlen($data['title']) > 0) {
        $data['title'] .= ' '. get_option('swp_keyword_title');
    }

    $prod_count_suffix_label = ($productcount == 1) ? __('product', 'shopello') : __('products', 'shopello');
    $title = preg_replace("#".__('products', 'shopello')."#", $prod_count_suffix_label, $title);
    $title = preg_replace("#\%amount\%#", $productcount, $title);
    $title = preg_replace("#\%phrase\%#", $querystring, $title);

    $data['title'] = $title;



    // Get predefined markup parts
    $html_start_tag = load_swp_template('result/pre_result');
    $html_end_tag   = load_swp_template('result/post_result');



    // Pager
    $pager = new \SWP\Pager();

    // Set the smallest one, total found products or one pagesize less than 1000 since 1000 is max offset from API.
    $pager->setTotalItems(min($api_result->total_found, (1000 - $params['pagesize'])));
    $pager->setPageSize($params['pagesize']);
    $pager->setCurrentPage($params['page']);
    $data['pager'] = $pager->getPager();



    // Return the rendered result
    return $view->render('result/product_grid', $data);
}



function shopello_show_filter_v2($filter, $filters, $is_admin_ajax)
{
    if (count($filters) == 0) {
        return true;
    }

    return in_array($filter, $filters) ? true : (!has_swp_item() && $is_admin_ajax);
}


function shopello_render_filters_build_uri($id)
{
    // Replace swp_page=<number> with swp_page=$page and return
    $uri = preg_replace('/swp_category=\d+/', 'swp_category='.$id, $_SERVER['REQUEST_URI']);

    // swp_page did not exist, add it
    if (strpos($uri, 'swp_category') === false) {
        $join = (bool) parse_url($uri, PHP_URL_QUERY) ? '&' : '?';

        $uri = $uri.$join.'swp_category='.$id;
    }

    return $uri;
}


function shopello_render_filters_v2($params)
{
    $view = \SWP\View::getInstance();

    global $is_admin_ajax;

    $data = array(
        'is_admin_ajax' => $is_admin_ajax,
        'params' => $params,
        'api_response' => SWP::Instance()->run_query($params)
    );


    $data['maxPrice'] = is_int(intval($data['api_response']->extra->max_price)) ? intval($data['api_response']->extra->max_price) : 5000;
    $data['categories'] = swp_get_active_categories($params);

    foreach ($data['categories'] as $key => $value) {
        $data['categories'][$key]->uri = shopello_render_filters_build_uri($value->category_id);
    }

    $data['brands'] = $data['api_response']->extra->brands;

    $data['sortables'] = array(
        'price' => __('Price', 'shopello'),
        'name' => __('Name', 'shopello'),
        'clicks' => __('Clicks', 'shopello'),
        'popularity' => __('Popularity', 'shopello')
    );

    $data['sortables_order'] = array(
        'ASC' => __('Ascending', 'shopello'),
        'DESC' => __('Descending', 'shopello')
    );

    $data['colors'] = array(
        'vit' => __('white', 'shopello'),
        'gra' => __('gray', 'shopello'),
        'svart' => __('black', 'shopello'),
        'bla' => __('blue', 'shopello'),
        'gron' => __('green', 'shopello'),
        'gul' => __('yellow', 'shopello'),
        'orange' => __('orange', 'shopello'),
        'rod' => __('red', 'shopello'),
        'rosa' => __('pink', 'shopello'),
        'lila' => __('purple', 'shopello')
    );

    $filter_arr = is_array($filters) ? $filters : explode(',', $filters);


    $data['settings'] = (object) array(
        'has_swp_item' => has_swp_item(),
        'show_keyword_filter' => shopello_show_filter_v2('keyword', $filters, $is_admin_ajax),
        'show_categories_filter' => shopello_show_filter_v2('categories', $filters, $is_admin_ajax),
        'show_sorting_filter' => shopello_show_filter_v2('pagesize', $filters, $is_admin_ajax),
        'show_maxprice_filter' => shopello_show_filter_v2('pricemax', $filters, $is_admin_ajax),
        'show_sorting_filter' => shopello_show_filter_v2('sorting', $filters, $is_admin_ajax),
        'show_color_filter' => shopello_show_filter_v2('colors', $filters, $is_admin_ajax)
    );


    return $view->render('result/filters', $data);
}
