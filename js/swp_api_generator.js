 /*
	SWP_Api_Generator class definition.
	An interface to modify and create shortcodes in javascript
*/

/**
 * SWP_Api_Generator class handles parameter intake, value changes and API calls for a backend API.
 */

// Array extension
Array.prototype.contains = function (i){ return (this.indexOf(i) !== -1);};


/* ------------------------------------------------------ */
/* ------------------ Class Definition ------------------ */
/* ------------------------------------------------------ */


var SWP_Api_Generator = function (){

	// Internal variables
	this.keyword      = false;
	this.color        = false;
	this.price_max  = false;
	this.pagesize     = 16;
	this.categories   = [];
	this.filters      = [];
	this.brands       = [];
	this.pending_requests = {};
	this.sort         = "";
	this.sort_order   = "";
	this.available_filters = ["categories","brands","pagesize","pricemax","keyword","sort_order","sort","color"];
	this._result_wrap = '#shopello_products_wrap';
	this._filter_wrap = '#shopello_filter_wrap';
	this.ajax_url     = "/wp-admin/admin-ajax.php";
	this.EVENTS       = {
		// Use when server args/query object is changed
		CHANGE : "swp_query_change"
	}
	this._event_listeners = {};

	// Init some basic input listeners. 
	// PRESUMING JQUERY IS LOADED!!
	this._create_input_listeners();
};


/* ------------------------------------------------------ */
/* --------------- PUBLIC INVOKING METHODS ------------*- */
/* ------------------------------------------------------ */


/**
 * Alias method for private method _create_input_listeners()
 * @return {void}
 */
SWP_Api_Generator.prototype.rebind = function (){
	this._create_input_listeners();
};



/* ------------------------------------------------------ */
/* ----------------------- SETTERS ---------------------- */
/* ------------------------------------------------------ */


/**
 * [set_keyword description]
 * @param {[type]} str [description]
 */
SWP_Api_Generator.prototype.set_keyword = function ( str ) {
	if( str && str.length > 0 ) 
		this.keyword = str;
	else
		this.keyword = false;

	// check for changes
	this._change();
};

SWP_Api_Generator.prototype.get_post_id = function () {
	var id = parseInt(jQuery(this._result_wrap).data('post-id'),10);
	return id && id > 0 ? id : false;
};

SWP_Api_Generator.prototype.set_color = function ( color ) {
	var colors = [false, "vit","grå","svart","blå","grön","gul","orange","röd","rosa","lila"];
	
	// If color is valis, set it on generator object.
	if( colors.indexOf( color ) !== -1)
		this.color = color;
	else 
		console.error("Color " + color + " is not valid.");

	// check for changes
	this._change();
};

SWP_Api_Generator.prototype.toggle_category = function ( category_id ) {
	
	// Make sure it's a valid ID 
	if( category_id === parseInt(category_id, 10) ) {
		
		// if not in array, add it!
		if(!  this.categories.contains( category_id ))
			this.categories.push(category_id);
		
		// Otherwise, remove it
		else {
			var ix = this.categories.indexOf(category_id);
			this.categories.splice(ix, 1);
		}

		// check for changes
		this._change();
	}
	// If ID invalid, notify user by console error
	else {
		console.error("Invalid category_id: "+ category_id +" passed to SWP_Api_Generator.toggle_category()");
	}
};

SWP_Api_Generator.prototype.toggle_filter = function ( filter ){
	// Make sure it's a valid ID 
	if( this.available_filters.contains(filter) ) {
		
		// if not in array, add it!
		if(!  this.filters.contains( filter ))
			this.filters.push(filter);
		
		// Otherwise, remove it
		else {
			var ix = this.filters.indexOf( filter );
			this.filters.splice(ix, 1);
		}

		// check for changes
		this._change();
	}
	// If ID invalid, notify user by console error
	else {
		console.error("Invalid filter: "+ filter +" passed to SWP_Api_Generator.toggle_filter()");
	}
};

SWP_Api_Generator.prototype.set_pagesize = function ( i ) {
	if( [16,32,64].indexOf(i) != -1 ) {
		this.pagesize = i;

		// check for changes
		this._change();
	}
};

SWP_Api_Generator.prototype.set_sort = function ( str ) {
	this.sort = str;
	this._change();
};

/*
	Method to set / unset the price roof
 */
SWP_Api_Generator.prototype.set_price_max = function ( v ) {
	if( v === false || ! jQuery.isNumeric(v))
		this.price_max = false;
	else 
		this.price_max = v;

	this._change();
};

SWP_Api_Generator.prototype.set_sort_order = function ( str ) {
	this.sort_order = str;
	this._change();
};


