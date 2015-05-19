<?php
namespace SWP;

class Pager
{
    private $totalItems;
    private $pageSize;
    private $currentPage;

    public function setTotalItems($totalItems)
    {
        $this->totalItems = $totalItems;
    }

    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;
    }

    public function setCurrentPage($currentPage)
    {
        $this->currentPage = $currentPage;
    }

    public function getPager()
    {
        $data = (object) array();

        // Set Current Page
        $data->active = $this->currentPage;

        // Set Total Pages
        $data->totalPages = ceil($this->totalItems / $this->pageSize);

        // Set number of next page, if we're on max page, don't set it
        if ($data->active < $data->totalPages) {
            $data->next = $data->active + 1;
        }

        // Set number of previous page, if we're on first page, don't set it
        if ($data->active > 1) {
            $data->previous = $data->active - 1;
        }

        // Add list of pages to count here, like: [ '<page-number>' => 'URI', '<page-number>' => 'URI' ]
        for ($i = 1; $i <= $data->totalPages; $i++) {
            $data->pages[$i] = $this->buildUri($i);
        }

        $pageOffset = ($data->active <= 4) ? 0 : $data->active - 4;

        if ($data->totalPages) {
            $data->pages = array_slice($data->pages, $pageOffset, 9, true);
        }

        return $data;
    }

    public function buildUri($page)
    {
        // Replace swp_page=<number> with swp_page=$page and return
        $uri = preg_replace('/swp_page=\d+/', 'swp_page='.$page, $_SERVER['REQUEST_URI']);

        // swp_page did not exist, add it
        if (strpos($uri, 'swp_page=') === false) {
            $join = (bool) parse_url($uri, PHP_URL_QUERY) ? '&' : '?';

            $uri = $uri.$join.'swp_page='.$page;
        }

        return $uri;
    }
}
