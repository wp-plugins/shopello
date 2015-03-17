<?php

/**
 * The Widget
 * For adding a searchfield widget to wordpress
 */
class shopello_search_widget extends WP_Widget
{
    // Instantiation
    public function __construct()
    {
        $options = array(
            'description' => __('Simplest way to start working from any page', 'shopello'),
            'name'        => __('Shopello Productfilter', 'shopello'),
            'label'       => __('Label', 'shopello')
        );

        parent::WP_Widget(false, $name = __('Shopello Search', 'shopello'), $options);
    }

    // Admin widget form
    public function form($instance)
    {
        // Definition of fields and their labels in the admin
        $fields = array(
            'label' => __('Label', 'shopello'),
            'text'  => __('Text', 'shopello'),
            'class' => __('CSS klass', 'shopello')
        );

        // Loop out each of them so the user can edit
        foreach ($fields as $field => $label) {
            $val = $instance ? esc_attr($instance[$field]) : '';

            echo '<p>';
            echo '<label for="'.$this->get_field_id($field).'">'._e($label, 'shopello_search_widget').'</label>';
            echo '<input class="widefat" id="'.$this->get_field_id($field).'" name="'.$this->get_field_name($field).'" type="text" value="'.$val.'" />';
            echo '</p>';
        }
    }

    // Admin save-method
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;

        // Fields
        $instance['label'] = strip_tags($new_instance['label']);
        $instance['text']  = strip_tags($new_instance['text'], '<br><a><div><hr><p><span><em><b><strong><i><u>');
        $instance['class'] = strip_tags($new_instance['class']);

        return $instance;
    }

    // Display-method
    public function widget($args, $instance)
    {
        extract($args);
        // these are the widget options
        $label = apply_filters('widget_title', $instance['label']);
        $text  = $instance['text'];
        $class = strlen($instance['class']) > 0 ? $instance['class'] : '';


        // Display the widget
        echo $before_widget;
        echo '<div class="widget-text shopello_search_widget_box '.$class.'">';

        // Check if title is set
        if ($label) {
            echo $before_title . $label . $after_title;
        }

        // Check if text is set
        if ($text) {
            echo '<p class="shopello_search_widget_text">'.$text.'</p>';
        }

        echo do_shortcode("[shopello_filters]");

        echo '</div>';
        echo $after_widget;
    }
}
