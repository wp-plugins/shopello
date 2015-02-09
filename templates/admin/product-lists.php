<?php

$settings_ok = filter_var(get_option('swp_settings_status'), FILTER_VALIDATE_BOOLEAN);

if(! $settings_ok ) :
?>

 <div class="wrap">
    <h2>Inställningarna verkar vara felaktiga!</h2>
	<p>En del obligatoriska uppgifter saknas eller är felaktiga för att kunna använda API:et. <br/>Vänligen gå till <a href="<?php echo admin_url('admin.php?page=shopello_options_account');?>">Inställningar</a> och kontrollera uppgifterna där.</p>

<?php
else :  


// Get stored SWP Item list (or init new one)
$list = SWP::Instance();
$items = $list->get_items();

?>

    <div class="wrap">
        <h2>Produktlistor</h2>

        <?php swp_print_success(); ?>
        <?php swp_print_notices(); ?>
        <?php swp_print_warnings(); ?>

        <hr/>

        <form method="post" action="options.php" id="swp_options_form">
			<input type="hidden" id="swp_api_key" name="swp_api_key" value="<?php echo get_option('swp_api_key'); ?>" />
			<input type="hidden" id="swp_api_endpoint" name="swp_api_endpoint" value="<?php echo get_option('swp_api_endpoint'); ?>" />
			<input type="hidden" id="swp_settings_status" name="swp_settings_status" value="<?php echo get_option('swp_settings_status');?>" />
            <input type="hidden" id="swp_result_title" name="swp_result_title" value="<?php echo get_option('swp_result_title'); ?>" placeholder="Hittade %antal produkter"/>
            <input type="hidden" id="swp_keyword_title" name="swp_keyword_title" value="<?php echo get_option('swp_keyword_title'); ?>" placeholder="för sökordet %ord." />

        	<div id="modal_wrap"></div>
    		<div class="swp_modal loader hidden">
        		<div id="publishing-action">
        			<div class="spinner"></div>
        		</div>
    		</div>

        	<?php settings_fields( 'default' ); ?>

            <input type="hidden" id="swp_SC_MAX" name="swp_SC_MAX" value="<?php echo swp_SC_MAX;?>" />
           	<input type="hidden" id="swp_list" name="swp_list" value="<?php echo get_option('swp_list'); ?>" />

            <h4>Sparade urval <em> - För att lägga till / spara ett urval så använder du verktyget <a href="#new">* Ny listning</a> längre ned på sidan. När du är klar, tryck på <strong>Spara urval</strong> så sparas den i denna lista.</em></h4>
            
       		<table class="form-table lists" id="shortcode_table">
	       		<?php
		            // Get stashed shortcodes
		            foreach($items as $item) : 
	               ?>
                    <tr>
                        <td width="15%"><input type="text" name="swp_shortcodes_names[]" class="swp_item_name" value="<?php echo htmlspecialchars($item->name);?>" /></td>
                        <td width="40%"><span class="swp_item_desc"><?php echo $item->get_description(DESC_DELIMITER);?></span></td>
                        <td width="10%">
                        	<input type="hidden" name="swp_item_id" class="swp_item_id" value="<?php echo $item->get_id(); ?>"/>
                        	<input type="button" class="button remove" value="Ta bort" /></td>
                    </tr>
	            <?php 
	                endforeach;
	            ?>
        	</table>

		</form>

        <div id="new"></div>
        <h2>* Nytt urval</h2>
        <hr/>

        <div class="leftcol">
			<?php include(SHOPELLO_PLUGIN_DIR."templates/result/filters.php"); ?>
        </div>
        <div class="maincol">

            <h4>Förhandsgranskning av ditt urval</h4>
            <div id="preview_box"></div>
        </div>
    </div>
<?php endif; // settings OK check. ?>