<?php
/*
	Method to read template part tp variable
 */
function load_template_part($template_name, $part_name=null) {
    ob_start();
    get_template_part($template_name, $part_name);
    $var = ob_get_contents();
    ob_end_clean();
    return $var;
}


function required_options_check() {
	$api_key      = get_option('sapi_api_key');
	$api_endpoint = get_option('sapi_api_endpoint');

	$error = "";

	if(! $api_key ) 
		$error .= "Ingen API-nyckel har angivits. Var god ange din API-nyckel och spara inställningarna. Har du ingen API-nyckel så kan du kontakta Shopello AB för att erhålla en.";
	if(! $api_endpoint )
		$error .= "Ingen API-endpoint har angivits. Var god ange din API-endpoint och spara inställningarna. Har du ingen API-endpoint så kan du kontakta Shopello AB för att erhålla en.";

	if( strlen($error) > 0 ) {	
		print "<div class='message-error'>";
		print $error;
		print "</div>";
	}
}

function get_post_swp_item( $post_id = false ) {

	if( ! $post_id ) {
		$post_id = get_the_ID();
		if( ! $post_id )
			die('Could not retrieve Post ID in get_post_swp_item() method.');
	}

	// Make sure SWP class is available
	if(! class_exists("SWP")) 
		require_once(SHOPELLO_PLUGIN_DIR."classes/SWP.php");

	// Get SWP_Item ID from post meta
	$swp_list_id = get_post_meta( $post_id, '_swp_selected_list' );
	$swp_list_id = intval( is_array( $swp_list_id ) ? $swp_list_id[0] : $swp_list_id );

	// Try to fetch SWP_Item through SWP Class
	return SWP::Instance()->get_items($swp_list_id);
}

/**
 * This method appends the get and post parameters possibly passed to this request
 * @return [type] [description]
 */
function shopello_sanitize_params( $params ) {

	// Querstring?
	if( request('keyword') ){
		$params['keyword'] = request('keyword');
		$params['query']  .= strlen(request('keyword')) > 0 ? " ".request('keyword') : "";
	}

	if( request('color') ){
		$params['color'] = request('color');
		$params['query']  .= strlen(request('color')) > 0 ? " ".request('color') : "";
	}

	// Category-limitation?
	if( request('categories') )
		$params['categories'] = request('categories');

	// Sorting order requested?
	if( request('sort_order'))
		$params['order'] = request('sort_order');

	// Sort by something special?
	if( request('sort'))
		$params['order_by'] = request('sort');

	if( request('price_max'))
		$params['price_max'] = request('price_max');

	// Paging stuff
	if( request('pagesize'))
		$params['pagesize'] = request('pagesize');

	// Calculate pagination variables
	$offset = 0;
	if( request('page'))
		$offset = request('page');

	$params['offset'] = $params['pagesize'] * $offset;
	$params['limit']  = $offset + $params['pagesize'];

	return $params;
}



// Not invented yet.. Meant to look for post <-> get variable, either that exists...
function shopello_filter_param( $str ) {
	// Get post/get parameter and return.
	$params = array(
		'keyword' => 's_qs',
		'brand'   => 's_bra',
		'categories' => 's_cat',
		'page'    => 's_page',
		'sale'    => 's_rea'
	);
}


/*
	Product rendering metod
 */
function shopello_render_products( $api_result, $params = false ) {

	// Get option values for result text
	$title = get_option('swp_result_title');

	// Make sure we have the params that were used this request
	if( ! $params )
		$params = shopello_sanitize_params( SWP::Instance()->get_active_params() );

	// Insert variables to rubrik
	$productcount = $api_result->total_found ? intval($api_result->total_found) : "inga";
	$productcount = is_int($productcount) ? number_format(floatval($productcount), 0,  ".", " ") : $productcount;

	$querystring  = get('s_qs');

	if( $querystring ) $title .= " ".get_option('swp_keyword_title');

	$prod_count_suffix_label = ($productcount == 1) ? "produkt" : "produkter";
	$title = preg_replace("#produkter#", $prod_count_suffix_label, $title);
	$title = preg_replace("#\%antal#", $productcount, $title);
	$title = preg_replace("#\%ord#", $querystring, $title);

	// Get predefined markup parts
	$html_start_tag = load_swp_template('result/pre_result');
	$html_end_tag   = load_swp_template('result/post_result');

	// Get markup for product lists ( or empty result )
	if($api_result->status == 1) {

		
		// defined som reusable variables
		$tmpl_path = SHOPELLO_PLUGIN_DIR.'templates/result/product_grid.php'; // item file to load
		$ul_start = "<ul class='shopello_product_list'>"; // start-part of a row / list
		$ul_end   = "</ul>";
		$product_html = $ul_start; // html-variable to return later

		// Make globals accesssible
		global $product;

		$counter = 1;

		foreach($api_result->data as $product) {

			// Load template into $product_html string
			ob_start();
				include( $tmpl_path );
				$product_html .= ob_get_contents();
			ob_end_clean();

			// Split list rows
			if( $counter % 4 == 0 )
				$product_html .= $ul_end . $ul_start;

			// Keep count
			$counter++;
		}

		$product_html.= "</ul>";
	} else {
		$product_html = load_template_part( __DIR__."/templates/result/no_hits" );
	}

	// Setup and generate paging markup
	$pages = new Paginator;
	$pages->items_total = min($api_result->total_found, (1000 - $pagesize));
	$pages->items_per_page = $pagesize;
	$pages->mid_range = 9;
	$pages->paginate();

	$title = '<div class="shopello_result_message">'.$title.'</div>';

	$pagination = "<div class='shopello_paging'><ul class='pagination'>";
	$pagination.= $pages->display_pages();
	$pagination.= "</ul></div>";

	// The full markup
	return    $html_start_tag
			. $title 
			. $product_html 
			. $pagination
			. $html_end_tag;

}

