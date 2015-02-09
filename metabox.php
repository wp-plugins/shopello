<?php

/**
 * Calls the class on the post edit screen.
 */
function swp_init_metabox() {
    new SWP_MetaBox();
}

if ( is_admin() ) {
    add_action( 'load-post.php', 'swp_init_metabox' );
    add_action( 'load-post-new.php', 'swp_init_metabox' );
}

/** 
 * The Class.
 */
class SWP_MetaBox {

	/**
	 * Hook into the appropriate actions when the class is constructed.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'swp_add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save' ) );
	}

	/**
	 * Adds the meta box container.
	 */
	public function swp_add_meta_box( $post_type ) {
        $post_types = array('post', 'page');     //limit meta box to certain post types
            if ( in_array( $post_type, $post_types )) {
		add_meta_box(
			'swp_page_metabox'
			,__( 'Shopello listning', 'swp' )
			,array( $this, 'render_meta_box_content' )
			,$post_type
			,'side'
			,'core'
		);
            }
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save( $post_id ) {
	
		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['myplugin_inner_custom_box_nonce'] ) )
			return $post_id;

		$nonce = $_POST['myplugin_inner_custom_box_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'myplugin_inner_custom_box' ) )
			return $post_id;

		// If this is an autosave, our form has not been submitted,
                //     so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return $post_id;

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) )
				return $post_id;
	
		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) )
				return $post_id;
		}

		/* OK, its safe for us to save the data now. */

		// Sanitize the user input.
		$selected_list = sanitize_text_field( $_POST['swp_selected_list'] );

		// Update the meta field.
		update_post_meta( $post_id, '_swp_selected_list', $selected_list );
	}


	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box_content( $post ) {
	
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'myplugin_inner_custom_box', 'myplugin_inner_custom_box_nonce' );

		// Display the form, using the current value.
		?>
			<p>
				<strong>Välj produktlista</strong>
			</p>
			<label class="screen-reader-text" for="parent_id">Välj produktlista</label>
		<?

		$items = SWP::Instance()->get_items();

		// Get currently selected list item, for pre-selecting in select box below.
		$selid = get_post_meta( $post->ID, "_swp_selected_list");
		// Parse ID to int. Also break out of array if wrapped in such.
		$selid = intval( is_array( $selid ) ? $selid[0] : $selid );
		
	
		// Start drawing selectbox with lists
		echo "<select name='swp_selected_list' id='swp_selected_list'>";
		echo 	"<option value='-1'>- Ingen listning -</option>";

		foreach($items as $item ){
			// BYGG IN SELECTED-FLAGGA
			$sel = ($item->get_id() == $selid ) ? " selected='selected' " : "";
			echo '<option value="'.$item->get_id().'" '.$sel.'>'.$item->name.'</option>';
		}

		echo "</select>";
	}
}