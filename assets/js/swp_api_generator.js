/*
  SWP_Api_Generator class definition.
  An interface to modify and create shortcodes in javascript
*/

/**
 * SWP_Api_Generator class handles parameter intake, value changes and API calls for a backend API.
 */

// Array extension
Array.prototype.contains = function (i) {
    return (this.indexOf(i) !== -1);
};


/* ------------------------------------------------------ */
/* ------------------ Class Definition ------------------ */
/* ------------------------------------------------------ */


var SWP_Api_Generator = function (args) {
    // Internal variables
    this.keyword      = false;
    this.color        = false;
    this.pricemax     = false;
    this.pagesize     = 16;
    this.page         = 1;
    this.categories   = [];
    this.filters      = [];
    this.brands       = [];
    this.sort         = "";
    this.sort_order   = "";
    this.pending_requests  = {};
    this.available_filters = ["categories","brands","pagesize","pricemax","keyword","sorting","colors"];
    this.auto_update_url   = false;
    this.is_admin     = false;
    this.ajax_url     = scriptL10n['ajax-uri'];
    this._result_wrap = '#shopello_products_wrap';
    this._filter_wrap = '#shopello_filter_wrap';
    this._has_html5   = true;
    this.EVENTS       = {
        // Use when server args/query object is changed
        CHANGE : "swp_query_change",
        LOADED : "swp_query_done",
    }

    // Set passed arguments to this
    if (args) {
        for (var i in args) {
            if (typeof this[i] !== undefined) {
                this[i] = args[i];
            }
        }
    }


    this._event_listeners = {};

    // Init some basic input listeners.
    this._create_input_listeners();

    // Check for HTML5 features
    this._has_html5 = (typeof window.history.pushState == "function");

    // Attach event listener for window navigation events
    if (this._has_html5 && !this.is_admin) {
        // Load from eventual hash url.
        var reload = false;
        this.auto_update_url = true;
        this._load_parse_url( reload );

        // Listen for hash url navigation changes
        self = this;
        window.onpopstate = function() {
            self._load_parse_url();
        }
    } else {
        // No unexpected uncaught history navigation can mess
        // Simply read the URL once
        var reload = false;
        this.auto_update_url = false;

        if (!this.is_admin) {
            this._load_parse_url(reload);
        }
    }
};


/* ------------------------------------------------------ */
/* --------------- PUBLIC INVOKING METHODS ------------*- */
/* ------------------------------------------------------ */


/**
 * Alias method for private method _create_input_listeners()
 * @return {void}
 */
SWP_Api_Generator.prototype.rebind = function () {
    this._create_input_listeners();
};

/**
 *
 * Method to load values from URL. If they differ, request correct page from backend
 * @param reload {bool} defaults to true
 * @return {void}
 **/

SWP_Api_Generator.prototype._load_parse_url = function (reload) {
    var allowed_params = ["keyword", "color", "pricemax", "pagesize", "page", "categories", "brands", "sort", "sort_order" ];
    var params = window.location.href.replace(/^.*\?/,"").split("&");

    // If no get params, cancel
    if (params.length == 0) {
        return;
    }

    // If reload flag not set, set to false
    if (!reload) {
        reload = true;
    }

    // Flag to know if url params differs with this object
    var corresponds = true;
    var url_options_clean = {};

    // Set the params that were available
    for (var i = 0; i < params.length; i++) {
        // Split item into key value pair
        params[i] = params[i].split("=");
        var key = params[i][0];
        var val = params[i][1];

        // See that it's a valid variable
        if (allowed_params.contains(key)) {
            // Parse array values to ints
            if (['categories','brands'].contains(key)) {
                val = val.split(",").map(Number);
            }

            url_options_clean[key] = val;

            // Check if url params differ from current ones
            if (this[key] !== val) {
                corresponds = false;
            }
        }
    }

    // If they do not match
    if (!corresponds) {
        // Set the url params to our object
        for (var i in url_options_clean) {
            this[i] = url_options_clean[i];
        }

        // In HTML5 browsers, reload via ajax
        console.log('load_parse_url done. Reload = ' + reload.toString());
        if (reload == true) {
            // And reload results
            this.load_api_result();
            this.load_api_filters();
        }
    }
}



