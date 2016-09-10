<?php
/**
 * Package: TheShopier.
 * User: kinhdon
 * Date: 11/23/2015
 * Vertion: 1.0
 */

global $woocommerce_loop;

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

$image = ''; $desc = '';
if( !empty( $ops['br_logo'] ) ) $image = wp_get_attachment_image( $ops['br_logo'], 'full' );
if( !empty($ops['scr']) ) $desc = '<h3 class="parent_brand-heading">' . esc_html($ops['scr']) . '</h3>';

$ordering_args = WC()->query->get_catalog_ordering_args( $orderby, $order );
$meta_query    = WC()->query->get_meta_query();
$query_args    = array(
    'post_type'				=> 'product',
    'post_status' 			=> 'publish',
    'ignore_sticky_posts'	=> 1,
    'orderby' 				=> $ordering_args['orderby'],
    'order' 				=> $ordering_args['order'],
    'posts_per_page' 		=> $per_page,
    'meta_query' 			=> $meta_query,
    'tax_query' 			=> array(
        array(
            'taxonomy' 		=> 'product_brand',
            'terms' 		=> array_map( 'sanitize_title', explode( ',', $ops['slug'] ) ),
            'field' 		=> 'slug',
            'operator' 		=> 'IN'
        )
    )
);

if ( isset( $ordering_args['meta_key'] ) ) {
    $query_args['meta_key'] = $ordering_args['meta_key'];
}

$products = new WP_Query( $query_args );

$old_columns = $woocommerce_loop['columns'];

?>
<div class="<?php echo esc_attr(implode(' ', $classes))?>">

    <div class="nth-row-grid">
        <div class="woo-brand-wrapper">
            <div class="brand-info text-center">
                <?php echo $image;?>
                <div class="brand-info-inner">
                    <?php echo $desc;?>
                </div>
            </div>

            <?php if($products->have_posts()):?>

            <div class="content-inner col-sm-24">

                <?php echo apply_filters( 'woocommerce_before_widget_product_list', '<ul class="product_list_widget grid">' );?>

                <?php while ( $products->have_posts() ) : $products->the_post(); ?>

                    <?php wc_get_template( 'content-widget-product.php', array( 'show_rating' => true ) );?>

                <?php endwhile;?>

                <?php echo apply_filters( 'woocommerce_after_widget_product_list', '</ul>' );?>

            </div>

            <?php endif;?>

        </div>

    </div>

</div>
