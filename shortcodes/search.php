<?php
/*
	Shortcode definition: [shopello_search]
 */
add_shortcode( 'shopello_search', 'shortcode_shopello_search' );
function shortcode_shopello_search( $atts ){

	// Shopello css
	wp_enqueue_style( 'shopello_css', SHOPELLO_PLUGIN_URL.'css/shopello_all.css');

	// Shopello custom stuff
	wp_enqueue_script('shopello-frontend', SHOPELLO_PLUGIN_URL.'js/frontend.js', array(), '0.1', true);

	// Get possibly passed attributes
	extract( shortcode_atts( array(
		'placeholder' => 'Ange sökord',
		'search_label'=> 'Sök',
		'target'      => '',
		'label'       => '',
		'class'       => ''
	), $atts ) );

	// Load template into $html string
	ob_start();
	include( SHOPELLO_PLUGIN_DIR.'templates/search/form.php' );
	$html = ob_get_contents();
	ob_end_clean();

	return $html;
}