SWP_Api_Generator.prototype._create_input_listeners = function () {

	// Predefined selectors and change/click handlers for inputs related to the Generator
	var selectors = 
	[
		{
			field : '#input-keyword',
			method : 'set_keyword',
			parser : function ( v, el ) { return v.length > 0 ? v : false; }
		},
		{
			field : '#swp_category_select',
			method : 'toggle_category',
			parser : function ( v, el ) { return parseInt(v, 10); }
		},
		/*{
			field : '.s_cat_sel a',
			method : 'toggle_category',
			event  : 'click.swp',
			parser : function ( v, el ) { return parseInt(jQuery(el).data('value'), 10); }
		},*/
		{
			field : '.filter-categories input[type="checkbox"]',
			method : 'toggle_category',
			parser : function ( v, el ) { return parseInt(v,10);}
		},
		{
			field : '.filter-categories A',
			method : 'toggle_category',
			event  : 'click.swp',
			parser : function ( v, el ) { return parseInt( el.data('category-id'), 10);}
		},
		{
			field : '#input-pagesize',
			method : 'set_pagesize',
			parser : function ( v, el ) { return parseInt(v,10);}
		},
		{
			field : '#input-price-max',
			method : 'set_price_max',
			parser : function ( v, el ) { v=parseInt(v,10); jQuery('#label-price-max').html(v +" kr"); return v; }
		},
		{
			field : '#reset-price-max',
			event : 'click.swp',
			method : 'set_price_max',
			parser : function ( v, el ){ return false; } // false resets previous chouce
		},
		{
			field : '#input-sort-field',
			method : 'set_sort',
			parser : false
		},
		{
			field : '#input-sort-order',
			method : 'set_sort_order',
			parser : false
		},
		{
			field : '.filter-filters input[type="checkbox"]',
			method : 'toggle_filter',
			parser : false
		},
		{
			field : '.filter-color input[type="radio"]',
			event : 'click.swp',
			method : 'set_color',
			parser : function ( v, el ) {
				var radio   = jQuery(el);
				var val     = radio.val();
				var checked = ! Boolean(radio.data("checked"));

				// Uncheck all others if this one gets checked
				if( checked ) jQuery(this.field).data('checked', false );

				// Toggle checked on this radio button
				radio.attr('checked', checked);
				radio.data('checked', checked);

				// set current color selection
				return checked ? val : false;
			}
		}
	];

	// Iterate all field selector objects and assign listeners
	// that will take values on interaction and store in generator instance
	for( var i in selectors ){

		this.register_event_listener.call( this, selectors[i] );
	}
};

/**
 * Method to apply event listener and event action on field
 * @param  {Object} item [Custom object defined in start_input_listeners()]
 * @return {void}
 */
SWP_Api_Generator.prototype.register_event_listener = function ( item ) {

	if(! item.field )
		return;

	var field          = item.field;
	var event          = item.event  ? item.event : "change.swp";
	var method         = item.method ? item.method : "set_keyword";
	var parser         = item.parser ? item.parser : function (v,el){return v;};
	var preventDefault = item.event == "click" ? true : false;

	// Scope-keeper
	var generator = this;

	// Proceed with method call
	console.log("Conencted field");
	jQuery(item.field).off(event).on(event, function ( e ){

		// Click event should not lead to any link/submission or so.
		if( preventDefault )
			e.preventDefault();
		
		// Parse value if field has a parser
		var el  = jQuery(this);
		var val = parser( el.val(), el);

		// Call defined method to set/unset values on generator
		generator[method].call( 
			generator, 
			val,
			el
		);
	});
};

/* ------------------------------------------------------ */
/*--------------------- GET-Methods --------------------- */
/* ------------------------------------------------------ */



/*
	AJAX Get html code for current preview setting
*/
SWP_Api_Generator.prototype.get_server_args = function ( act ){

	// Init argument object for ajax request.
	var args = { 
		action     : act ? act : "sapi_get_listing",
		pagesize   : 16,
		sort       : 'price',
		sort_order : 'ASC'
	};
	
	// Querystring based on:  keyword +/ color
	// Color är inbyggt här
	if(this.get_querystring())
		args.keyword = this.get_querystring();

	// Categories
	if(this.categories && this.categories.length > 0)
		args.categories = this.categories.join();
	
	// Pagesize
	if(this.pagesize)
		args.pagesize = this.pagesize;

	// Price max
	if(this.price_max)
		args.price_max = this.price_max; 

	// Sorting
	if(this.sort)
		args.sort = this.sort;

	// Color
	if(this.color)
		args.color = this.color;

	// Ordering
	if(this.sort_order)
		args.sort_order = this.sort_order;

	// Post ID 
	if( this.get_post_id() )
		args.post_id = this.get_post_id();

	// Selected frontend-filters (if any)
	if( this.filters.length > 0) 
		args.filters = this.filters.join();

	// Return argument object
	return args;
};



