<?php

/**
 * Shortcode definition: [shopello_search]
 */
add_shortcode('shopello_search', (function ($atts) {
    // Shopello css
    wp_enqueue_style('shopello_css', SHOPELLO_PLUGIN_URL.'assets/css/shopello-all.css');

    // Shopello custom stuff
    wp_enqueue_script('shopello-frontend', SHOPELLO_PLUGIN_URL.'assets/js/frontend.js', array(), '0.1', true);

    // Get possibly passed attributes
    extract(shortcode_atts(array(
        'placeholder' => 'Ange sökord',
        'search_label'=> 'Sök',
        'target'      => '',
        'label'       => '',
        'class'       => ''
    ), $atts));

    // Load template into $html string
    ob_start();
    include(SHOPELLO_PLUGIN_TEMPLATE_DIR.'search/form.php');
    $html = ob_get_contents();
    ob_end_clean();

    return $html;
}));
