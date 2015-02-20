<?php
// Create array of chosen filter fields
global $filter_arr;
$filter_arr = is_array($filters) ? $filters : explode(",", $filters );

function swp_show_filter($f)
{
    // Scope is messing, this will fix it.
    global $is_admin_ajax, $filter_arr;

    if (count($filter_arr) == 0) {
	return true;
    }
    $in_arr = in_array( $f, $filter_arr );

    return $in_arr ? true : (! has_swp_item() && $is_admin_ajax);
}

function swp_admin_category_list($categories, $d = 0)
{
    if (!empty($categories))
    {
        foreach ($categories as $c)
        {
            $id = 'adm_cat_'.$c->category_id;

            $indent = 10 + $d*10;
            $html .= "<div class='row' style='text-indent: ".$indent."px;'>";

            $html .= "<input type='checkbox' value='$c->category_id' id='$id' $styleleft />";
            $html .= "<label for='$id' style='text-indent:0;'>".$c->category_name."</label>";

            $html .= "</div>";

            if (!empty($c->children)) {
                $html .= swp_admin_category_list($c->children, $d+1);
            }
        }
    }
    return $html;
}

$max_price = $max_price ? $max_price : 5000;
?>

<div class="swp-filters filters" id="shopello_filter_wrap">

    <?php if( swp_show_filter('keyword')) : ?>
    <div class="item filter-keyword">
        <h4>Sökord</h4>
        <input type="text" id="input-keyword" placeholder="Sökterm" value="<?= $keyword?$keyword:'';?>"/>
        <p class="filter-hint">Använd egna nyckelord för att smalna av produkturvalet.</p>
    </div>
    <?php endif; ?>

    <?php if( swp_show_filter('categories')) : ?>
    <div class="item filter-categories">
        <h4>Kategorier</h4>
        <?php if( ! has_swp_item() && $is_admin_ajax == true ) : ?>
            <p class="filter-hint">Om en vald kategori har underkategorier så tas de automatiskt med i valet, trots att de inte är förkryssade.</p>
            <div class="scrollable">
                <?php echo swp_admin_category_list( swp_get_category_tree() ); ?>
            </div>
        <?php else : ?>
            <?php
             $categories = swp_get_active_categories( $params );
            foreach( $categories as $category ) {
                echo "<a href='javascript:void();' data-category-id='".$category->category_id."'>".$category->category_name."</a><br/>";
            }
            ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if( swp_show_filter('pagesize')) : ?>
    <div class="item filter-pagesize">
        <h4>Produkter per sida</h4>
        <select name="preview_per_page" id="input-pagesize">
            <option value="16" <?= $pagesize==16?'selected="selected"':'';?>>16</option>
            <option value="32" <?= $pagesize==32?'selected="selected"':'';?>>32</option>
            <option value="64" <?= $pagesize==64?'selected="selected"':'';?>>64</option>
        </select>
    </div>
    <?php endif; ?>

    <?php if( swp_show_filter('pricemax')) : ?>
    <div class="item filter-price-max">
        <h4>Pristak <span id="label-price-max" style="font-weight:normal;"><?= $max_price ? $max_price ." kr": ''; ?></span></h4>
        <input id="input-price-max" type ="range" min ="10" max="<?=$max_price;?>" step ="1" value ="<?= $max_price ? $max_price : 0; ?>"/>
        <input type="button" class="button" id="reset-price-max" value="Ta bort pristak" />
        <p class="filter-hint">Dra i handtaget för att sätta ett pristak. Tryck på knappen för att ta bort pristaket.</p>
    </div>
    <?php endif; ?>

    <?php if( swp_show_filter('sorting')) : ?>
    <div class="item filter-sort">
        <h4>Sortering</h4>
        <select name="preview_sort" id="input-sort-field">
            <?php
            $sortables = array(
                'price' => 'Pris',
                'name' => 'Namn',
                'clicks' => 'Klick',
                'popularity' => 'Popularitet'
            );

            foreach ($sortables as $value=>$label) {
                $sel="";
                if (isset($params['order_by']) && $params['order_by'] == $value) {
                    $sel = ' selected="selected"';
                }

                echo "<option value='$value' $sel >$label</option>";
            }
            ?>
        </select>
        <select name="preview_sort_order" id="input-sort-order">
            <option value="ASC" <?= isset($params['order']) && $params['order'] == 'ASC' ? 'selected="selected"' : '';?>>Stigande</option>
            <option value="DESC" <?= isset($params['order']) && $params['order'] == 'DESC' ? 'selected="selected"' : '';?> >Fallande</option>
        </select>
    </div>
    <?php endif; ?>

    <?php if( swp_show_filter('colors')) : ?>
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

    	    foreach ($colors as $slug=>$name) {
    		$checked = ($color == $name);
    		if ($checked) {
    		    $state = ' data-checked="true" checked="checked" ';
    		} else {
    		    $state = ' data-checked="false" checked="false" ';
                }

    		echo '<input type="radio" id="color_filter_'.$slug.'" name="color" class="color '.$slug.'" value="'.$name.'" title="'.$name.'" '.$state.' />';
        	echo '<label for="color_filter_'.$slug.'" title="'.$name.'"></label>';
    	    }
    	    ?>
    	</div>
    	<p class="filter-hint">Du kan bara välja en färg. För att bort färgfilter, klicka på vald färg igen.</p>
    </div>
    <?php endif; ?>


    <?php /* ------- Admin specific fields ---------- */ ?>
    <?php if( ! has_swp_item() && $is_admin_ajax == true ) : ?>
	<div class="item filter-filters">
	    <h4>Tillgängliga filter för listning</h4>
	    <fieldset class="cb_list">

		<input type="checkbox" name="filters" id="cb_filters_keyword" value="keyword">
		<label for="cb_filters_keyword">Sökfält</label>

		<input type="checkbox" name="filters" id="cb_filters_categories" value="categories">
		<label for="cb_filters_categories">Kategorier</label>

		<!--<input type="checkbox" name="filters" id="cb_filters_brands" value="brands">
		<label for="cb_filters_brands">Märken / Tillverkare</label>-->

		<input type="checkbox" name="filters" id="cb_filters_pagesize" value="pagesize">
		<label for="cb_filters_pagesize">Produkter per sida</label>

		<input type="checkbox" name="filters" id="cb_filters_pricemax" value="pricemax">
		<label for="cb_filters_pricemax">Prisintervall</label>

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
