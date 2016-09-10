<?php

/**
 * Package: TheShopier.
 * User: kinhdon
 * Date: 11/24/2015
 * Vertion: 1.0
 */
class Nexthemes_WooBrands{
    private $tax_cat = 'product_brand';
    private $post_type = 'product';

    public function __construct(){
        add_action('init', array($this,'registertax') );

        add_action( $this->tax_cat . '_add_form_fields', array( $this, 'add_brand_fields' ) );
        add_action( $this->tax_cat . '_edit_form_fields', array( $this, 'edit_category_fields' ), 10 );
        add_action( 'created_term', array( $this, 'save_brand_fields' ), 10, 3 );
        add_action( 'edit_term', array( $this, 'save_brand_fields' ), 10, 3 );

        // Add columns
        add_filter( 'manage_edit-'.$this->tax_cat.'_columns', array( $this, 'product_brand_columns' ) );
        add_filter( 'manage_'.$this->tax_cat.'_custom_column', array( $this, 'product_brand_column' ), 10, 3 );
    }

    public function registertax(){
        $labels = array(
            'name'                       => _x( 'Brands', 'taxonomy general name', 'nexthemes-plugin' ),
            'singular_name'              => _x( 'Brand', 'taxonomy singular name', 'nexthemes-plugin' ),
            'search_items'               => __( 'Search Brands', 'nexthemes-plugin' ),
            'popular_items'              => __( 'Popular Brands', 'nexthemes-plugin' ),
            'all_items'                  => __( 'All Brands', 'nexthemes-plugin' ),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => __( 'Edit Brand', 'nexthemes-plugin' ),
            'update_item'                => __( 'Update Brand', 'nexthemes-plugin' ),
            'add_new_item'               => __( 'Add New Brand', 'nexthemes-plugin' ),
            'new_item_name'              => __( 'New Brand Name', 'nexthemes-plugin' ),
            'separate_items_with_commas' => __( 'Separate Brands with commas', 'nexthemes-plugin' ),
            'add_or_remove_items'        => __( 'Add or remove Brands', 'nexthemes-plugin' ),
            'choose_from_most_used'      => __( 'Choose from the most used Brands', 'nexthemes-plugin' ),
            'not_found'                  => __( 'No Brand found.', 'nexthemes-plugin' ),
            'menu_name'                  => __( 'Brands', 'nexthemes-plugin' ),
        );

        $args = array(
            'hierarchical'          => true,
            'labels'                => $labels,
            'show_ui'               => true,
            'show_admin_column'     => true,
            //'update_count_callback' => '_update_post_term_count',
            'query_var'             => true,
            'rewrite'               => array( 'slug' => $this->tax_cat ),
        );

        register_taxonomy( $this->tax_cat, $this->post_type, $args );
    }

    public function add_brand_fields(){
        ?>
        <div class="form-field">
            <label><?php _e( 'Thumbnail', 'woocommerce' ); ?></label>
            <div id="product_brand_thumbnail" style="float: left; margin-right: 10px;"><img src="<?php echo esc_url( wc_placeholder_img_src() ); ?>" width="60px" height="60px" /></div>
            <div style="line-height: 60px;">
                <input type="hidden" id="product_brand_thumbnail_id" name="product_brand_thumbnail_id" />
                <button type="button" class="upload_image_button button"><?php _e( 'Upload/Add image', 'nexthemes-plugin' ); ?></button>
                <button type="button" class="remove_image_button button"><?php _e( 'Remove image', 'nexthemes-plugin' ); ?></button>
            </div>
            <script type="text/javascript">

                // Only show the "remove image" button when needed
                if ( ! jQuery( '#product_brand_thumbnail_id' ).val() ) {
                    jQuery( '.remove_image_button' ).hide();
                }

                // Uploading files
                var file_frame;

                jQuery( document ).on( 'click', '.upload_image_button', function( event ) {

                    event.preventDefault();

                    // If the media frame already exists, reopen it.
                    if ( file_frame ) {
                        file_frame.open();
                        return;
                    }

                    // Create the media frame.
                    file_frame = wp.media.frames.downloadable_file = wp.media({
                        title: '<?php _e( "Choose an image", "woocommerce" ); ?>',
                        button: {
                            text: '<?php _e( "Use image", "woocommerce" ); ?>'
                        },
                        multiple: false
                    });

                    // When an image is selected, run a callback.
                    file_frame.on( 'select', function() {
                        var attachment = file_frame.state().get( 'selection' ).first().toJSON();

                        jQuery( '#product_brand_thumbnail_id' ).val( attachment.id );
                        jQuery( '#product_brand_thumbnail' ).find( 'img' ).attr( 'src', attachment.sizes.thumbnail.url );
                        jQuery( '.remove_image_button' ).show();
                    });

                    // Finally, open the modal.
                    file_frame.open();
                });

                jQuery( document ).on( 'click', '.remove_image_button', function() {
                    jQuery( '#product_brand_thumbnail' ).find( 'img' ).attr( 'src', '<?php echo esc_js( wc_placeholder_img_src() ); ?>' );
                    jQuery( '#product_brand_thumbnail_id' ).val( '' );
                    jQuery( '.remove_image_button' ).hide();
                    return false;
                });

            </script>
            <div class="clear"></div>
        </div>
        <?php
    }