function shopello_render_filters( $params ){

	// Get API data from last/this page's listing
	$api_response = SWP::Instance()->run_query( $params );
	
	// Put array in variables, for template rendering
	extract($params);

	$max_price = $extra->max_price ? $extra->max_price : 5000;
    
	// All categories in the system
	$api_categories = $api_response->extra->categories; //shopello_get_base_categories();

	// Set available brands from response
	$api_brands = $api_response->extra->brands;

	// Load template into $html string
	$tmpl_path = SHOPELLO_PLUGIN_DIR.'templates/result/filters.php'; // item file to load
	
	// Read in everything
	ob_start();
	include( $tmpl_path );
	$html = ob_get_contents();
	ob_end_clean();


	// Set selected status on selected categories
	$selected_categories = $params['categories'];
	if( is_object($selected_categories)) {

		foreach( $selected_categories as $cat_id ) {
			$api_categories->$cat_id->selected = true;
		}
	}

	return $html;
}


//truncate a string only at a whitespace (by nogdog)
function truncate($text, $length, $hard = false) {
   $length = abs((int)$length);
   if(strlen($text) > $length) {
   		if($hard)
   			$text = substr($text, 0, $length). "...";
   		else
   			$text = preg_replace("/^(.{1,$length})(\s.*|$)/s", '\\1...', $text);
   }
   return($text);
}


/*
	Load a template from this plugin
 */
function load_swp_template( $template_path ){
	$full_path = SHOPELLO_PLUGIN_DIR."templates/" . $template_path;
	if(! file_exists($full_path)) {
		$full_path .= ".php";
		if(! file_exists($full_path)) 
			return false;
	}

	ob_start();
	include( $full_path );
	return ob_get_clean();
}


/*
	Core extension to locate templates in plugin folders
 */
function locate_plugin_template($template_names, $load = false, $require_once = true )
{
    if ( !is_array($template_names) ) {
    	if(is_string($template_names))
    		$template_names = array($template_names);
    	else
    		return '';
    }
    
    $located = '';
    
    $this_plugin_dir = WP_PLUGIN_DIR.'/'.str_replace( basename( __FILE__), "", plugin_basename(__FILE__) );
    
    foreach ( $template_names as $template_name ) {
        if ( !$template_name )
            continue;
        if ( file_exists(STYLESHEETPATH . '/' . $template_name)) {
            $located = STYLESHEETPATH . '/' . $template_name;
            break;
        } else if ( file_exists(TEMPLATEPATH . '/' . $template_name) ) {
            $located = TEMPLATEPATH . '/' . $template_name;
            break;
        } else if ( file_exists( $this_plugin_dir .  $template_name) ) {
            $located =  $this_plugin_dir . $template_name;
            break;
        }
    }
    
    if ( $load && '' != $located )
        load_template( $located, $require_once );
    
    return $located;
}


// Adds (i.e. overwrites if current)
function modifyGetStr($key, $val) {
	global $wp;
	$current_url = add_query_arg($wp->query_string, '', home_url($wp->request));
}

function removeGetStr($key, $val) {
	global $wp;
	$current_url = add_query_arg($wp->query_string, '', home_url($wp->request));
}


// CATEGORIES

function shopello_get_category_url($id) {
	if (shopello_is_active_category($id)) {
		return shopello_category_qs_del($id);
	} else {
		return shopello_category_qs_add($id);
	}
}

function shopello_is_active_category($id) {

	global $wp;

	if (isset($_GET['s_cat']) && $_GET['s_cat'] == $id)
		return true;

	return false;
}

