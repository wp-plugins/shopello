<?php

use \SWP\ApiClient as ShopelloAPI;

class category_lib
{
    private $t_categories;
    private $t_relations;
    private $categories;
    private $last_sync_opt;

    public function __construct()
    {
        global $wpdb;

        // Setup vars
        $this->t_categories  = $wpdb->prefix . SHOPELLO_PLUGIN_TABLE_CATEGORIES;
        $this->t_relations   = $wpdb->prefix . SHOPELLO_PLUGIN_TABLE_RELATIONS;
        $this->last_sync_opt = 'swp_categories_last_sync';

        // Initial fetch to have categories ready
        $this->categories = $wpdb->get_results(
            "SELECT c.*, cp.parent_id FROM $this->t_categories AS c LEFT JOIN $t_relations AS cp ON cp.category_id = c.category_id ORDER BY c.name ASC",
            OBJECT
        );

        // Store sync-datetime in options
        add_option($this->last_sync_opt, '');
    }

    public function get_category_html_tree($parent_id = null)
    {
        return $this->create_category_tree($parent_id);
    }

    private function create_category_tree($parent_id)
    {
        $category = reset($this->get_category_path($parent_id));
        return '<ul>' . $this->build_category_tree($category, $parent_id) . '</ul>';
    }

    public function get_category($category_id)
    {
        foreach ($this->categories as $category) {
            if ($category_id == $category->category_id) {
                return $category;
            }
        }

        return array();
    }

    public function synchronize_categories_from_server()
    {
        global $wpdb;

        $shopelloApi = ShopelloAPI::getInstance();
        $shopelloApi->cache(false);

        // Get categories from API
        $categories = $shopelloApi->getCategories();

        // If alles guut, insert those categories into db
        if ($categories->status === true) {
            global $wpdb;

            // Wipe old categories
            $wpdb->query("TRUNCATE TABLE $this->t_categories");

            // Insert new ones
            foreach ($categories->data as $cat) {
                $success = $wpdb->insert(
                    $this->t_categories,
                    array(
                        'category_id' => $cat->category_id,
                        'name' => $cat->name
                    ),
                    array(
                        '%d',
                        '%s'
                    )
                );

                if (!$success) {
                    $wpdb->show_errors();
                }
            }
        } else {
            return false;
        }


        // Get relations from API
        $relations = $shopelloApi->getCategoryParents();

        // If alles guut, insert new relations
        if ($relations->status === true) {
            global $wpdb;

            // Wipe old category relations
            $wpdb->query("TRUNCATE TABLE $this->t_relations");

            // Insert new ones
            foreach ($relations->data as $rel) {
                $success = $wpdb->insert(
                    $this->t_relations,
                    array(
                        'category_id' => $rel->category_id,
                        'parent_id' => $rel->parent_id
                    ),
                    array(
                        '%d',
                        '%d'
                    )
                );
                //if(! $success ) $wpdb->show_errors();
            }
        } else {
            return false;
        }

        return true;
    }

    private function build_category_tree($category, $parent_id)
    {
        $category_tree_html = '<li><a' . ($category->category_id == $parent_id ? ' class="selected" ' : '') . ' href="' . site_url(url_title($category->name, '-', true)) . '">' . $category->name . '</a>';
        $sub_categories = $this->get_sub_categories($category->category_id);

        if (!empty($sub_categories) && $this->in_path($category, $parent_id)) {
            $category_tree_html .= '<ul>';

            foreach ($sub_categories as $sub_category) {
                $category_tree_html .= $this->build_category_tree($sub_category, $parent_id);
            }

            $category_tree_html .= '</ul>';
        }

        $category_tree_html .= '</li>';

        return $category_tree_html;

    }

    private function get_sub_categories($parent_id)
    {

        $sub_categories = array();

        foreach ($this->categories as $category) {
            if ($parent_id == null && ($category->parent_id == 0 || $category->parent_id == null)) {
                $sub_categories[] = $category;
            } else {
                if ($category->parent_id == $parent_id) {
                    $sub_categories[] = $category;
                }
            }
        }

        return $sub_categories;

    }

    private function in_path($category, $parent_id)
    {
        $category_path = $this->get_category_path($parent_id);

        foreach ($category_path as $category_path_item) {
            if ($category_path_item->category_id == $category->category_id) {
                return true;
            }
        }

        return false;

    }

    public function get_categories()
    {
        return $this->categories;
    }

    public function get_category_path($category_id)
    {

        $category_path = array();

        $category = $this->get_category($category_id);
        $category_path[] = $category;

        while ($category->parent_id != null || $category->parent_id != 0) {
            $category = $this->get_category($category->parent_id);
            $category_path[] = $category;
        }

        return array_reverse($category_path);
    }
}