    public function edit_category_fields($term){
        $thumbnail_id = absint( get_woocommerce_term_meta( $term->term_id, 'thumbnail_id', true ) );

        if ( $thumbnail_id ) {
            $image = wp_get_attachment_thumb_url( $thumbnail_id );
        } else {
            $image = wc_placeholder_img_src();
        }

        ?>

        <tr class="form-field">
            <th scope="row" valign="top"><label><?php _e( 'Thumbnail', 'woocommerce' ); ?></label></th>
            <td>
                <div id="product_brand_thumbnail" style="float: left; margin-right: 10px;"><img src="<?php echo esc_url( $image ); ?>" width="60px" height="60px" /></div>
                <div style="line-height: 60px;">
                    <input type="hidden" id="product_brand_thumbnail_id" name="product_brand_thumbnail_id" value="<?php echo $thumbnail_id; ?>" />
                    <button type="button" class="upload_image_button button"><?php _e( 'Upload/Add image', 'woocommerce' ); ?></button>
                    <button type="button" class="remove_image_button button"><?php _e( 'Remove image', 'woocommerce' ); ?></button>
                </div>
                <script type="text/javascript">

                    // Only show the "remove image" button when needed
                    if ( '0' === jQuery( '#product_brand_thumbnail_id' ).val() ) {
                        jQuery( '.remove_image_button' ).hide();
                    }

                    // Uploading files
                    var file_frame;

                    jQuery( document ).on( 'click', '.upload_image_button', function( event ) {

                        event.preventDefault();

                        // If the media frame already exists, reopen it.
                        if ( file_frame ) {
                            file_frame.open();
                            return;
                        }

                        // Create the media frame.
                        file_frame = wp.media.frames.downloadable_file = wp.media({
                            title: '<?php _e( "Choose an image", "woocommerce" ); ?>',
                            button: {
                                text: '<?php _e( "Use image", "woocommerce" ); ?>'
                            },
                            multiple: false
                        });

                        // When an image is selected, run a callback.
                        file_frame.on( 'select', function() {
                            var attachment = file_frame.state().get( 'selection' ).first().toJSON();

                            jQuery( '#product_brand_thumbnail_id' ).val( attachment.id );
                            jQuery( '#product_brand_thumbnail' ).find( 'img' ).attr( 'src', attachment.sizes.thumbnail.url );
                            jQuery( '.remove_image_button' ).show();
                        });

                        // Finally, open the modal.
                        file_frame.open();
                    });

                    jQuery( document ).on( 'click', '.remove_image_button', function() {
                        jQuery( '#product_brand_thumbnail' ).find( 'img' ).attr( 'src', '<?php echo esc_js( wc_placeholder_img_src() ); ?>' );
                        jQuery( '#product_brand_thumbnail_id' ).val( '' );
                        jQuery( '.remove_image_button' ).hide();
                        return false;
                    });

                </script>
                <div class="clear"></div>
            </td>
        </tr>

        <?php
    }

    public function save_brand_fields($term_id, $tt_id = '', $taxonomy = ''){
        if ( isset( $_POST['product_brand_thumbnail_id'] ) && $this->tax_cat === $taxonomy ) {
            update_woocommerce_term_meta( $term_id, 'thumbnail_id', absint( $_POST['product_brand_thumbnail_id'] ) );
        }
    }

    public function product_brand_columns( $columns ){
        $new_columns          = array();
        $new_columns['cb']    = $columns['cb'];
        $new_columns['thumb'] = __( 'Image', 'nexthemes-plugin' );

        unset( $columns['cb'] );

        return array_merge( $new_columns, $columns );
    }

    public function product_brand_column( $columns, $column, $id ){
        if ( 'thumb' == $column ) {

            $thumbnail_id = get_woocommerce_term_meta( $id, 'thumbnail_id', true );

            if ( $thumbnail_id ) {
                $image = wp_get_attachment_thumb_url( $thumbnail_id );
            } else {
                $image = wc_placeholder_img_src();
            }

            $image = str_replace( ' ', '%20', $image );

            $columns .= '<img src="' . esc_url( $image ) . '" alt="' . esc_attr__( 'Thumbnail', 'nexthemes-plugin' ) . '" class="wp-post-image" height="48" width="48" />';

        }

        return $columns;
    }
}

new Nexthemes_WooBrands();