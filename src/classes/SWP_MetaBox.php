<?php
use \SWP\View;
use \Shopello\ListingManager;

class SWP_MetaBox
{
    /** @var View */
    private $view;

    /** @var ListingManager */
    private $listingManager;

    /**
     * Hook into the appropriate actions when the class is constructed.
     */
    public function __construct()
    {
        $this->view = View::getInstance();
        $this->listingManager = ListingManager::getInstance();

        add_action('add_meta_boxes', array($this, 'swp_add_meta_box'));
        add_action('save_post', array($this, 'save'));
    }

    /**
     * Adds the meta box container.
     */
    public function swp_add_meta_box($post_type)
    {
        $post_types = array('post', 'page');     //limit meta box to certain post types

        if (in_array($post_type, $post_types)) {
            add_meta_box(
                'swp_page_metabox',
                __('Shopello listning', 'shopello'),
                array( $this, 'render_meta_box_content' ),
                $post_type,
                'side',
                'core'
            );
        }
    }

    /**
     * Save the meta when the post is saved.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save($post_id)
    {
        /*
         * We need to verify this came from the our screen and with proper authorization,
         * because save_post can be triggered at other times.
         */

        // Check if our nonce is set.
        if (!isset( $_POST['myplugin_inner_custom_box_nonce'])) {
            return $post_id;
        }

        $nonce = $_POST['myplugin_inner_custom_box_nonce'];

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($nonce, 'myplugin_inner_custom_box')) {
            return $post_id;
        }

        // If this is an autosave, our form has not been submitted,
        //     so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        // Check the user's permissions.
        if ('page' == $_POST['post_type']) {
            if (!current_user_can('edit_page', $post_id)) {
                return $post_id;
            }
        } else {
            if (!current_user_can('edit_post', $post_id)) {
                return $post_id;
            }
        }

        /* OK, its safe for us to save the data now. */

        // Sanitize the user input.
        $selected_list = sanitize_text_field($_POST['swp_selected_list']);

        // Update the meta field.
        update_post_meta($post_id, '_swp_selected_list', $selected_list);
    }


    /**
     * Render Meta Box content.
     *
     * @param WP_Post $post The post object.
     */
    public function render_meta_box_content($post)
    {
        // Add an nonce field so we can check for it later.
        wp_nonce_field('myplugin_inner_custom_box', 'myplugin_inner_custom_box_nonce');

        $listings = $this->listingManager->getAllListings();

        // Get currently selected list item, for pre-selecting in select box below.
        $selectedId = get_post_meta($post->ID, '_swp_selected_list');
        $selectedId = intval(is_array($selectedId) ? $selectedId[0] : $selectedId);

        echo $this->view->render(
            'admin/metabox',
            array(
                'listings' => $listings,
                'selectedId' => $selectedId
            )
        );
    }
}
