<div class="swp-filters filters" id="shopello_filter_wrap">	   
    <?php //var_dump($params);?>         
    <div class="item filter-keyword">
        <h4>Sökord</h4>
        <input type="text" id="input-keyword" placeholder="Sökterm" value="<?= $keyword?$keyword:'';?>"/>
        <p class="filter-hint">Använd egna nyckelord för att smalna av produkturvalet.</p>
    </div>

    <div class="item filter-pagesize">
        <h4>Produkter per sida</h4>
        <select name="preview_per_page" id="input-pagesize">
            <option value="16" <?= $input-pagesizesize==16?'selected="selected"':'';?>>16</option>
            <option value="32" <?= $pagesize==32?'selected="selected"':'';?>>32</option>
            <option value="64" <?= $pagesize==64?'selected="selected"':'';?>>64</option>
        </select>
    </div>

    <div class="item filter-price-max">
        <h4>Pristak <span id="label-price-max" style="font-weight:normal;"><?= $price_max ? $price_max ." kr": ''; ?></span></h4>
        <input id="input-price-max" type ="range" min ="10" max="<?=$max_price;?>" step ="10" value ="<?= $price_max ? $price_max : 0; ?>"/>
        <input type="button" class="button" id="reset-price-max" value="Ta bort pristak" />
        <p class="filter-hint">Dra i handtaget för att sätta ett pristak. Tryck på knappen för att ta bort pristaket.</p>
    </div>


    <div class="item filter-sort">	
        <h4>Sortering</h4>
        <select name="preview_sort" id="input-sort-field">
        <?php
            $sortables = array(
                'price' => 'Pris',
                'name' => 'Namn',
                'clicks' => 'Klick',
                'popular' => 'Popularitet'
            );
            foreach( $sortables as $value=>$label ) {

                $sel="";
                if( isset($params['order_by']) && $params['order_by'] == $value)
                    $sel = ' selected="selected"';

                echo "<option value='$value' $sel >$label</option>";
            }
        ?>
        </select>
        <select name="preview_sort_order" id="input-sort-order">
            <option value="DESC" <?= isset($params['order']) && $params['order'] == 'DESC' ? 'selected="selected"' : '';?> >Fallande</option>
            <option value="ASC" <?= isset($params['order']) && $params['order'] == 'ASC' ? 'selected="selected"' : '';?>>Stigande</option>
        </select>
    </div>
    <div class="item filter-color">
    	<h4>Färgfilter</h4>
    	<div class="colors">
    		<?php
    			$colors = array(
    				'vit' => 'vit',
    				'gra' => 'grå',
    				'svart' => 'svart',
    				'bla' => 'blå',
    				'gron' => 'grön',
    				'gul' => 'gul',
    				'orange' => 'orange',
    				'rod' => 'röd',
    				'rosa' => 'rosa',
    				'lila' => 'lila'
    			);

    			foreach($colors as $slug=>$name) {
    				$checked = ($color == $name);

    				if($checked)
    					$state = ' data-checked="true" checked="checked" ';
    				else
    					$state = ' data-checked="false" checked="false" ';

    				echo '<input type="radio" id="color_filter_'.$slug.'" name="color" class="color '.$slug.'" value="'.$name.'" title="'.$name.'" '.$state.' />';
        			echo '<label for="color_filter_'.$slug.'" title="'.$name.'"></label>';
    			}
    		?>
    	</div>
    	<p class="filter-hint">Du kan bara välja en färg. För att bort färgfilter, klicka på vald färg igen.</p>
    </div>
    
   	
   	<?php /* ------- Admin specific fields ---------- */ ?>

    <?php if( is_admin() || ! request('post_id') ) : ?>
	<div class="item filter-filters">
		<h4>Tillgängliga filter för listning</h4>
		<fieldset class="cb_list">
			<input type="checkbox" name="filters" disabled="disabled" id="cb_filters_categories" value="categories">
			<label for="cb_filters_categories">Kategorier</label>

			<input type="checkbox" name="filters" id="cb_filters_brands" value="brands">
			<label for="cb_filters_brands">Märken / Tillverkare</label>

			<input type="checkbox" name="filters" id="cb_filters_pagesize" value="pagesize">
			<label for="cb_filters_pagesize">Produkter per sida</label>

			<input type="checkbox" name="filters" id="cb_filters_pricerange" value="pricerange">
			<label for="cb_filters_pricerange">Prisintervall</label>

			<input type="checkbox" name="filters" id="cb_filters_sorting" value="sorting">
			<label for="cb_filters_sorting">Sortering</label>

			<input type="checkbox" name="filters" id="cb_filters_colors" value="colors">
			<label for="cb_filters_colors">Färgfilter</label>

		</fieldset>
	</div>

    <div class="item">
        <h4>Spara urvalet</h4>
        <input type="button" class="button" id="save_button" value="Lägg till i Sparade urval"/>
    </div>
	<?php endif; ?>
</div>