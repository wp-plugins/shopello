(function($) {
    $(document).ready(function() {
	// Put some common elements into variables for easier access / readability
	var options_form     = $('#swp_options_form');
	var pagesize_select  = $('#preview_per_page');
	var sort_order       = $('#preview_sort_order');
	var sort             = $('#preview_sort');
	var price_roof       = $('#price-interval');
	var shortcode        = $('#shortcode');
	var shortcode_table  = $('#shortcode_table');
	var save_button      = '#save_button';
	var filter_box       = $('#shopello_filter_wrap');
	var preview_box      = $('#preview_box');
	var sc_remove_btns   = $('.form-table.lists .button.remove');
	var sc_name_fields   = $('.form-table.lists .swp_item_name');
	var color_radios     = $('.colors input[type=radio]');
	var keyword_input    = $('#keyword');
	var submit_button    = $('p.submit input[type="submit"]');
	var categories_wrap  = $('.leftcol .categories');
	var category_ids_cbs = categories_wrap.find('input[type="checkbox"]');
	var working_spinner  = $('.swp_modal.loader');
	var btn_test_settings= $('#test_settings');
	var inp_swp_key      = $("#swp_api_key");
	var inp_swp_endpoint = $("#swp_api_endpoint");
	var swp_status_field = $("#swp_settings_status");
	var sync_cat_btn     = $("#swp_cat_sync");
	var swp_status_indicator = $(".settings_status");
	var cat_list_triggers = ".filter-categories ul label";

	// Row counting and max-count of rows
	var total_shortcodes_max = parseInt($('#SAPI_SC_MAX').val(), 10) || 25;
	var total_shortcodes     = shortcode_table.find('tr').length;

	// Init a new shortcode generator
	var generator = new SWP_Api_Generator({
            is_admin : true,
            auto_update_url : false
        });

	// Bind category sync button
	sync_cat_btn.click(function(){

	    var btn = $(this);
	    var btn_label = btn.val();
	    var label = $("#swp_cat_sync_status");

	    btn.val(adminL10n.working_button);
	    btn.attr('disabled', true);

	    $.ajax({
		url : ajaxurl,
		type : 'post',
		dataType : 'html',
		data : {
		    action : 'sync_categories'
		}
	    })
		.done(function( r ){
		    label.html( r );
		    btn.val(btn_label);
		    btn.attr('disabled', false);
		})
		.fail(function( e ){
		    console.error(e);
		    btn.val(btn_label);
		    btn.attr('disabled', false);
		});
	});

	// Test account settings
	btn_test_settings.click(function(){

	    if( inp_swp_key.length && inp_swp_endpoint.length ){

		var args = {
		    action   : "test_api_settings",
		    endpoint : inp_swp_endpoint.val(),
		    key      : inp_swp_key.val()
		};

		$.ajax({
		    url: ajaxurl,
		    type: 'post',
		    dataType: 'json',
		    data: args
		})
		    .done(function( r ) {

			swp_status_indicator.addClass('tested');
			swp_status_indicator.toggleClass('good', r.status);

			swp_status_field.val( r.status );
		    })
		    .fail(function( e ) {
			console.error(e);
		    });


	    }
	});

	// Make sure forms send with ajax
	options_form.submit(function() {
	    ajax_store_options();
	});

	// Detect changes and store.. (is this still working?)
	/**
	 * Listen for server args changes (filter changes)
	 * and refresh results and filters dynamically
	 */
	generator.listen( generator.EVENTS.CHANGE , function( server_args ){
	    // Load new search results
	    generator.load_api_result( $("#preview_box") );

	});

	generator.listen( generator.EVENTS.LOADED, function( response ){
	    console.log(response);
	});

	// Initial bindings and event listeners
	bind_events_to_row();
	bind_save_button();

	function bind_save_button(){
	    /*
	      When user wants to save a generated shortcode
	    */
	    $(cat_list_triggers).off('click.swp').on('click.swp', function(){
		var l = $(this).next('ul');
		if(l.is(':visible'))
		    l.slideUp();
		else
		    l.slideDown();
	    });

	    $(save_button).off('click.swp').on('click.swp', function() {

		if( max_shortcodes_exceeded() ) {
                    alert(adminL10n.max_listings.replace('%d', total_shortcodes_max));
		    return;
		}

		// Save page!
		var args = generator.get_server_args('save_item');
		args.name = prompt(adminL10n.save_prompt);

		$.post(ajaxurl, args, function(response) {
		    response = $.parseJSON( response );

		    var $table = $('.form-table.lists');
		    var $row = get_blank_row();

		    $row.find('.swp_item_name').val(response.data.name);
		    $row.find('.swp_item_desc').html(response.data.description);
		    $row.find('.swp_item_id').val(response.data.id);

		    $table.append($row);
		    bind_events_to_row($row);

		    $("#swp_list").val(response.serialized);

		    // Store admin changes
		    ajax_store_options_silent();

		});
	    });
	}

	/*
	  Method to store options via ajax.
	*/
	function ajax_store_options( silent ) {

	    working_spinner.fadeIn(250);

	    options_form.ajaxSubmit({

		complete : function(e, status){

		    if( status == "success" ) {
			if(! silent) {

			    var modal_wrap = $('#modal_wrap');
			    var modal      = $("<div id='saveMessage' class='swp_modal'></div>");

			    modal_wrap.html( modal );
			    modal.append("<p>Inställningarna sparades</p>").show();
			}
		    }

		    working_spinner.fadeOut(250);
		},
		timeout: 5000
	    });

	    if(! silent )
		setTimeout("jQuery('#saveMessage').fadeOut(300);", 2500);

	    return false;
	}
	/*
	  Alias method for silent save
	*/
	function ajax_store_options_silent() {
	    ajax_store_options( true );
	}

	/*
	  Method to bind new list-rows eventhandling
	*/
	function bind_events_to_row( $row ) {

	    if(! $row ) $row = $('.form-table.lists tr');

	    bind_item_namechange( $row.find('.swp_item_name'));
	    bind_remove_btns( $row.find('.button.remove'));
	}

	/*
	  Method to re-bind (or bind) remove-buttons in the shortcode table
	*/
	function bind_remove_btns( b ) {

	    // use passed button or all buttons?
	    var btns = b ? b : sc_remove_btns;

	    // assign listener
	    btns.click(function(){

		var self = $(this);

		var args = {};
		args.id = $(this).siblings(".swp_item_id").val();
		args.action = "remove_item";

		$.post(ajaxurl, args, function(response) {
		    response = $.parseJSON( response );

		    if(response.success) {
			self.closest('tr').animate({opacity:0, height:0}, function(){
			    $(this).remove();

			    console.log(response.serialized);
			    $("#swp_list").val(response.serialized);

			    // Store admin changes
			    ajax_store_options_silent();
			});
		    }
		});
	    });
	}

	function bind_item_namechange( tf ) {

	    // use passed button or all buttons?
	    var tf = tf ? tf : sc_name_fields;

	    tf.each( function( ix, item ){
		$field = $(item);
		$field.change(function(){

		    $field = $(this);

		    var args    = {};
		    args.action = "edit_item";
		    args.id     = $field.closest('tr').find(".swp_item_id").val();
		    args.name   = $field.val();

		    $.post(ajaxurl, args, function(response) {

			response = $.parseJSON( response );

			// Store admin changes
			if( response.success ) {
			    console.log(response.serialized);
			    $("#swp_list").val(response.serialized);
			    ajax_store_options_silent();
			}
			else
			    alert(adminL10n.save_error);
		    });
		});
	    });
	}

	function get_blank_row() {
	    return $('<tr><td width="15%"><input type="text" name="swp_shortcodes_names[]" class="swp_item_name" value="" /></td><td width="40%"><span class="swp_item_desc"></span></td><td width="10%"><input type="hidden" name="swp_item_id" class="swp_item_id" value=""/><input type="button" class="button remove" value="Ta bort" /></td></tr>');
	}

	/*
	  If we have a determined number of max rows, based on that - return if we've exceeded it or not
	*/
	function max_shortcodes_exceeded() {
	    return (total_shortcodes_max != -1 && shortcode_table.find('tr').length >= total_shortcodes_max);
	}
    });
})(jQuery);
