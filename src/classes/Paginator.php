<?php

class Paginator
{
    var $items_per_page;
    var $items_total;
    var $current_page;
    var $num_pages;
    var $mid_range;
    var $low;
    var $high;
    var $return;
    var $default_ipp = 16;

    public function __construct()
    {
        $this->current_page = -1;
        $this->mid_range = 7;
        $this->items_per_page = 16;
        $this->default_ipp = 16;
    }

    public function paginate()
    {
        if (!is_numeric($this->items_per_page) or $this->items_per_page <= 0) {
            $this->items_per_page = $this->default_ipp;
        }

        $this->num_pages = ceil($this->items_total/$this->items_per_page);

        $g_p = isset($_GET['page']) ? intval($_GET['page']) : false;
        if ($this->current_page == -1) {
            $this->current_page = 1;
        }
        if ($g_p) {
            $this->current_page = $g_p;
        }


        if ($this->current_page < 1 or !is_numeric($this->current_page)) {
            $this->current_page = 1;
        }
        if ($this->current_page > $this->num_pages) {
            $this->current_page = $this->num_pages;
        }

        $prev_page = $this->current_page-1;
        $next_page = $this->current_page+1;

        if ($this->num_pages > 10) {
            $this->return = ($this->current_page != 1 and $this->items_total >= 10) ? "<li><a href='javascript:void();' data-pagenum='$prev_page'>«</a></li> ":"<li class=\"disabled\"><a href=\"javascript:void();\">«</a></li>";

            $this->start_range = $this->current_page - floor($this->mid_range/2);
            $this->end_range = $this->current_page + floor($this->mid_range/2);

            if ($this->start_range <= 0) {
                $this->end_range += abs($this->start_range)+1;
                $this->start_range = 1;
            }
            if ($this->end_range > $this->num_pages) {
                $this->start_range -= $this->end_range-$this->num_pages;
                $this->end_range = $this->num_pages;
            }
            $this->range = range($this->start_range, $this->end_range);

            for ($i=1; $i<=$this->num_pages; $i++) {
                if ($this->range[0] > 2 and $i == $this->range[0]) {
                    $this->return .= "<li class='disabled'><a href='javascript:void();'>...</a></li>";
                }
                // loop through all pages. if first, last, or in range, display
                if ($i==1 or $i==$this->num_pages or in_array($i, $this->range)) {
                    $this->return .= ($i == $this->current_page and $_GET['s_page'] != 'All') ? "<li class=\"active\"><a title=\"Go to page $i of $this->num_pages\" data-pagenum=\"$i\" href=\"javascript:void();\">$i</a></li> ":"<li><a title=\"Go to page $i of $this->num_pages\" data-pagenum=\"$i\" href=\"#\">$i</a></li> ";
                }
                if ($this->range[$this->mid_range-1] < $this->num_pages-1 and $i == $this->range[$this->mid_range-1]) {
                    $this->return .= "<li class=\"disabled\"><a href=\"javascript:void();\">...</a></li>";
                }
            }

            $this->return .= (($this->current_page != $this->num_pages and $this->items_total >= 10) and ($_GET['s_page'] != 'All')) ? "<li><a data-pagenum=\"$i\" href=\"javascript:void();\">»</a></li>\n":"<li class=\"disabled\"><a href=\"#\">»</a></li>\n";
        } else {
            for ($i=1; $i<=$this->num_pages; $i++) {
                $this->return .= ($i == $this->current_page) ? "<li class=\"active\"><a data-pagenum=\"$i\" href=\"javascript:void();\">$i</a></li> ":"<li><a data-pagenum=\"$i\" href=\"javascript:void();\">$i</a></li> ";
            }
        }

        $this->low = ($this->current_page-1) * $this->items_per_page;
        $this->high = ($this->current_page * $this->items_per_page)-1;
    }

    function display_pages()
    {
        return $this->return;
    }
}
