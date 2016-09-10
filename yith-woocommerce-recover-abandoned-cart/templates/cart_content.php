<?php
/**
 * YITH WooCommerce Recover Abandoned Cart Content metabox template
 *
 * @package YITH WooCommerce Recover Abandoned Cart
 * @since   1.0.0
 * @author  Yithemes
 */

?>
<table class="shop_table cart" id="yith-ywrac-table-list" cellspacing="0">
    <thead>
    <tr>
        <th class="product-thumbnail"><?php _e( 'Thumbnail', 'ywraq' ) ?></th>
        <th class="product-name"><?php _e( 'Product', 'ywraq' ) ?></th>
        <th class="product-single"><?php _e( 'Product Price', 'ywraq' ) ?></th>
        <th class="product-quantity"><?php _e( 'Quantity', 'ywraq' ) ?></th>
        <th class="product-subtotal"><?php _e( 'Total', 'ywraq' ); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ( $cart_content['cart']  as $key => $raq ):
        $_product = wc_get_product(  ( isset( $raq['variation_id'] ) &&  $raq['variation_id'] != '' ) ? $raq['variation_id'] : $raq['product_id'] );
        ?>
        <tr class="cart_item">
            <td class="product-thumbnail">
                <?php $thumbnail =  $_product->get_image();

                if ( ! $_product->is_visible() )
                    echo $thumbnail;
                else
                    printf( '<a href="%s">%s</a>', $_product->get_permalink(), $thumbnail );
                ?>
            </td>

            <td class="product-name">
                <a href="<?php echo $_product->get_permalink() ?>"><?php echo $_product->get_title() ?></a>
                <?php
                // Meta data
                $item_data = array();

                // Variation data
                if ( ! empty( $raq['variation_id'] ) && is_array( $raq['variations'] ) ) {
                    foreach ( $raq['variations'] as $name => $value ) {
                        $label = '';
                        if ( '' === $value )
                            continue;
                        $taxonomy = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( $name ) ) );

                        // If this is a term slug, get the term's nice name
                        if ( taxonomy_exists( $taxonomy ) ) {
                            $term = get_term_by( 'slug', $value, $taxonomy );
                            if ( ! is_wp_error( $term ) && $term && $term->name ) {
                                $value = $term->name;
                            }
                            $label = wc_attribute_label( $taxonomy );

                        }else {

                            if( strpos( $name, 'attribute_') !== false ) {
                                $custom_att = str_replace( 'attribute_', '', $name );

                                if ( $custom_att != '' ) {
                                    $label = wc_attribute_label( $custom_att );
                                }
                                else {
                                    $label = $name;
                                }
                            }

                        }

                        $item_data[] = array(
                            'key'   => $label,
                            'value' => $value
                        );
                    }
                }

                // Output flat or in list format
                if ( sizeof( $item_data ) > 0 ) {
                    foreach ( $item_data as $data ) {
                        echo esc_html( $data['key'] ) . ': ' . wp_kses_post( $data['value'] ) . "\n";
                    }
                }
                ?>
            </td>
            <td class="product-price">
                <?php
                echo $_product->get_price_html();
                ?>
            </td>

            <td class="product-quantity">
                <?php echo $raq['quantity'] ?>
            </td>

            <td class="product-subtotal">
                <?php
                echo wc_price($_product->price * $raq['quantity']);
                ?>
            </td>
        </tr>

    <?php endforeach ?>
    <tr>
        <td scope="col" colspan="4" style="text-align:right;"><strong><?php _e( 'Cart Subtotal', 'ywraq' ) ?></strong></td>
        <td scope="col" style="text-align:right;"><?php echo wc_price( $subtotal ) ?></td>
    </tr>
    </tbody>
</table>