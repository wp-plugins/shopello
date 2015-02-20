<div class="wrap">
    <h2>Kontoinställningar</h2>

    <?php swp_print_success(); ?>
    <?php swp_print_notices(); ?>
    <?php swp_print_warnings(); ?>

    <hr/>

    <form method="post" action="options.php" id="swp_options_form">

    	<?php settings_fields( 'default' ); ?>

        <table class="form-table compact">
	    <tr valign="top">
		<th scope="row">
		    <label for="swp_api_key">API-nyckel</label>
                </th>
		<td>
		    <input type="text" id="swp_api_key" name="swp_api_key" value="<?php echo get_option('swp_api_key'); ?>" />
                </td>
	    </tr>
	    <tr valign="top">
		<th scope="row">
		    <label for="swp_api_endpoint">API-domän * </label>
                </th>
		<td>
		    <input type="text" id="swp_api_endpoint" name="swp_api_endpoint" value="<?php echo get_option('swp_api_endpoint'); ?>" />
                </td>
	    </tr>
	    <tr valign="top">
		<th scope="row">
		    <label for="swp_api_endpoint">Testa inställningar</label>
                </th>
		<td>
		    <input type="button" id="test_settings" class="button" value="Testa inställningar"/>
		    <input type="hidden" id="swp_settings_status" name="swp_settings_status" value="<?php echo get_option('swp_settings_status');?>" />
		    <div id="test-spinner">
		    	<div class="spinner"></div>
		    </div>
		    <div class="settings_status <?php if(get_option('swp_settings_status') == 'false') echo 'tested';?>">
			<span class="good-label">Inställningarna är korrekta.</span>
			<span class="bad-label">Vänligen kontrollera dina inställningar.</span>
		    </div>
	    </tr>
	</table>

	<?php if( get_option('swp_settings_status') === 'true' ) : ?>
	<h2>Generella inställningar</h2>

	<table class="form-table compact">
	    <tr valign="top">
		<th scope="row">
		    <label for="swp_result_title">Antal träffar-text</label>
		</th>
		<td>
		    <input type="text" id="swp_result_title" name="swp_result_title" value="<?php echo get_option('swp_result_title'); ?>" placeholder="Hittade %antal produkter"/>
		    <p>Hittade 247 produkter</p>
		    <p>Skrivs: "Hittade %antal produkter för sökordet %ord".</p>
		</td>
	    </tr>
	    <tr valign="top">
		<th scope="row">
		    <label for="swp_keyword_title">Sökords-text</label>
		</th>
		<td>
		    <input type="text" id="swp_keyword_title" name="swp_keyword_title" value="<?php echo get_option('swp_keyword_title'); ?>" placeholder="för sökordet %ord." />
		    <p>Kompletterar föregående "Hittade 247 produkter" med ett sökord.</p>
		    <p>T.ex.: "för sökordet %ord". </p>
		    <p>Resultat: "Hittade 247 produkter för sökordet Skor</p>
		</td>
	    </tr>
	    <tr valign="top">
		<th scope="row">
		    <label for="swp_keyword_title">Kategorier</label>
		</th>
		<td>
		    <input type="button" id="swp_cat_sync" class="button" name="swp_cat_sync" value="Synkronisera kategorier" />
		    <p class="sync_status" id="swp_cat_sync_status"></p>
		    <p>Kategorierna uppdateras dagligen. Använd knappen för att manuellt uppdatera alla kategorier.</p>
		    <p>Första gången du använder pluginet behöver du troligen forcera synkronisering för att ladda ned alla kategorier.</p>
		</td>
	    </tr>
	</table>
	<?php endif; // settings status == ok ?>

	<?php submit_button('Save','primary'); ?>
    </form>
</div>
