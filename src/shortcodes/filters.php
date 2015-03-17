<?php

/**
 * [shopello_filters]
 * Shows the filter capabilities in a designated space, separated from the rest of the code
 */
add_shortcode('shopello_filters', (function () {
    if (!$item = get_post_swp_item(get_the_ID())) {
        return;
    }

    // include apis and helpers
    require_once(SHOPELLO_PLUGIN_DIR.'src/helpers.php');

    SWP::Instance()->set_active_item($item);
    $params = SWP::Instance()->get_active_params();
    $params = shopello_sanitize_params($params);

    // Run filter-code
    return shopello_render_filters($params);
}));
