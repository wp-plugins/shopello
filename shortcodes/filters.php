<?php

/*
	[shopello_filters]
	Shows the filter capabilities in a designated space, separated from the rest of the code
*/

add_shortcode( 'shopello_filters', 'shortcode_shopello_filters' );
function shortcode_shopello_filters( $atts ){

	if( ! $item = get_post_swp_item( get_the_ID() ))
		return;

	// include apis and helpers
	require_once( SHOPELLO_PLUGIN_DIR.'helpers/methods.php');

	SWP::Instance()->set_active_item( $item );
	$params = SWP::Instance()->get_active_params();

	// Run filter-code
	return shopello_render_filters( $params );
}