<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

$site_url = get_option( 'siteurl' );

foreach ( $item_list as $item ) {

    $image = '';

    if ( has_post_thumbnail( $item['id'] ) ) {

        $product_image = wp_get_attachment_image_src( get_post_thumbnail_id( $item['id'] ), 'ywrr_picture' );
        list( $src, $width, $height ) = $product_image;

        if ( strpos( $src, $site_url ) === false ) {
            $src = $site_url . $src;
        }

        $image = $src;

    }
    elseif ( wc_placeholder_img_src() ) {

        $src = wc_placeholder_img_src();

        if ( strpos( $src, $site_url ) === false ) {
            $src = $site_url . $src;
        }

        $image = $src;

    }

    $product_link = apply_filters( 'ywrr_product_permalink', get_permalink( $item['id'] ) );

    ?>

    <a class="ywrr-items" href="<?php echo $product_link ?>"><img class="ywrr-items-image" src="<?php echo $image ?>" /><span class="ywrr-items-title"><?php echo $item['name'] ?>&gt;</span><span class="ywrr-items-vote"><?php _e( 'Your Vote', 'yith-woocommerce-review-reminder' ) ?></span></a>

    <?php
}