// BRANDS
function shopello_get_brand_url($id) {
	if (shopello_is_active_brand($id)) {
		return shopello_brand_qs_del($id);
	} else {
		return shopello_brand_qs_add($id);
	}
}

function shopello_is_active_brand($id) {

	global $wp;

	if (isset($_GET['s_bra']) && is_array($_GET['s_bra']) && in_array($id, $_GET['s_bra']))
		return true;

	return false;
}

function shopello_get_base_categories(){
	
	$categories = $_SESSION['sapi_categories'];

	if(! $categories) {

		$shopello = new Shopello();
	    $shopello->set_api_key( get_option('sapi_api_key'));
	    $shopello->set_api_endpoint( get_option('sapi_api_endpoint'));

	    $categories = $shopello->categories();
	    if($categories->data)
	        $categories = $categories->data;
	    else
	        $categories = array();
	}
    
    return $categories;
}


function shopello_brand_qs_add($id) {

	$out = array();

	if (shopello_is_active_brand($id)) {
		return shopello_brand_del($id);
	}

	$has_k = false;
	foreach ($_GET as $key => $value) {

		if (is_array($value)) {
			if ($key == 's_bra') {
				$has_k = true;
				$value[] = $id;
			}

			$out[] = implode('&', array_map(function($v) use ($key) { return $key . '[]=' . urlencode($v); }, $value));

		} else {
			$out[] = $key . '=' . urlencode($value);
		}
	}

	if (!$has_k)
		$out[] = "s_bra[]=" . urlencode($id);

	return '?' . implode('&', $out);
}

function shopello_brand_qs_del($id) {

	$out = array();

	foreach ($_GET as $key => $value) {
		if (is_array($value)) {
			if ($key == 's_bra') {
				foreach ($value as $idx => $v) {
					if ($v == $id) {
						unset($value[$idx]);
						$value = array_values($value);
						break;
					}
				}
			}
			if (count($value) > 0)
				$out[] = implode('&', array_map(function($v) use ($key) { return $key . '[]=' . urlencode($v); }, $value));

		} else {
			$out[] = $key . '=' . urlencode($value);
		}
	}

	return '?' . implode('&', $out);
}

function shopello_page_qs_add($page) {

	$out = array();

	$has_k = false;
	foreach ($_GET as $key => $value) {
		if (is_array($value)) {
			$out[] = implode('&', array_map(function($v) use ($key) { return $key . '[]=' . urlencode($v); }, $value));
		} else {
			if ($key != 's_page') {
				$out[] = $key . '=' . urlencode($value);
			}
		}
	}

	$out[] = "s_page=" . urlencode($page);

	return '?' . implode('&', $out);
}



function shopello_category_qs_add($id) {

	$out = array();

	if (shopello_is_active_brand($id)) {
		return shopello_brand_del($id);
	}

	$has_k = false;
	foreach ($_GET as $key => $value) {
		if (is_array($value)) {
			$out[] = implode('&', array_map(function($v) use ($key) { return $key . '[]=' . urlencode($v); }, $value));
		} else {
			if ($key != 's_cat') {
				$out[] = $key . '=' . urlencode($value);
			}
		}
	}

	$out[] = "s_cat=" . urlencode($id);

	return '?' . implode('&', $out);
}

function shopello_category_qs_del($id) {

	$out = array();

	foreach ($_GET as $key => $value) {
		if (is_array($value)) {
			if (count($value) > 0)
				$out[] = implode('&', array_map(function($v) use ($key) { return $key . '[]=' . urlencode($v); }, $value));
		} else {
			if ($key != 's_cat')
				$out[] = $key . '=' . urlencode($value);
		}
	}

	return '?' . implode('&', $out);
}


// TEMPORARY DEV FUNCTIONS
function get_json($file){
	return json_decode(get_file($file));
}
function get_file($file) {
	if(!file_exists($file))
		return "Invalid file: $file";
	else
		return file_get_contents($file);
}
function jsonp_decode($jsonp, $assoc = false) { // PHP 5.3 adds depth as third parameter to json_decode
    if($jsonp[0] !== '[' && $jsonp[0] !== '{') { // we have JSONP
       $jsonp = substr($jsonp, strpos($jsonp, '('));
    }
    return json_decode(trim($jsonp,'();'), $assoc);
}
function nice_print($v) {
	echo "<pre>";
	print_r($v);
	echo "</pre>";
}


function post( $p ) {
	if(! isset($_POST[$p]))
		return false;
	else
		return $_POST[$p];
}

function get( $p ) {
	if(! isset($_GET[$p]))
		return false;
	else
		return $_GET[$p];
}
function request( $p ){
	if( post($p))
		return post($p);
	else if( get($p))
		return get($p);
	else return false;
}