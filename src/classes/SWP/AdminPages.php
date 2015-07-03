<?php
namespace SWP;

use \SWP\View;
use \SWP\SystemTests;

class AdminPages
{
    /** @var View */
    private $view;

    /** @var SystemTests */
    private $systemTests;

    public function __construct(View $view, SystemTests $systemTests)
    {
        $this->view = $view;
        $this->systemTests = $systemTests;
    }

    public function registerActions()
    {
        /**
         * Feel the JavaScript uglyness when reading this code
         *
         * Reason: PHP 5.3 Scopes doesn't allow using $this inside
         * of anonymous functions.
         */
        $self = $this;

        add_action('admin_menu', (function () use ($self) {
            add_menu_page(
                __('Shopello', 'shopello'),           // Page Title
                __('Shopello', 'shopello'),           // Menu Title
                'manage_options',                     // Permissions
                'shopello_options',                   // Menu SLUG
                array($self, 'adminListings'),        // Callback
                null,                                 // Icon
                76                                    // Sort
            );

            add_submenu_page(
                'shopello_options',                   // Parent SLUG
                __('Shopello', 'shopello'),           // Page Title
                __('Product Listings', 'shopello'),   // Menu Title
                'manage_options',                     // Permissions
                'shopello_options',                   // Menu SLUG
                array($self, 'adminListings')         // Callback
            );

            add_submenu_page(
                'shopello_options',                   // Parent SLUG
                __('API-Settings', 'shopello'),       // Page Title
                __('API-Settings', 'shopello'),       // Menu Title
                'manage_options',                     // Permissions
                'shopello_options_account',           // Menu SLUG
                array($self, 'adminAccount')          // Callback
            );

            add_submenu_page(
                'shopello_options',                   // Parent SLUG
                __('System Test', 'shopello'),        // Page Title
                __('System Test', 'shopello'),        // Menu Title
                'manage_options',                     // Permissions
                'shopello_system_test',               // Menu SLUG
                array($self, 'adminSystemTests')      // Callback
            );
        }));


        /**
         * Load custom scripts in WP Admin
         */
        add_action('admin_enqueue_scripts', (function () {
            wp_enqueue_style('shopello_css', SHOPELLO_PLUGIN_URL.'assets/css/shopello-all.css', false, '1.0.0');
            wp_enqueue_script('jquery_form', SHOPELLO_PLUGIN_URL.'assets/js/jquery.form.min.js', false, '1.0', true);
            wp_enqueue_script(
                'generator_js',
                SHOPELLO_PLUGIN_URL.'assets/js/swp_api_generator.js',
                false,
                '1.0.0',
                true
            );
            wp_enqueue_script('admin_js', SHOPELLO_PLUGIN_URL.'assets/js/admin.js', false, '1.0.0', true);

            wp_localize_script('generator_js', 'scriptL10n', array(
                'ajax_url' => admin_url('admin-ajax.php')
            ));

            wp_localize_script('admin_js', 'adminL10n', array(
                'working_button' => __('Working... This can take several minutes...', 'shopello'),
                'max_listings' => __(
                    'You have reached max number of listings (%d st). Remove one to be able to save this new one.',
                    'shopello'
                ),
                'save_error' => __('Error while saving. Reload the page and try again.', 'shopello'),
                'save_prompt' => __('Choose a name for your listing:', 'shopello')
            ));
        }));


        /**
         * Set up default settings when loading admin
         */
        add_action('admin_init', (function () {
            // Define options & defaults
            add_option('swp_api_key', '');
            add_option('swp_api_endpoint', 'https://se.shopelloapi.com/1/');
            add_option('swp_settings_status', '');
            add_option('shopello_list', '');
            add_option('swp_result_title', '');
            add_option('swp_keyword_title', '');

            // Register settings and eventual sanitizing callbacks (when saving)
            register_setting('default', 'swp_api_key');
            register_setting('default', 'swp_api_endpoint');
            register_setting('default', 'swp_settings_status');
            register_setting('default', 'shopello_list', 'sanitize_swp_items');
            register_setting('default', 'swp_result_title');
            register_setting('default', 'swp_keyword_title');

            /**
             * Include scripts for ajax-saving on the plugin's option page
             */
            if (is_admin() && isset($_GET['page']) && $_GET['page'] == 'shopello_api_options') {
                wp_enqueue_script('jquery');
                wp_enqueue_script('jquery-form');
            }
        }));
    }

    /****/

    /**
     * Product Listing page
     */
    public function adminListings()
    {
        global $is_admin_ajax;

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'shopello'));
        }

        $is_admin_ajax = true;
        $api_categories = swp_get_category_list();

        include(SHOPELLO_PLUGIN_TEMPLATE_DIR.'admin/product-lists.php');
    }

    /**
     * Admin Account page
     */
    public function adminAccount()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'shopello'));
        }

        /**
         * Output Buffering hack to run settings_fields() and get data to use in the form
         */
        ob_start();
        settings_fields('default');
        $hiddenWpFields = ob_get_contents();
        ob_clean();

        submit_button('Save', 'primary');
        $submitButton = ob_get_contents();
        ob_end_clean();
        /**
         * End hack
         */

        $data = array(
            'hiddenWpFields' => $hiddenWpFields,
            'swpApiKey' => get_option('swp_api_key'),
            'swpApiEndpoint' => get_option('swp_api_endpoint'),
            'swpSettingsStatus' => get_option('swp_settings_status'),
            'swpResultTitle' => get_option('swp_result_title'),
            'swpKeywordTitle' => get_option('swp_keyword_title'),
            'submitButton' => $submitButton
        );

        echo $this->view->render('admin/account', $data);
    }

    /**
     * System test page
     */
    public function adminSystemTests()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'shopello'));
        }

        $data = array(
            'curlCheck' => $this->systemTests->isCurlInstalled(),
            'pingCheck' => $this->systemTests->pingShopello()
        );

        echo $this->view->render('admin/tests', $data);
    }
}