/* ------------------------------------------------------ */
/* ----------------------- SETTERS ---------------------- */
/* ------------------------------------------------------ */


/**
 * [set_keyword description]
 * @param {[type]} str [description]
 */
SWP_Api_Generator.prototype.set_keyword = function (str) {
    if (str && str.length > 0) {
        this.keyword = str;
    } else {
        this.keyword = false;
    }

    // check for changes
    this._change();
};

SWP_Api_Generator.prototype.get_post_id = function () {
    var id = parseInt(jQuery(this._result_wrap).data('post-id'), 10);

    return id && id > 0 ? id : false;
};

SWP_Api_Generator.prototype.set_color = function (color) {
    var colors = [false, "vit","grå","svart","blå","grön","gul","orange","röd","rosa","lila"];

    // If color is valis, set it on generator object.
    if (colors.indexOf( color ) !== -1) {
        this.color = color;
    } else {
        console.error("Color " + color + " is not valid.");
    }

    // check for changes
    this._change();
};

SWP_Api_Generator.prototype.toggle_category = function (category_id) {
    // Make sure it's a valid ID
    if(category_id === parseInt(category_id, 10)) {
        // if not in array, add it!
        if (!this.categories.contains(category_id)) {
            this.categories.push(category_id);
        } else { // Otherwise, remove it
            var ix = this.categories.indexOf(category_id);
            this.categories.splice(ix, 1);
        }

        // check for changes
        this._change();
    } else { // If ID invalid, notify user by console error
        console.error("Invalid category_id: "+ category_id +" passed to SWP_Api_Generator.toggle_category()");
    }
};

SWP_Api_Generator.prototype.toggle_filter = function (filter) {
    // Make sure it's a valid ID
    filter = filter.toLowerCase().trim();
    if (this.available_filters.contains(filter)) {
        // if not in array, add it!
        if (!this.filters.contains(filter)) {
            this.filters.push(filter);
        } else { // Otherwise, remove it
            var ix = this.filters.indexOf(filter);
            this.filters.splice(ix, 1);
        }

        // check for changes
        this._change();
    } else { // If ID invalid, notify user by console error
        console.error("Invalid filter: "+ filter +" passed to SWP_Api_Generator.toggle_filter()");
    }
};

SWP_Api_Generator.prototype.set_pagesize = function (i) {
    if([16, 32, 64].indexOf(i) != -1) {
        this.pagesize = i;

        // check for changes
        this._change();
    }
};

SWP_Api_Generator.prototype.set_page = function(p) {
    if (parseInt(p, 10) !== p) {
        console.error('Invalid parameter for generator.set_page; ' + p);
    } else {
        this.page = p;
        this._change();
    }
}

SWP_Api_Generator.prototype.set_sort = function (str) {
    this.sort = str;
    this._change();
};

/**
 * Method to set / unset the price roof
 */
SWP_Api_Generator.prototype.set_price_max = function (v) {
    if(v === false || !jQuery.isNumeric(v)) {
        this.pricemax = false;
    } else {
        this.pricemax = v;
    }

    this._change();
};

SWP_Api_Generator.prototype.set_sort_order = function (str) {
    this.sort_order = str;
    this._change();
};


