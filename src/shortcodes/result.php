<?php

/**
 * Shortcode definition: [shopello_products]
 */
add_shortcode('shopello_products', (function () {
    // Shopello css
    wp_enqueue_style('shopello_css', SHOPELLO_PLUGIN_URL.'assets/css/shopello-all.css');

    // Enqueue CSS files
    wp_enqueue_style('bootstrap-multiselect', SHOPELLO_PLUGIN_URL.'assets/bootstrap/bootstrap-multiselect.css', array('bootstrap'), '1.0', 'all');
    wp_enqueue_style('bootstrap', SHOPELLO_PLUGIN_URL.'assets/bootstrap/bootstrap.min.css', array(), '3.1.1', 'all');

    // Enqueue JS files
    wp_enqueue_script('bootstrap', SHOPELLO_PLUGIN_URL.'assets/bootstrap/bootstrap.min.js', array('jquery'), '3.1.1', false);
    wp_enqueue_script('bootstrap-multiselect', SHOPELLO_PLUGIN_URL.'assets/bootstrap/bootstrap-multiselect.js', array('bootstrap'), '1.0', true);

    // Shopello custom stuff
    wp_enqueue_script('shopello-frontend', SHOPELLO_PLUGIN_URL.'assets/js/frontend.js', array('bootstrap-multiselect'), '0.1', true);


    // include apis and helpers
    require_once(SHOPELLO_PLUGIN_DIR.'src/helpers.php');

    // Get the SWP Item to run product query
    $query_item = get_post_swp_item(get_the_ID());

    if (!is_a($query_item, SWP_Item)) {
        die(__('The product listing you are trying to use does not exist. Please check your API-Settings.', 'shopello'));
    }

    // Get query result
    SWP::Instance()->set_active_item($query_item);
    $response = SWP::Instance()->run_query();

    // Return generated markup
    return shopello_render_products($response);
}));