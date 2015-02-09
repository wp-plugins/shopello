<?php
/*
	The Widget
	For adding a searchfield widget to wordpress
 */

class shopello_search_widget extends WP_Widget {

	// Instantiation
	function shopello_search_widget() {

		$options = array(
            'description'   =>  'Simplest way to start working from any page',
            'name'          =>  'Sidebar Widget',
            'label'  		=>  'Hej Label'
        );

		parent::WP_Widget(false, $name = __('Shopello Sök', 'shopello_search_widget'), $options );
	}

	// Admin widget form
	function form($instance) {

		// Definition of fields and their labels in the admin
		$fields = array(
			'label'          => "Etikett",
			'placeholder'    => "Platshållartext",
			'search_label'   => "Sök-knapptext",
			'target'         => "Målsida",
			'class'          => "Extra CSS-klass"
		);

		// Loop out each of them so the user can edit
		foreach( $fields as $field=>$label ) {
			
			$val = $instance ? esc_attr($instance[ $field ]) : "";
			?>
				<p>
					<label for="<?php echo $this->get_field_id($field); ?>"><?php _e($label, 'shopello_search_widget'); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id($field); ?>" name="<?php echo $this->get_field_name($field); ?>" type="text" value="<?php echo $val; ?>" />
				</p>	
			<?php
		}
	}

	// Admin save-method
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		
		// Fields
		$instance['label']        = strip_tags($new_instance['label']);
		$instance['text']        = strip_tags($new_instance['text']);
		$instance['placeholder']  = strip_tags($new_instance['placeholder']);
		$instance['search_label'] = strip_tags($new_instance['search_label']);
		$instance['target']       = strip_tags($new_instance['target']);
		$instance['class']        = strip_tags($new_instance['class']);

		return $instance;
	}

	// Display-method
	function widget($args, $instance) {
		extract( $args );
		// these are the widget options
		$label        = apply_filters('widget_title', $instance['label']);
		$text  = $instance['text'];
		$placeholder  = $instance['placeholder'];
		$search_label = $instance['search_label'];
		$target       = $instance['target'];
		$class        = $instance['class'];

		echo $before_widget;
		
		// Display the widget
		echo '<div class="widget-text shopello_search_widget_box">';

		// Check if title is set
		if ( $label )
			echo $before_title . $label . $after_title;

		// Check if text is set
		if( $text )
			echo '<p class="shopello_search_widget_text">'.$text.'</p>';

		// Check if textarea is set
		if( $textarea )
			echo '<p class="shopello_search_widget_textarea">'.$textarea.'</p>';
		
		echo "<hr/>";

		echo do_shortcode("[shopello_filters]");
		
		echo '</div>';
		echo $after_widget;
	}
}

// register widget
add_action('widgets_init', create_function('', 'return register_widget("shopello_search_widget");'));
