<div class="wrap">
    <h2><?php _e('API-Settings', 'shopello'); ?></h2>

    <?php swp_print_success(); ?>
    <?php swp_print_notices(); ?>
    <?php swp_print_warnings(); ?>

    <hr/>

    <form method="post" action="options.php" id="swp_options_form">

    	<?php settings_fields('default'); ?>

        <table class="form-table compact">
	    <tr valign="top">
		<th scope="row">
		    <label for="swp_api_key"><?php _e('API-Key', 'shopello'); ?></label>
                </th>
		<td>
		    <input type="text" id="swp_api_key" name="swp_api_key" value="<?php echo get_option('swp_api_key'); ?>" />
                </td>
	    </tr>
	    <tr valign="top">
		<th scope="row">
		    <label for="swp_api_endpoint"><?php _e('API-Endpoint', 'shopello'); ?></label>
                </th>
		<td>
		    <input type="text" id="swp_api_endpoint" name="swp_api_endpoint" value="<?php echo get_option('swp_api_endpoint'); ?>" />
                </td>
	    </tr>
	    <tr valign="top">
		<th scope="row">
		    <label for="swp_api_endpoint"><?php _e('Test API-Settings', 'shopello') ?></label>
                </th>
		<td>
		    <input type="button" id="test_settings" class="button" value="<?php _e('Test now', 'shopello') ?>"/>
		    <input type="hidden" id="swp_settings_status" name="swp_settings_status" value="<?php echo get_option('swp_settings_status');?>" />
		    <div id="test-spinner">
		    	<div class="spinner"></div>
		    </div>
		    <div class="settings_status <?php if (get_option('swp_settings_status') == 'false') { echo 'tested'; } ?>">
			<span class="good-label"><?php _e('Settings are correct.', 'shopello') ?></span>
			<span class="bad-label"><?php _e('Please check your settings and check if ping works on the System Test page.', 'shopello') ?></span>
		    </div>
	    </tr>
	</table>

	<?php if (get_option('swp_settings_status') === 'true') : ?>
	<h2><?php _e('General Settings', 'shopello'); ?></h2>

	<table class="form-table compact">
	    <tr valign="top">
		<th scope="row">
		    <label for="swp_result_title"><?php _e('Total amount of hits-text', 'shopello'); ?></label>
		</th>
		<td>
		    <input type="text" id="swp_result_title" name="swp_result_title" value="<?php echo get_option('swp_result_title'); ?>" placeholder="<?php _e('Found %amount% products', 'shopello'); ?>"/>
		    <p><?php _e('Found 247 products') ?></p>
		    <p><?php _e('Written: "Found %amount% of products for phrase %phrase%".', 'shopello') ?></p>
		</td>
	    </tr>
	    <tr valign="top">
		<th scope="row">
		    <label for="swp_keyword_title"><?php _e('Keyword-text', 'shopello'); ?></label>
		</th>
		<td>
		    <input type="text" id="swp_keyword_title" name="swp_keyword_title" value="<?php echo get_option('swp_keyword_title'); ?>" placeholder="<?php _e('for phrase %phrase%.', 'shopello'); ?>" />
                    <p><?php _e('Completes previous "Found 247 products" with a keyword', 'shopello'); ?></p>
                    <p><?php _e('For example: "for phrase %phrase%".', 'shopello'); ?></p>
                    <p><?php _e('Result: "Found 247 products for phrase Shoes"', 'shopello'); ?></p>
		</td>
	    </tr>
	    <tr valign="top">
		<th scope="row">
		    <label for="swp_keyword_title"><?php _e('Categories', 'shopello'); ?></label>
		</th>
		<td>
		    <input type="button" id="swp_cat_sync" class="button" name="swp_cat_sync" value="<?php _e('Synchronize categories', 'shopello'); ?>" />
		    <p class="sync_status" id="swp_cat_sync_status"></p>
                    <p><?php _e('Categories is automaticly updated daily. Use this button to manually update everything now.', 'shopello'); ?></p>
                    <p><?php _e('If it is the first time you are using the plugin, you probably need to click this.', 'shopello'); ?></p>
		</td>
	    </tr>
	</table>
	<?php endif; // settings status == ok ?>

	<?php submit_button('Save', 'primary'); ?>
    </form>
</div>
