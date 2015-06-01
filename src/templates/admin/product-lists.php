<?php
$settings_ok = filter_var(get_option('swp_settings_status'), FILTER_VALIDATE_BOOLEAN);
if (!$settings_ok) :
?>
    <div class="wrap">
        <h2><?php _e('Settings is incorrect', 'shopello'); ?></h2>
        <p><?php _e('One or more required settings is missing or incorrect to be able to use the API.', 'shopello'); ?></p>
        <p><?php printf(__('Please visit <a href="%s">API-Settings</a> and check your settings.', 'shopello'), admin_url('admin.php?page=shopello_options_account')); ?></p>
<?php
else :
    // Get stored SWP Item list (or init new one)
    $list = SWP::Instance();
    $items = $list->get_items();
?>
        <div class="wrap">
            <h2><?php _e('Product Listings', 'shopello'); ?></h2>

            <hr/>

            <form method="post" action="options.php" id="swp_options_form">
                <input type="hidden" id="swp_api_key" name="swp_api_key" value="<?php echo get_option('swp_api_key'); ?>" />
                <input type="hidden" id="swp_api_endpoint" name="swp_api_endpoint" value="<?php echo get_option('swp_api_endpoint'); ?>" />
                <input type="hidden" id="swp_settings_status" name="swp_settings_status" value="<?php echo get_option('swp_settings_status');?>" />
                <input type="hidden" id="swp_result_title" name="swp_result_title" value="<?php echo get_option('swp_result_title'); ?>" />
                <input type="hidden" id="swp_keyword_title" name="swp_keyword_title" value="<?php echo get_option('swp_keyword_title'); ?>" />

                <div id="modal_wrap"></div>
                <div class="swp_modal loader hidden">
                    <div id="publishing-action">
                        <div class="spinner"></div>
                    </div>
                </div>

                <?php settings_fields('default'); ?>

                <input type="hidden" id="swp_SC_MAX" name="swp_SC_MAX" value="<?php echo SWP_SC_MAX;?>" />
                <input type="hidden" id="swp_list" name="swp_list" value="<?php echo get_option('swp_list'); ?>" />

                <h4><?php _e('Saved Listings <em> - To Add / Save a listing you have to use the tool <a href="#new">New Listing</a> a bit further down on this page. When done, press the button <strong>Save Listing</strong> to save to this list.</em>', 'shopello'); ?></h4>

                <table class="form-table lists" id="shortcode_table">
                    <?php
                    // Get stashed shortcodes
                    foreach ($items as $id => $item) :
                    ?>
                        <tr>
                            <td width="15%"><input type="text" name="swp_shortcodes_names[]" class="swp_item_name" value="<?php echo htmlspecialchars($item->name);?>" /></td>
                            <td width="40%"><span class="swp_item_desc"><?php echo $item->get_description(DESC_DELIMITER);?></span></td>
                            <td width="10%">
                                <input type="hidden" name="swp_item_id" class="swp_item_id" value="<?php echo $id ?>"/>
                                <input type="button" class="button remove" value="Ta bort" />
                            </td>
                        </tr>
                    <?php
                    endforeach;
                    ?>
                </table>
            </form>
            <div id="new"></div>
            <h2><?php _e('New Listing', 'shopello'); ?></h2>
            <hr/>

            <div class="leftcol">
                <?php
                $is_admin_ajax = is_admin();
                include(SHOPELLO_PLUGIN_TEMPLATE_DIR.'result/filters.php');
                ?>
            </div>
            <div class="maincol">
                <h4><?php _e('Preview of your listing', 'shopello'); ?></h4>
                <div id="preview_box"></div>
            </div>
        </div>
<?php
endif;
// settings OK check.
?>
