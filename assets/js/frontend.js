(function($){
    $(document).ready(function() {

	// reserve a variable for SWP Api Generator.
	var generator = false;
	var products_wrap  = '#shopello_products_wrap';
	var paging_buttons = '.shopello_paging li:not(.active):not(.disabled) a';

	window.ajaxurl = urlpath() + "wp-admin/admin-ajax.php";

	var connect_on_reload = function(){

	    /* When navigating, always scroll to top */
	    $(paging_buttons).click(function( e ){
		$('html, body').animate({
		    scrollTop: $(products_wrap).offset().top - 50
		}, 1000);
	    });
	}
	connect_on_reload();

	/**
	 * Get URL Parameters converted to variables
	 */
	function urlparameters(){
	    var target = window.location.href;
	    target = target.split('?');

	    if (target.length > 1){
		target = decodeURIComponent(target[1]);
		var pairs = target.split('&');
		var arr = {};

		for(var i=0;i<pairs.length;i++) {

		    var set = pairs[i].split('=');
		    var key = set[0];
		    var val = set[1];


		    if (key.indexOf("[]") == key.length - 2)
		 	key = key.replace("[]", "");

		    // Do we already have this key defined
		    if( arr[key] ) {
			// Is this key an array? If not, it should be.
			if( ! $.isArray(arr[key]) )
			    arr[key] = [arr[key]];

			// add new value to key array
			arr[key].push(val);
		    } else {
			// First time-key, just set value
			arr[key] = val;
		    }
		}
		return arr;
	    }
	    return false;
	}

	/**
	 * Returns the part of the url before the '?' mark
	 */
	function urlpath() {
	    // Look in h (webpage path) for c (querystring delimited ?) and return everything before the '?'
	    var h = window.location.href;
	    var c = '?';

	    // Url has querystring?
	    if( h.indexOf( c )) {
		// Split it and return path before querystring
		h = h.split( c );
		return h[0];
	    }
	    // URL had no querystring, just return it
	    return h;
	}

	/**
	 * Shopello Frontend filtering stuff.
	 */
	var autobots_transform = function() {

	    // Create Generator instance
	    generator = new SWP_Api_Generator();

	    var result_wrap = $('#shopello_products_wrap');
	    var filter_wrap = $('#shopello_filter_wrap');


	    /**
	     * Listen for server args changes (filter changes)
	     * and refresh results and filters dynamically
	     */
	    generator.listen( generator.EVENTS.CHANGE , function( server_args ){


		// Load new search results
		generator.load_api_result( result_wrap, server_args );

		if( filter_wrap.length ) {
		    // Load new filter data ( if filters are here )
		    generator.load_api_filters( filter_wrap, server_args, function(){
			connect_on_reload();
		    });
		}
	    });
	}; // autobots_transformed complete!


	// Enable filter listeners?
	if( $('#swp-filters') && typeof SWP_Api_Generator !== undefined ) {

	    autobots_transform();
	}

	$(".s_qs_search_form").submit(function(e) {

	    // Check if search-plugin is this page, then add to query, otherwise let it do a regular get search
	    var form = $(this);
	    var action = form.attr('action');

	    narrow_search = ( action.length == 0 ||  window.location.href.indexOf( action ) > 0);

	    if(narrow_search)
		e.preventDefault();
	    else
		return;

	    // Get value from search field
	    var qs = form.find('#shopello_search_field').val();

	    var cparams = urlparameters();
	    if(!cparams)
		cparams = {};

	    cparams.keyword = qs;
	    location.href = urlpath() + '?' + $.param(cparams);
	});

	$(".shopello_product").click(function( e ){
	    window.open( $(this).find("a").attr('href') );
	});

    });

})(jQuery);
