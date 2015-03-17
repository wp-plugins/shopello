<li>
    <div class="shopello_product product <?php echo $product->locale;?>" id="product_<?php echo $product->product_id; ?>">
        <div class="product_image_wrap">
            <?php
            // Product image
            foreach ($product->images as $img) :
            ?>
                <img src="<?php echo $img;?>" title="<?php echo $product->name;?>" />
            <?php
            // Only get one.
            break;
            endforeach;
            // Product image End
            ?>
        </div>

        <div class="text">
            <h3>
                <a href="<?php echo $product->url;?>" target="_blank" title="<?php echo $product->name;?>">
                    <?php echo truncate($product->name, 50, true);?>
                </a>
            </h3>
            <p class="description">
                <p class="price_wrap">
                    <?php
                    // Sales price
                    if ($product->price->regular_price > 0) :
                    ?>
                        <span class="price sales"><?php echo number_format($product->price->price); ?> <?php echo $product->price->currency; ?></span>
                        <span class="price old_price"><?php echo number_format($product->price->regular_price); ?> <?php echo $product->price->currency; ?></span>
                    <?php
                    // Sales price End
                    else :
                    // Regular price
                    ?>
                        <span class="price"><?php echo $product->price->price; ?> <?php echo $product->price->currency; ?></span>
                    <?php
                    // Regular price End
                    endif;
                    ?>
                </p>
        </div>
        <div class="checkout_button">
            <a href="<?php echo $product->url;?>" target="_blank" target="_blank" title="Gå till produkt">Köp</a>
        </div>
    </div>
</li>
