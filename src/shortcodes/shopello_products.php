<?php

/**
 * Shortcode definition: [shopello_products]
 */
add_shortcode('shopello_products', (function () {
    // Get the SWP Item for this article
    $SWPItem = get_post_swp_item(get_the_ID());

    if (empty($SWPItem)) {
        return __('The product listing you are trying to use does not exist. Please check your API-Settings.', 'shopello');
    }

    // Get Query Result
    SWP::Instance()->set_active_item($SWPItem);

    // Fetch params to send to the API
    $params = shopello_sanitize_params(SWP::Instance()->get_active_params());


    /**
     * Handle API Params
     */
    if (get('swp_pagesize')) {
        $params['pagesize'] = intval(get('swp_pagesize'));
    }

    if (get('swp_page')) {
        $params['page'] = intval(get('swp_page'));

        $params['offset'] = ($params['page'] - 1) * $params['pagesize'];
        $params['limit'] = $params['pagesize'];
    }

    if (get('swp_query')) {
        $params['query'] = get('swp_query');
    }

    if (get('swp_maxprice')) {
        $params['price_max'] = intval(get('swp_maxprice'));
    }

    if (get('swp_category')) {
        $params['category_id'] = get('swp_category');
    }

    if (get('swp_sorting')) {
        $params['order_by'] = get('swp_sorting');
    }

    if (get('swp_sortorder')) {
        $params['order'] = get('swp_sortorder');
    }

    if (get('swp_color')) {
        $params['query'] .= ' '.get('swp_color');
    }

    // Fetch products from the API with these params
    $response = SWP::Instance()->run_query($params);

    return shopello_render_products_v2($response, $params);
}));