/*
	Set Hashbang url
*/
SWP_Api_Generator.prototype.set_url_hash = function (){

	var data    = this.get_server_args();
	var pairs   = [];
	var hash    = "#!/?";

	console.log(data);

	for( var i in data) {
		if( this.available_filters.contains( i ))
			pairs.push( i + "=" + data[i]);
	}

	hash += pairs.join("&");

	console.log(pairs);

	var url = window.location.href.split("#")[0];
	window.location.href = url + hash;
}

/*
	Method to compose querystring. Mostly for appending color filter
 */
SWP_Api_Generator.prototype.get_querystring = function () {

	var q = "";

	if(this.keyword)
		q += this.keyword;

	//if(this.color)
	//	q += (q !== "" ? " ":"") + this.color;

	// If no querystring was generated, return false
	return q.length > 0 ? q : false;
};


/* ------------------------------------------------------ */
/* ------------------------ AJAX ------------------------ */
/* ------------------------------------------------------ */


// Load new result content to a div
SWP_Api_Generator.prototype.load_api_result = function ( $el, args, cb ) {

	var self = this;
	var xhr_id = "load_api_result";
	
	// Compose server args
	if(!args ) 
		args = this.get_server_args();
	args.html_only = true;

	$el.fadeOut(200);

	// Call cancelable ajax so HTML is not replace on every filter click.
	this.do_cancelable_ajax( args, xhr_id, function ( response ){
		// Request done.
		// Reattach event listeners and bind html
		$el.html( response.products );
		$el.fadeIn(200);
		this.rebind();

		// Callback
		if(cb) cb();
	});
};

// Load new filter content to a div
SWP_Api_Generator.prototype.load_api_filters = function ( $el, args, cb ) {

	var self = this;
	var xhr_id = "load_api_filters";
	
	// Compose server args
	if(!args ) 
		args = this.get_server_args();
	args.action = 'sapi_get_filters';
	args.html_only = true;

	// Call cancelable ajax so HTML is not replace on every filter click.
	this.do_cancelable_ajax( args, xhr_id, function ( response ){
		// Request done.
		// Reattach event listeners and bind html
		$el.html( response.html );
		self.rebind();

		// Callback
		if(cb) cb();
	});
};


// Load new filter content to a div
SWP_Api_Generator.prototype.do_cancelable_ajax = function ( args, xhr_id, cb, err_cb ){
	
	// For scope-keeeping in callback
	var self = this;

	var call = jQuery.ajax({
		// Standard ajax url
		url     : this.ajax_url,
		// Data object for request
		data    : args,
		dataType: 'json',
		// All well, call callback if having one
		success : function (){ 
			if( cb ) cb.apply( self, arguments ); 
		},
		// Something wrong, call error callback if having one
		error   : function (){ if( err_cb ) err_cb.apply( self, arguments ); }
	});

	// Cancel eventual previous XHR requests with same ID
	if( xhr_id !== false ) {
		if( this.pending_requests[ xhr_id ] ) {
			// Abort previous call
			if( this.pending_requests[ xhr_id ].abort ){
				this.pending_requests[ xhr_id ].abort();	
			}
		}
		// Save this call
		this.pending_requests[ xhr_id ] = call;
	}
};


/* ------------------------------------------------------ */
/* ----------------------- EVENTS ----------------------- */
/* ------------------------------------------------------ */


/*
	Internal change-handler for shortcode publication
*/
SWP_Api_Generator.prototype._change = function (){

	// trigger change event with new server args, to the wooorld
	this.trigger(this.EVENTS.CHANGE, [ this.get_server_args() ] );

	// Update the URL to correspond to selected filter choices
	this.set_url_hash();
};

SWP_Api_Generator.prototype.listen = function ( e, func ) {

	if( ! this._event_listeners[e] )
		this._event_listeners[e] = [];

	this._event_listeners[e].push( func );
};

/*
	Connect to this method to retrieve server_arguments upon change!
*/
SWP_Api_Generator.prototype.trigger = function ( method, args ){

	// If event didn't exist, break here.
	if( ! this._event_listeners[ method ]) {
		console.log(":Dev note: " +method +" was not an event on SWP_Api_Generator.");
		return;	
	}

	// Call each callback with no scope and the args passed to this trigger
	for( var i in this._event_listeners[ method ]) {
		var f = this._event_listeners[ method ][i];
		if( typeof f == "function")
			f.apply( args );
	}
};