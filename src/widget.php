<?php
// register widget
add_action('widgets_init', (function () {
    return register_widget('shopello_search_widget');
}));
