<?php

/**
 * Calls the class on the post edit screen.
 */
function swp_init_metabox()
{
    new SWP_MetaBox();
}

if (is_admin()) {
    add_action('load-post.php', 'swp_init_metabox');
    add_action('load-post-new.php', 'swp_init_metabox');
}
