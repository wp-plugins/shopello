<?php
// Create array of chosen filter fields
global $filter_arr;
$filter_arr = is_array($filters) ? $filters : explode(",", $filters);

function swp_show_filter($f)
{
    // Scope is messing, this will fix it.
    global $is_admin_ajax, $filter_arr;

    if (count($filter_arr) == 0) {
        return true;
    }
    $in_arr = in_array($f, $filter_arr);

    return $in_arr ? true : (! has_swp_item() && $is_admin_ajax);
}

function swp_admin_category_list($categories, $d = 0)
{
    if (!empty($categories)) {
        foreach ($categories as $c) {
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

    <?php if (swp_show_filter('keyword')) : ?>
    <div class="item filter-keyword">
        <h4><?php _e('Keyword', 'shopello')?></h4>
        <input type="text" id="input-keyword" placeholder="<?php _e('Keyword', 'shopello'); ?>" value="<?= $keyword?$keyword:'';?>"/>
        <p class="filter-hint"><?php _e('Use your own keywords to limit the results.', 'shopello'); ?></p>
    </div>
    <?php endif; ?>

    <?php if (swp_show_filter('categories')) : ?>
    <div class="item filter-categories">
        <h4><?php _e('Categories', 'shopello'); ?></h4>
        <?php if (!has_swp_item() and $is_admin_ajax == true) : ?>
            <p class="filter-hint"><?php _e('If a selected category has sub-categories, the subcategories will be included even when not selected.', 'shopello'); ?></p>
            <div class="scrollable">
                <?php echo swp_admin_category_list(swp_get_category_tree()); ?>
            </div>
        <?php else : ?>
            <?php
             $categories = swp_get_active_categories($params);
            foreach ($categories as $category) {
                echo "<a href='javascript:void();' data-category-id='".$category->category_id."'>".$category->category_name."</a><br/>";
            }
            ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (swp_show_filter('pagesize')) : ?>
    <div class="item filter-pagesize">
        <h4><?php _e('Products per page', 'shopello'); ?></h4>
        <select name="preview_per_page" id="input-pagesize">
            <option value="16" <?= $pagesize==16?'selected="selected"':'';?>>16</option>
            <option value="32" <?= $pagesize==32?'selected="selected"':'';?>>32</option>
            <option value="64" <?= $pagesize==64?'selected="selected"':'';?>>64</option>
        </select>
    </div>
    <?php endif; ?>

    <?php if (swp_show_filter('pricemax')) : ?>
    <div class="item filter-price-max">
        <h4><?php _e('Max Price', 'shopello'); ?> <span id="label-price-max" style="font-weight:normal;"><?= $max_price ? $max_price.__(' kr', 'shopello'): ''; ?></span></h4>
        <input id="input-price-max" type ="range" min ="10" max="<?=$max_price;?>" step ="1" value ="<?= $max_price ? $max_price : 0; ?>"/>
        <input type="button" class="button" id="reset-price-max" value="<?php _e('Remove max price', 'shopello'); ?>" />
    </div>
    <?php endif; ?>

    <?php if (swp_show_filter('sorting')) : ?>
    <div class="item filter-sort">
        <h4><?php _e('Sorting', 'shopello'); ?></h4>
        <select name="preview_sort" id="input-sort-field">
            <?php
            $sortables = array(
                'price' => __('Price', 'shopello'),
                'name' => __('Name', 'shopello'),
                'clicks' => __('Clicks', 'shopello'),
                'popularity' => __('Popularity', 'shopello')
            );

            foreach ($sortables as $value => $label) {
                $sel="";
                if (isset($params['order_by']) && $params['order_by'] == $value) {
                    $sel = ' selected="selected"';
                }

                echo "<option value='$value' $sel >$label</option>";
            }
            ?>
        </select>
        <select name="preview_sort_order" id="input-sort-order">
            <option value="ASC" <?= isset($params['order']) && $params['order'] == 'ASC' ? 'selected="selected"' : '';?>><?php _e('Rising', 'shopello'); ?></option>
            <option value="DESC" <?= isset($params['order']) && $params['order'] == 'DESC' ? 'selected="selected"' : '';?>><?php _e('Falling', 'shopello'); ?></option>
        </select>
    </div>
    <?php endif; ?>

    <?php if (swp_show_filter('colors')) : ?>
    <div class="item filter-color">
        <h4><?php _e('Color Filter', 'shopello'); ?></h4>
        <div class="colors">
            <?php
            $colors = array(
                'vit' => __('white', 'shopello'),
                'gra' => __('gray', 'shopello'),
                'svart' => __('black', 'shopello'),
                'bla' => __('blue', 'shopello'),
                'gron' => __('green', 'shopello'),
                'gul' => __('yellow', 'shopello'),
                'orange' => __('orange', 'shopello'),
                'rod' => __('red', 'shopello'),
                'rosa' => __('pink', 'shopello'),
                'lila' => __('purple', 'shopello')
            );

            foreach ($colors as $slug => $name) {
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
        <p class="filter-hint"><?php _e('You can only choose one color. Click on the chosen color to remove the colorfilter.', 'shopello'); ?></p>
    </div>
    <?php endif; ?>


    <?php /* ------- Admin specific fields ---------- */ ?>
    <?php if (!has_swp_item() && $is_admin_ajax == true) : ?>
        <div class="item filter-filters">
            <h4><?php _e('Available filters for listing', 'shopello'); ?></h4>
            <fieldset class="cb_list">

                <input type="checkbox" name="filters" id="cb_filters_keyword" value="keyword">
                <label for="cb_filters_keyword"><?php _e('Keyword', 'shopello'); ?></label>

                <input type="checkbox" name="filters" id="cb_filters_categories" value="categories">
                <label for="cb_filters_categories"><?php _e('Categories', 'shopello'); ?></label>

                <!--<input type="checkbox" name="filters" id="cb_filters_brands" value="brands">
                <label for="cb_filters_brands">Märken / Tillverkare</label>-->

                <input type="checkbox" name="filters" id="cb_filters_pagesize" value="pagesize">
                <label for="cb_filters_pagesize"><?php _e('Products per page', 'shopello'); ?></label>

                <input type="checkbox" name="filters" id="cb_filters_pricemax" value="pricemax">
                <label for="cb_filters_pricemax"><?php _e('Max Price', 'shopello'); ?></label>

                <input type="checkbox" name="filters" id="cb_filters_sorting" value="sorting">
                <label for="cb_filters_sorting"><?php _e('Sorting', 'shopello'); ?></label>

                <input type="checkbox" name="filters" id="cb_filters_colors" value="colors">
                <label for="cb_filters_colors"><?php _e('Color Filter', 'shopello'); ?></label>
            </fieldset>
        </div>

        <div class="item">
            <h4>Spara urvalet</h4>
            <input type="button" class="button" id="save_button" value="<?php _e('Save Listing', 'shopello'); ?>"/>
        </div>
    <?php endif; ?>
</div>
