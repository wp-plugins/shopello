<?php

/**
 * SWP Item represents a Shopello Wordpress Plugin listing-instance
 * it's the definition of a listing and it's attributes
 */
class SWP_Item {

	private $id;
	public $name;
	public $pagesize;
	public $keyword;
	public $categories;
	public $price_max;
	public $sort;
	public $sort_order;
	public $color;
	public $filters;


	// Contruct an instance of this object
	function __construct( $n = "", $p = 16, $k = "", $c = array()) {
		$this->name       = $n ? $n : "";
		$this->pagesize   = $p ? $p : 16;
		$this->keyword    = $k ? $k : "";
		$this->categories = $c ? $c : array();
	}
	function get_shortcode_result() {
		return '[swp_result]';
	}
	function get_shortcode_filter() {
		return '[swp_filter]';
	}
	function get_id() {
		return $this->id;
	}
	function set_id( $i ) {
		$this->id = $i;
	}
	function get_description($del = ", ") {
		$d = array();

		if(strlen($this->keyword != "") > 0)
			$d[] = "Query: ".$this->keyword;
		else
			$d[] = "No querystring";

		if(count($this->categories) > 0)
			$d[] = "Categories: [". implode(",", $this->categories)."]";
		else
			$d[] = "No categories selected";

		if($this->pagesize)
			$d[] = "Page size: ".$this->pagesize;

		return count($d) > 0 ? implode($del , $d) : "";
	}
}