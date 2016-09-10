<?php
/**
 * Package: TheShopier.
 * User: kinhdon
 * Date: 11/23/2015
 * Vertion: 1.0
 */

$term = get_term_by( "slug", $ops['slug'], "product_cat" );

$classes = array('woo-subcat-item');
if( absint($columns) > 1 && absint($is_slider) == 0 ) {
    $classes[] = 'col-lg-' . round( 24 /$columns );
    $classes[] = 'col-md-' . round( 24 / round( $columns * 992 / 1200) );
    $classes[] = 'col-sm-' . round( 24 / round( $columns * 768 / 1200) );
    $classes[] = 'col-xs-' . round( 24 / round( $columns * 480 / 1200) );
    $classes[] = 'col-mb-24';
} else {
    $classes[] = 'col-sm-24';
}

$image = '';
if( !empty( $ops['bg_img'] ) ) {
    $image = wp_get_attachment_image( $ops['bg_img'], 'theshopier_shop_subcat_large' );
}
$desc = '';
if( !empty($ops['desc']) ) $desc = '<p class="description">' . esc_html( sprintf($ops['desc'], $term->count) ) . '</p>';

$product_categories = get_categories( apply_filters( 'woocommerce_product_subcategories_args', array(
    'parent'       => $term->term_id,
    'menu_order'   => 'ASC',
    'hide_empty'   => 0,
    'hierarchical' => 1,
    'taxonomy'     => 'product_cat',
    'pad_counts'   => 1,
    'number'       => $per_page
) ) );

?>
<div class="<?php echo esc_attr(implode(' ', $classes))?>">

    <div class="nth-row-grid">
        <div class="woo-subcat-wrapper">
            <div class="subcat-info">
                <?php echo $image;?>
                <div class="subcat-info-inner">
                    <h3 class="parent_cat-heading"><?php echo esc_html($term->name);?></h3>
                    <?php echo $desc;?>
                </div>
            </div>

            <div class="col-sm-24">
                <ul class="product_list_widget list">
                <?php
                foreach( $product_categories as $category ) :
                    $small_thumbnail_size   = 'shop_thumbnail';
                    $thumbnail_id  		    = get_woocommerce_term_meta( $category->term_id, 'thumbnail_id', true  );
                    if ( $thumbnail_id ) {
                        $image = wp_get_attachment_image( $thumbnail_id, $small_thumbnail_size  );
                    } else {
                        $image = '<img alt="Placeholder" src="'.wc_placeholder_img_src().'"/>';
                    } ?>
                    <li>
                        <a class="product-image" href="<?php echo esc_url( get_term_link( $category->slug, 'product_cat' ) ); ?>" title="<?php echo esc_attr( $category->name ); ?>">
                            <?php echo $image ?>
                        </a>
                        <div class="product-detail">
                            <h2 class="text-uppercase"><?php echo $category->name; ?></h2>
                            <p><?php echo esc_html($category->description);?></p>
                            <a class="text-color1 text-link" href="<?php echo esc_url( get_term_link( $category->slug, 'product_cat' ) ); ?>" title="<?php echo esc_attr( $category->name ); ?>"><?php _e('Show now', 'nexthemes-plugin')?></a>
                        </div>
                    </li>
                <?php endforeach; ?>
                </ul>
            </div>

        </div>

    </div>

</div>
