<?php

/**
 * Shortcode definition: [shopello_filters]
 */
add_shortcode('shopello_filters', (function () {
    // Get the SWP Item for this article
    $SWPItem = get_post_swp_item(get_the_ID());

    if (empty($SWPItem)) {
        return null;
    }

    // include apis and helpers
    require_once(SHOPELLO_PLUGIN_DIR.'src/helpers.php');

    SWP::Instance()->set_active_item($SWPItem);
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

    if (get('swp_maxprice')) {
        $params['price_max'] = intval(get('swp_maxprice'));
    }

    if (get('swp_sorting')) {
        $params['order_by'] = get('swp_sorting');
    }

    if (get('swp_sortorder')) {
        $params['order'] = get('swp_sortorder');
    }

    if (get('swp_color')) {
        $params['color'] = get('swp_color');
    }

    // Run filter-code
    return shopello_render_filters_v2($params);
}));