SWP_Api_Generator.prototype._create_input_listeners = function () {
    // Predefined selectors and change/click handlers for inputs related to the Generator
    var selectors = [
        {
            field  : '#input-keyword',
            method : 'set_keyword',
            parser : function ( v, el ) { return v.length > 0 ? v : false; }
        },
        {
            field  : '#swp_category_select',
            method : 'toggle_category',
            parser : function ( v, el ) { return parseInt(v, 10); }
        },
        {
            field  : '.filter-categories input[type="checkbox"]',
            method : 'toggle_category',
            parser : function ( v, el ) { return parseInt(v,10);}
        },
        {
            field  : '.filter-categories a',
            method : 'toggle_category',
            event  : 'click.swp',
            parser : function ( v, el ) { return parseInt( el.data('category-id'), 10);}
        },
        {
            field  : '#input-pagesize',
            method : 'set_pagesize',
            parser : function ( v, el ) { return parseInt(v,10);}
        },
        {
            field  : '#input-price-max',
            method : 'set_price_max',
            parser : function ( v, el ) { v=parseInt(v,10); jQuery('#label-price-max').html(v +" kr"); return v; }
        },
        {
            field  : '#reset-price-max',
            event  : 'click.swp',
            method : 'set_price_max',
            parser : function ( v, el ){ return false; } // false resets previous chouce
        },
        {
            field  : '#input-sort-field',
            method : 'set_sort',
            parser : false
        },
        {
            field  : '.shopello_paging li:not(.active):not(.disabled) a',
            method : 'set_page',
            event  : 'click.swp',
            parser : function( v, el ){ return parseInt(el.data('pagenum'), 10); }
        },
        {
            field  : '.shopello_paging li:not(.active):not(.disabled) a',
            method : 'set_page',
            event  : 'click.swp',
            parser : function( v, el ){ return parseInt(el.data('pagenum'), 10); }
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
                //if( checked ) jQuery(radio).data('checked', false );

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
    for (var i in selectors) {
        this.register_event_listener.call(this, selectors[i]);
    }
};

/**
 * Method to apply event listener and event action on field
 * @param  {Object} item [Custom object defined in start_input_listeners()]
 * @return {void}
 */
SWP_Api_Generator.prototype.register_event_listener = function (item) {
    if (!item.field) {
        return;
    }

    var field          = item.field;
    var event          = item.event  ? item.event : "change.swp";
    var method         = item.method ? item.method : "set_keyword";
    var parser         = item.parser ? item.parser : function (v, el) { return v; };
    var preventDefault = ['click', 'click.swp'].contains(item.event);

    // Scope-keeper
    var generator = this;

    // Proceed with method call
    jQuery(item.field).off(event).on(event, function (e) {
        // Click event should not lead to any link/submission or so.
        if (preventDefault) {
            e.preventDefault();
        }

        // Parse value if field has a parser
        var el  = jQuery(this);
        var val = parser(el.val(), el);

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
SWP_Api_Generator.prototype.get_server_args = function (act) {
    // Init argument object for ajax request.
    var args = {
        action     : act ? act : "sapi_get_listing",
        pagesize   : 16,
        sort       : 'price',
        sort_order : 'ASC'
    };

    // Sökord
    if (this.keyword) {
        args.keyword = this.keyword;
    }

    // Categories
    if (this.categories && this.categories.length > 0) {
        args.categories = this.categories.join();
    }

    // Pagesize
    if (this.pagesize) {
        args.pagesize = this.pagesize;
    }

    // Current page
    if (this.page > 1) {
        args.page = this.page;
    }

    // Price max
    if (this.pricemax) {
        args.pricemax = this.pricemax;
    }

    // Sorting
    if (this.sort) {
        args.sort = this.sort;
    }

    // Color
    if (this.color) {
        args.color = this.color;
    }

    // Ordering
    if (this.sort_order) {
        args.sort_order = this.sort_order;
    }

    // Post ID
    if (this.get_post_id()) {
        args.post_id = this.get_post_id();
    }

    // Selected frontend-filters (if any)
    if (this.filters.length > 0) {
        args.filters = this.filters.join();
    }

    // Return argument object
    return args;
};



/**
 * Set URL. Either hashbang style OR get param style, depending on browser
 */
SWP_Api_Generator.prototype.update_url = function () {
    var data    = this.get_server_args();
    var pairs   = [];
    var trail   = "#!/?";
    var url     = window.location.href;

    if(this._has_html5) {
        if (url.indexOf("#") > 0) {
            url = url.split("#")[0];
        } else {
            url = url.split("?")[0];
        }
    } else {
        url = url.split("?")[0].replace("#!/","");
    }

    // Cannot work with
    if (!this._has_html5) {
        trail = "?";
    }

    // Work through the parameters
    for (var i in data) {
        if (this.available_filters.contains(i)) {
            pairs.push(i + "=" + data[i]);
        }
    }

    // join all params into a string
    trail += pairs.join("&");

    // Set the url
    window.location.href = url + trail;

    // Reload everything from server
    this.load_api_result();
    this.load_api_filters();
}


/* ------------------------------------------------------ */
/* ------------------------ AJAX ------------------------ */
/* ------------------------------------------------------ */


// Load new result content to a div
SWP_Api_Generator.prototype.load_api_result = function ($el, args, cb) {
    var self = this;
    var xhr_id = "load_api_result";

    // Compose server args
    if (!args) {
        args = this.get_server_args();
    }
    args.html_only = true;

    // Default result wrap
    if (!$el) {
        $el = jQuery(this._result_wrap);
    } else {
        $el = jQuery($el);
    }

    $el.parent().height($el.parent().outerHeight());
    $el.fadeOut(200);


    // Call cancelable ajax so HTML is not replace on every filter click.
    this.do_cancelable_ajax( args, xhr_id, function (response) {
        this.trigger(this.EVENTS.LOADED,[response]);

        // Request done.
        // Reattach event listeners and bind html
        $el.html(response.products);

        $el.fadeIn(200, function() {
            $el.parent().height('auto');
        });
        this.rebind();

        // Callback
        if(cb) cb();
    });
};

// Load new filter content to a div
SWP_Api_Generator.prototype.load_api_filters = function ($el, args, cb) {
    var self = this;
    var xhr_id = "load_api_filters";

    // Compose server args
    if(!args ) args = this.get_server_args();
    args.action = 'sapi_get_filters';
    args.html_only = true;

    // Default filter wrap
    if(!$el) $el = jQuery(this._filter_wrap);

    // Call cancelable ajax so HTML is not replace on every filter click.
    this.do_cancelable_ajax(args, xhr_id, function (response) {
        // Request done.
        // Reattach event listeners and bind html
        $el.html(response.html);
        self.rebind();

        // Callback
        if(cb) cb();
    });
};


// Load new filter content to a div
SWP_Api_Generator.prototype.do_cancelable_ajax = function (args, xhr_id, cb, err_cb) {
    // For scope-keeeping in callback
    var self = this;

    var call = jQuery.ajax({
        // Standard ajax url
        url     : scriptL10n.ajax_url,
        // Request method
        type    : "post",
        // Data object for request
        data    : args,
        dataType: 'json',
        // All well, call callback if having one
        success : function () {
            if(cb) cb.apply(self, arguments);
        },
        // Something wrong, call error callback if having one
        error   : function () {
            if(err_cb) {
                err_cb.apply(self, arguments);
            }
        }
    });

    // Cancel eventual previous XHR requests with same ID
    if (xhr_id !== false) {
        if (this.pending_requests[xhr_id]) {
            // Abort previous call
            if (this.pending_requests[xhr_id].abort) {
                this.pending_requests[xhr_id].abort();
            }
        }
        // Save this call
        this.pending_requests[xhr_id] = call;
    }
};


/* ------------------------------------------------------ */
/* ----------------------- EVENTS ----------------------- */
/* ------------------------------------------------------ */


/**
 * Internal change-handler for shortcode publication
 */
SWP_Api_Generator.prototype._change = function () {
    // trigger change event with new server args, to the wooorld
    this.trigger(this.EVENTS.CHANGE, [ this.get_server_args() ]);

    // Update the URL to correspond to selected filter choices
    if(this.auto_update_url) {
        this.update_url();
    }
};

SWP_Api_Generator.prototype.listen = function (e, func) {
    if(!this._event_listeners[e]) {
        this._event_listeners[e] = [];
    }

    this._event_listeners[e].push(func);
};

/*
  Connect to this method to retrieve server_arguments upon change!
*/
SWP_Api_Generator.prototype.trigger = function (method, args) {
    // If event didn't exist, break here.
    if(!this._event_listeners[method]) {
        console.log(":Dev note: " +method +" was not an event on SWP_Api_Generator.");
        return;
    }

    console.log("Looking to trigger " + method);
    // Call each callback with no scope and the args passed to this trigger
    for (var i in this._event_listeners[method]) {
        var f = this._event_listeners[method][i];
        if (typeof f == "function") {
            f.apply(args);
        } else {
            console.error(f + " is not a function");
        }
    }
};
