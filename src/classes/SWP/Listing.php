<?php
namespace SWP;

class Listing
{
    private $id;
    public $name;
    public $pagesize;
    public $keyword;
    public $categories;
    public $pricemax;
    public $sort;
    public $sort_order;
    public $color;
    public $filters;

    public function __construct($name = '', $pagesize = 16, $keyword = '', $categories = array())
    {
        $this->name = $name;
        $this->pagesize = $pagesize;
        $this->keyword = $keyword;
        $this->categories = $categories;
    }

    public function get_shortcode_result()
    {
        return '[swp_result]';
    }

    public function get_shortcode_filter()
    {
        return '[swp_filter]';
    }

    public function get_id()
    {
        return $this->id;
    }

    public function set_id($i)
    {
        $this->id = $i;
    }

    public function get_description($del = ', ')
    {
        $d = array();

        if (strlen($this->keyword != "") > 0) {
            $d[] = sprintf(__('Query: %s', 'shopello'), $this->keyword);
        } else {
            $d[] = __('No querystring', 'shopello');
        }

        if (count($this->categories) > 0) {
            $d[] = sprintf(__('Categories: [%s]', 'shopello'), implode(',', $this->categories));
        } else {
            $d[] = __('No categories selected', 'shopello');
        }

        if ($this->pagesize) {
            $d[] = sprintf(__('Page size: %s', 'shopello'), $this->pagesize);
        }

        return count($d) > 0 ? implode($del, $d) : '';
    }

    public function importSettings($settings)
    {
        foreach ($settings as $key => $val) {
            $this->$key = $val;
        }
    }

    public function exportSettings()
    {
        $settings = new \stdClass;

        foreach ($this as $key => $val)
        {
            $settings->$key = $val;
        }

        return $settings;
    }
}
