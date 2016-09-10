<?php
/**
 * Admin class
 *
 * @author  Yithemes
 * @package YITH WooCommerce Bulk Edit Products
 * @version 1.0.0
 */

if ( !defined( 'YITH_WCBEP' ) ) {
    exit;
} // Exit if accessed directly

if ( !class_exists( 'YITH_WCBEP_Admin_Premium' ) ) {
    /**
     * Admin class.
     * The class manage all the admin behaviors.
     *
     * @since    1.0.0
     * @author   Leanza Francesco <leanzafrancesco@gmail.com>
     */
    class YITH_WCBEP_Admin_Premium extends YITH_WCBEP_Admin {

        public $importer = NULL;

        /**
         * Returns single instance of the class
         *
         * @return \YITH_WCBEP
         * @since 1.0.0
         */
        public static function get_instance() {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * Constructor
         *
         * @access public
         * @since  1.0.0
         */
        public function __construct() {
            parent::__construct();

            YITH_WCBEP_Custom_Fields_Manager();

            add_action( 'wp_ajax_yith_wcbep_save_default_hidden_cols', array( $this, 'save_default_hidden_cols' ) );
            add_action( 'wp_ajax_yith_wcbep_save_enabled_columns', array( $this, 'save_enabled_columns' ) );
            add_action( 'wp_ajax_yith_wcbep_get_image_gallery_uploader', array( $this, 'get_image_gallery_uploader' ) );
            add_action( 'wp_ajax_yith_wcbep_bulk_delete_products', array( $this, 'delete_products' ) );

            add_action( 'wp_ajax_yith_wcbep_import', array( $this, 'import' ) );

            add_filter( 'yith_wcbep_settings_admin_tabs', array( $this, 'add_premium_settings_tabs' ) );

            add_action( 'yith_wcbep_import_tab', array( $this, 'render_import_tab' ) );
            add_action( 'yith_wcbep_enabled_columns_tab', array( $this, 'render_enabled_columns_tab' ) );

            // register plugin to licence/update system
            add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
            add_action( 'admin_init', array( $this, 'register_plugin_for_updates' ) );

            if ( isset( $_GET[ 'yith_wcbep_action' ] ) && 'export' === $_GET[ 'yith_wcbep_action' ] ) {
                add_action( 'init', array( $this, 'download_exported' ), 999 );
            }
        }

        public function download_exported() {
            if ( !current_user_can( 'export' ) )
                wp_die( __( 'You do not have sufficient permissions to export the content of this site.' ) );

            if ( isset( $_POST[ 'export_ids' ] ) ) {
                $ids      = json_decode( $_POST[ 'export_ids' ] );
                $exporter = new YITH_WCBEP_Exporter();
                $exporter->export_products( $ids );
                die();
            }
        }

        public function add_premium_settings_tabs( $tabs ) {
            $tabs[ 'import' ]          = __( 'Import', 'yith-woocommerce-bulk-product-editing' );
            $tabs[ 'enabled-columns' ] = __( 'Enabled Columns', 'yith-woocommerce-bulk-product-editing' );

            return $tabs;
        }

        public function render_import_tab() {
            ?>

            <h2><?php _e( 'Import Products', 'yith-woocommerce-bulk-product-editing' ) ?></h2>
            <form id="yith-wcbep-importer-form" method="post" enctype="multipart/form-data"
                  action="admin.php?page=yith_wcbep_panel&tab=import">
                <input id="yith-wcbep-importer-upload-url" name="file_url" type="file"
                       placeholder="<?php _e( 'File URL', 'yith-woocommerce-bulk-product-editing' ); ?>">
                <input type="submit" class="button button-primary button-large"
                       value="<?php _e( 'Import', 'yith-woocommerce-bulk-product-editing' ) ?>">
            </form>
            <?php
            if ( !empty( $_FILES ) && isset( $_FILES[ 'file_url' ][ 'tmp_name' ] ) && !empty( $_FILES[ 'file_url' ][ 'tmp_name' ] ) ) {
                $file_url = $_FILES[ 'file_url' ][ 'tmp_name' ];
                ?>
                <input type="hidden" id="yith-wcbep-imported-file"
                       value="<?php echo $_FILES[ 'file_url' ][ 'tmp_name' ]; ?>">
                <div id="yith-wcbep-message" class="updated notice below-h2">
                    <p><?php _e( 'Importing...', 'yith-woocommerce-bulk-product-editing' ) ?></p>
                </div>
                <?php
                $importer = new YITH_WCBEP_Importer();
                ob_start();
                $importer->import( $file_url );
                $data = ob_get_clean();
                echo $data;
            }
        }

        public function render_enabled_columns_tab() {
            wc_get_template( 'enabled-columns-tab.php', array(), '', YITH_WCBEP_TEMPLATE_PATH . '/premium/panel/' );
        }

        /**
         * Import products [AJAX]
         *
         * @access public
         * @since  1.0.0
         */
        public function import() {
            if ( isset( $_POST[ 'file_url' ] ) ) {
                $file     = $_POST[ 'file_url' ];
                $importer = new YITH_WCBEP_Importer();
                $importer->import( $file );
            }
            die();
        }


        /**
         * Delete products [AJAX]
         *
         * @access public
         * @since  1.0.0
         */
        public function delete_products() {
            if ( isset( $_POST[ 'products_to_delete' ] ) && is_array( $_POST[ 'products_to_delete' ] ) ) {
                $products_to_delete = $_POST[ 'products_to_delete' ];
                $counter            = 0;
                foreach ( $products_to_delete as $del_id ) {
                    wp_delete_post( absint( $del_id ), true );
                    $counter++;
                }

                if ( $counter > 0 )
                    echo sprintf( _n( '%s product deleted', '%s products deleted', $counter, 'yith-woocommerce-bulk-product-editing' ), $counter );
            }
            die();
        }

        /**
         * Save default hidden cols in table
         *
         * @access public
         * @since  1.0.0
         */
        public function save_default_hidden_cols() {
            if ( isset( $_POST[ 'hidden_cols' ] ) ) {
                $hidden_cols = $_POST[ 'hidden_cols' ];
                update_option( 'yith_wcbep_default_hidden_cols', $hidden_cols );
            } else {
                update_option( 'yith_wcbep_default_hidden_cols', array() );
            }
            die();
        }

        /**
         * Save enabled columns in table
         *
         * @access public
         * @since  1.1.8
         */
        public function save_enabled_columns() {
            if ( isset( $_POST[ 'enabled_columns' ] ) ) {
                $enabled_columns = $_POST[ 'enabled_columns' ];
                update_option( 'yith_wcbep_enabled_columns', $enabled_columns );
            } else {
                update_option( 'yith_wcbep_enabled_columns', array() );
            }
            die();
        }

        public function get_image_gallery_uploader() {
            if ( isset( $_POST[ 'post_id' ] ) ) {
                $post = get_post( $_POST[ 'post_id' ] );
                WC_Meta_Box_Product_Images::output( $post );
            }
            die();
        }

        /**
         * Get table [AJAX]
         *
         * @access public
         * @since  1.0.0
         */
        public function ajax_fetch_table_callback() {
            // Disable display_errors during this ajax requests to prevent malformed JSON
            $current_error_reporting = error_reporting();
            error_reporting( 0 );

            $table = new YITH_WCBEP_List_Table_Premium();
            $table->ajax_response();

            // Enable display_errors
            error_reporting( $current_error_reporting );
        }

        /**
         * Get main-tab template
         *
         * @access public
         * @since  1.0.0
         */
        public function main_tab() {
            $args = array();
            //yith_wcbep_get_template('main-tab-premium.php', $args);
            wc_get_template( 'main-tab-custom-input.php', $args, YITH_WCBEP_TEMPLATE_PATH . '/premium/', YITH_WCBEP_TEMPLATE_PATH . '/premium/' );
            wc_get_template( 'main-tab-filters-and-table.php', $args, YITH_WCBEP_TEMPLATE_PATH . '/premium/', YITH_WCBEP_TEMPLATE_PATH . '/premium/' );
            wc_get_template( 'main-tab-bulk-editor.php', $args, YITH_WCBEP_TEMPLATE_PATH . '/premium/', YITH_WCBEP_TEMPLATE_PATH . '/premium/' );
            wc_get_template( 'main-tab-columns-settings.php', $args, YITH_WCBEP_TEMPLATE_PATH . '/premium/', YITH_WCBEP_TEMPLATE_PATH . '/premium/' );
            wc_get_template( 'main-tab-importer.php', $args, YITH_WCBEP_TEMPLATE_PATH . '/premium/', YITH_WCBEP_TEMPLATE_PATH . '/premium/' );
        }

        /**
         * Get table [AJAX]
         *
         * @access public
         * @since  1.0.0
         */
        public function get_table_ajax() {
            $table = new YITH_WCBEP_List_Table_Premium();
            $table->prepare_items();
            $table->display();
            die();
        }

        /**
         * Bulk Edit Products [AJAX]
         *
         * @access public
         * @since  1.0.0
         */
        public function bulk_edit_products() {
            global $pagenow;
            if ( isset( $_POST[ 'matrix_modify' ] ) && isset( $_POST[ 'matrix_keys' ] ) && isset( $_POST[ 'edited_matrix' ] ) ) {
                $matrix_modify = $_POST[ 'matrix_modify' ];
                $matrix_keys   = $_POST[ 'matrix_keys' ];
                $edited_matrix = $_POST[ 'edited_matrix' ];

                foreach ( $edited_matrix as $row => $cols ) {
                    foreach ( $cols as $id_col => $col ) {
                        if ( $col == 0 && $id_col != 2 ) {
                            $matrix_modify[ $row ][ $id_col ] = NULL;
                        }
                    }
                }

                $id_index         = array_search( 'ID', $matrix_keys );
                $reg_price_index  = array_search( 'regular_price', $matrix_keys );
                $sale_price_index = array_search( 'sale_price', $matrix_keys );

                $title_index         = array_search( 'title', $matrix_keys );
                $slug_index          = array_search( 'slug', $matrix_keys );
                $sku_index           = array_search( 'sku', $matrix_keys );
                $image_index         = array_search( 'image', $matrix_keys );
                $image_gallery_index = array_search( 'image_gallery', $matrix_keys );
                $description_index   = array_search( 'description', $matrix_keys );
                $shortdesc_index     = array_search( 'shortdesc', $matrix_keys );
                $categories_index    = array_search( 'categories', $matrix_keys );
                $tags_index          = array_search( 'tags', $matrix_keys );

                $weight_index         = array_search( 'weight', $matrix_keys );
                $height_index         = array_search( 'height', $matrix_keys );
                $width_index          = array_search( 'width', $matrix_keys );
                $length_index         = array_search( 'length', $matrix_keys );
                $stock_quantity_index = array_search( 'stock_quantity', $matrix_keys );

                $purchase_note_index   = array_search( 'purchase_note', $matrix_keys );
                $download_limit_index  = array_search( 'download_limit', $matrix_keys );
                $download_expiry_index = array_search( 'download_expiry', $matrix_keys );
                $menu_order_index      = array_search( 'menu_order', $matrix_keys );

                $stock_status_index      = array_search( 'stock_status', $matrix_keys );
                $manage_stock_index      = array_search( 'manage_stock', $matrix_keys );
                $sold_individually_index = array_search( 'sold_individually', $matrix_keys );
                $featured_index          = array_search( 'featured', $matrix_keys );
                $virtual_index           = array_search( 'virtual', $matrix_keys );
                $downloadable_index      = array_search( 'downloadable', $matrix_keys );
                $enable_reviews_index    = array_search( 'enable_reviews', $matrix_keys );

                $tax_status_index     = array_search( 'tax_status', $matrix_keys );
                $tax_class_index      = array_search( 'tax_class', $matrix_keys );
                $backorders_index     = array_search( 'allow_backorders', $matrix_keys );
                $shipping_class_index = array_search( 'shipping_class', $matrix_keys );
                $status_index         = array_search( 'status', $matrix_keys );
                $visibility_index     = array_search( 'visibility', $matrix_keys );

                $download_type_index = array_search( 'download_type', $matrix_keys );
                $prod_type_index     = array_search( 'prod_type', $matrix_keys );

                $date_index            = array_search( 'date', $matrix_keys );
                $sale_price_from_index = array_search( 'sale_price_from', $matrix_keys );
                $sale_price_to_index   = array_search( 'sale_price_to', $matrix_keys );

                $button_text_index = array_search( 'button_text', $matrix_keys );
                $product_url_index = array_search( 'product_url', $matrix_keys );

                $upsells_index    = array_search( 'up_sells', $matrix_keys );
                $crosssells_index = array_search( 'cross_sells', $matrix_keys );

                $downloadable_files_index = array_search( 'downloadable_files', $matrix_keys );

                // ATTRIBUTES
                $attributes_indexes   = array();
                $attribute_taxonomies = wc_get_attribute_taxonomies();
                if ( $attribute_taxonomies ) {
                    foreach ( $attribute_taxonomies as $tax ) {
                        $attribute_taxonomy_name                        = wc_attribute_taxonomy_name( $tax->attribute_name );
                        $attributes_indexes[ $attribute_taxonomy_name ] = array_search( 'attr_' . $attribute_taxonomy_name, $matrix_keys );
                    }
                }

                $counter     = 0;
                $counter_new = 0;

                foreach ( $matrix_modify as $single_modify ) {
                    $id         = $single_modify[ $id_index ];
                    $reg_price  = $single_modify[ $reg_price_index ];
                    $sale_price = $single_modify[ $sale_price_index ];

                    $title         = $single_modify[ $title_index ];
                    $slug          = $single_modify[ $slug_index ];
                    $sku           = $single_modify[ $sku_index ];
                    $image         = $single_modify[ $image_index ];
                    $image_gallery = $single_modify[ $image_gallery_index ];
                    $description   = $single_modify[ $description_index ];
                    $shortdesc     = $single_modify[ $shortdesc_index ];
                    $categories    = $single_modify[ $categories_index ];
                    $tags          = $single_modify[ $tags_index ];

                    $weight         = $single_modify[ $weight_index ];
                    $height         = $single_modify[ $height_index ];
                    $width          = $single_modify[ $width_index ];
                    $length         = $single_modify[ $length_index ];
                    $stock_quantity = $single_modify[ $stock_quantity_index ];

                    $purchase_note   = $single_modify[ $purchase_note_index ];
                    $download_limit  = $single_modify[ $download_limit_index ];
                    $download_expiry = $single_modify[ $download_expiry_index ];
                    $menu_order      = $single_modify[ $menu_order_index ];

                    $stock_status = NULL;
                    if ( $single_modify[ $stock_status_index ] != NULL ) {
                        $stock_status = ( $single_modify[ $stock_status_index ] == '1' ) ? 'instock' : 'outofstock';
                    }
                    $manage_stock = NULL;
                    if ( $single_modify[ $manage_stock_index ] != NULL ) {
                        $manage_stock = ( $single_modify[ $manage_stock_index ] == '1' ) ? 'yes' : 'no';
                    }

                    $sold_individually = NULL;
                    if ( $single_modify[ $sold_individually_index ] != NULL ) {
                        $sold_individually = ( $single_modify[ $sold_individually_index ] == '1' ) ? 'yes' : 'no';
                    }
                    $featured = NULL;
                    if ( $single_modify[ $featured_index ] != NULL ) {
                        $featured = ( $single_modify[ $featured_index ] == '1' ) ? 'yes' : 'no';
                    }
                    $virtual = NULL;
                    if ( $single_modify[ $virtual_index ] != NULL ) {
                        $virtual = ( $single_modify[ $virtual_index ] == '1' ) ? 'yes' : 'no';
                    }
                    $downloadable = NULL;
                    if ( $single_modify[ $downloadable_index ] != NULL ) {
                        $downloadable = ( $single_modify[ $downloadable_index ] == '1' ) ? 'yes' : 'no';
                    }
                    $enable_reviews = NULL;
                    if ( $single_modify[ $enable_reviews_index ] != NULL ) {
                        $enable_reviews = ( $single_modify[ $enable_reviews_index ] == '1' ) ? 'open' : 'closed';
                    }

                    $tax_status     = $single_modify[ $tax_status_index ];
                    $tax_class      = $single_modify[ $tax_class_index ];
                    $backorders     = $single_modify[ $backorders_index ];
                    $shipping_class = $single_modify[ $shipping_class_index ];
                    $status         = $single_modify[ $status_index ];
                    $visibility     = $single_modify[ $visibility_index ];

                    $download_type = $single_modify[ $download_type_index ];
                    $prod_type     = $single_modify[ $prod_type_index ];

                    $date            = $single_modify[ $date_index ];
                    $sale_price_from = $single_modify[ $sale_price_from_index ];
                    $sale_price_to   = $single_modify[ $sale_price_to_index ];

                    $button_text = $single_modify[ $button_text_index ];
                    $product_url = $single_modify[ $product_url_index ];

                    $upsells = NULL;
                    if ( $single_modify[ $upsells_index ] != NULL ) {
                        $upsells = isset( $single_modify[ $upsells_index ] ) ? array_filter( array_map( 'intval', explode( ',', $single_modify[ $upsells_index ] ) ) ) : array();
                    }
                    $crosssells = NULL;
                    if ( $single_modify[ $crosssells_index ] != NULL ) {
                        $crosssells = isset( $single_modify[ $crosssells_index ] ) ? array_filter( array_map( 'intval', explode( ',', $single_modify[ $crosssells_index ] ) ) ) : array();
                    }

                    $downloadable_files = $single_modify[ $downloadable_files_index ];

                    $attributes_array = array();
                    foreach ( $attributes_indexes as $key => $value ) {
                        //$attributes_array[$key] = json_decode( $single_modify[ $value ] );
                        $attributes_array[ $key ] = $single_modify[ $value ];
                    };

                    $prod           = NULL;
                    $is_new_product = false;
                    if ( $id === 'NEW' && $title ) {
                        $counter_new++;
                        $counter--;
                        $new_post = array(
                            'post_type'  => 'product',
                            'post_title' => $title,
                        );

                        if ( !empty( $status ) )
                            $new_post[ 'post_status' ] = $status;

                        if ( !empty( $slug ) )
                            $new_post[ 'post_name' ] = sanitize_title( $slug );

                        if ( !empty( $description ) )
                            $new_post[ 'post_content' ] = $description;

                        if ( !empty( $shortdesc ) )
                            $new_post[ 'post_excerpt' ] = $shortdesc;

                        if ( !empty( $enable_reviews ) )
                            $new_post[ 'comment_status' ] = $enable_reviews;

                        if ( !empty( $date ) )
                            $new_post[ 'post_date' ] = $date;

                        $id             = wp_insert_post( $new_post );
                        $prod           = new WC_Product( $id );
                        $is_new_product = true;
                    } else {
                        $prod = wc_get_product( $id );
                    }

                    if ( $prod ) {
                        $counter++;

                        $not_is_variation = true;
                        if ( $prod->product_type == 'variation' || $prod_type == 'variation' ) {
                            $not_is_variation = false;
                        }

                        // EDIT PRODUCT TYPE
                        if ( isset( $prod_type ) && $not_is_variation )
                            wp_set_object_terms( $id, $prod_type, 'product_type' );

                        $price_change = false;

                        // EDIT REGULAR PRICE AND SALE PRICE
                        if ( isset( $reg_price ) ) {
                            $price_change = true;
                            update_post_meta( $id, '_regular_price', ( $reg_price === '' ) ? '' : wc_format_decimal( $reg_price ) );
                        }
                        if ( isset( $sale_price ) ) {
                            $price_change = true;

                            update_post_meta( $id, '_sale_price', ( $sale_price === '' ? '' : wc_format_decimal( $sale_price ) ) );
                        }

                        // EDIT SALE PRICE FROM
                        if ( isset( $sale_price_from ) ) {
                            $price_change = true;
                            update_post_meta( $id, '_sale_price_dates_from', strtotime( $sale_price_from ) );
                        }

                        // EDIT SALE PRICE TO
                        if ( isset( $sale_price_to ) ) {
                            $price_change = true;
                            update_post_meta( $id, '_sale_price_dates_to', strtotime( $sale_price_to ) );
                        }

                        if ( $price_change ) {
                            $reg_price  = $prod->regular_price;
                            $sale_price = $prod->sale_price;

                            $date_from = get_post_meta( $id, '_sale_price_dates_from', true );
                            $date_to   = get_post_meta( $id, '_sale_price_dates_to', true );

                            if ( is_numeric( $sale_price ) && '' !== $sale_price && empty( $date_to ) && empty( $date_from ) ) {
                                update_post_meta( $id, '_price', wc_format_decimal( $sale_price ) );
                            } else {
                                update_post_meta( $id, '_price', ( $reg_price === '' ) ? '' : wc_format_decimal( $reg_price ) );
                            }

                            if ( '' !== $sale_price && $date_from && $date_from <= strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
                                update_post_meta( $id, '_price', wc_format_decimal( $sale_price ) );
                            }

                            if ( $date_to && $date_to < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
                                update_post_meta( $id, '_price', ( $reg_price === '' ) ? '' : wc_format_decimal( $reg_price ) );
                                update_post_meta( $id, '_sale_price_dates_from', '' );
                                update_post_meta( $id, '_sale_price_dates_to', '' );
                            }

                        }


                        // EDIT POST
                        if ( $not_is_variation && !$is_new_product ) {
                            $this_post = array(
                                'ID' => $id,
                            );

                            if ( isset( $date ) ) {
                                $post      = $prod->get_post_data();
                                $post_date = $post->post_date;

                                if ( date( 'Y-m-d', strtotime( $prod->post_date ) ) != date( 'Y-m-d', strtotime( $date ) ) )
                                    $post_date = date( $date );

                                $this_post[ 'post_date' ]     = $post_date;
                                $this_post[ 'post_date_gmt' ] = gmdate( $post_date );
                            }

                            if ( isset( $title ) )
                                $this_post[ 'post_title' ] = $title;

                            if ( isset( $description ) )
                                $this_post[ 'post_content' ] = $description;

                            if ( isset( $shortdesc ) )
                                $this_post[ 'post_excerpt' ] = $shortdesc;

                            if ( isset( $enable_reviews ) )
                                $this_post[ 'comment_status' ] = $enable_reviews;

                            if ( isset( $status ) )
                                $this_post[ 'post_status' ] = $status;

                            if ( isset( $slug ) )
                                $this_post[ 'post_name' ] = $slug;


                            wp_update_post( $this_post );
                        }

                        // EDIT PRODUCT CATEGORIES
                        if ( isset( $categories ) && $not_is_variation ) {
                            $terms = json_decode( $categories );
                            wp_set_post_terms( $id, $terms, 'product_cat' );
                        }

                        // EDIT PRODUCT TAGS
                        if ( isset( $tags ) && $not_is_variation ) {
                            if ( is_taxonomy_hierarchical( 'product_tag' ) ) {
                                $tags_array = explode( ',', trim( $tags, " \n\t\r\0\x0B," ) );
                                $tags       = array();
                                if ( !!$tags_array ) {
                                    foreach ( $tags_array as $current_tag ) {
                                        $term_name = trim( $current_tag );
                                        $term      = get_term_by( 'name', $term_name, 'product_tag' );
                                        if ( $term ) {
                                            $tags[] = $term->term_id;
                                        } else {
                                            $term = wp_insert_term( $term_name, 'product_tag' );
                                            if ( isset( $term[ 'term_id' ] ) ) {
                                                $tags[] = $term[ 'term_id' ];
                                            }
                                        }
                                    }
                                }
                            }
                            wp_set_post_terms( $id, $tags, 'product_tag' );
                        }

                        // EDIT SKU
                        if ( isset( $sku ) )
                            update_post_meta( $id, '_sku', $sku );

                        // EDIT WEIGHT
                        if ( isset( $weight ) )
                            update_post_meta( $id, '_weight', wc_clean( $weight ) );

                        // EDIT LENGHT
                        if ( isset( $length ) )
                            update_post_meta( $id, '_length', wc_clean( $length ) );

                        // EDIT WIDTH
                        if ( isset( $width ) )
                            update_post_meta( $id, '_width', wc_clean( $width ) );

                        // EDIT HEIGHT
                        if ( isset( $height ) )
                            update_post_meta( $id, '_height', wc_clean( $height ) );


                        // EDIT PURCHASE NOTE
                        if ( isset( $purchase_note ) && $not_is_variation )
                            update_post_meta( $id, '_purchase_note', wc_clean( $purchase_note ) );

                        // EDIT PURCHASE NOTE
                        if ( isset( $download_limit ) )
                            update_post_meta( $id, '_download_limit', wc_clean( $download_limit ) );

                        // EDIT PURCHASE NOTE
                        if ( isset( $download_expiry ) )
                            update_post_meta( $id, '_download_expiry', wc_clean( $download_expiry ) );

                        // EDIT MENU ORDER
                        if ( isset( $menu_order ) && $not_is_variation )
                            update_post_meta( $id, '_menu_order', wc_clean( $menu_order ) );

                        // EDIT STOCK STATUS
                        if ( isset( $stock_status ) ) {
                            if ( !$is_new_product ) {
                                wc_update_product_stock_status( $id, $stock_status );
                            } else {
                                $prod->set_stock_status( $stock_status );
                            }
                        }
                        //update_post_meta( $id, '_stock_status',$stock_status );

                        // EDIT MANAGE STOCK
                        if ( isset( $manage_stock ) )
                            update_post_meta( $id, '_manage_stock', $manage_stock );

                        // EDIT STOCK QUANTITY
                        if ( !$prod->is_type( 'grouped' ) && isset( $stock_quantity ) ) {
                            if ( !$is_new_product ) {
                                wc_update_product_stock( $id, wc_stock_amount( $stock_quantity ) );
                            } else {
                                $prod->set_stock( $stock_quantity );
                            }
                        }

                        // EDIT SOLD INDIVIDUALLY
                        if ( isset( $sold_individually ) && $not_is_variation )
                            update_post_meta( $id, '_sold_individually', $sold_individually );

                        // EDIT FEATURED
                        if ( isset( $featured ) && $not_is_variation )
                            update_post_meta( $id, '_featured', $featured );

                        // EDIT VIRTUAL
                        if ( isset( $virtual ) )
                            update_post_meta( $id, '_virtual', $virtual );

                        // EDIT DOWNLOADABLE
                        if ( isset( $downloadable ) )
                            update_post_meta( $id, '_downloadable', $downloadable );

                        // EDIT TAX STATUS
                        if ( isset( $tax_status ) && $not_is_variation )
                            update_post_meta( $id, '_tax_status', $tax_status );

                        // EDIT TAX CLASS
                        if ( isset( $tax_class ) )
                            update_post_meta( $id, '_tax_class', $tax_class );

                        // EDIT ALLOW BACKORDERS
                        if ( isset( $backorders ) )
                            update_post_meta( $id, '_backorders', $backorders );

                        // EDIT SHIPIING CLASS
                        if ( isset( $shipping_class ) ) {
                            if ( $shipping_class > 0 ) {
                                $s = get_term_by( 'id', $shipping_class, 'product_shipping_class' );
                                wp_set_object_terms( $id, $s->name, 'product_shipping_class' );
                            } else {
                                wp_set_object_terms( $id, '', 'product_shipping_class' );
                            }
                        }

                        // EDIT VISIBILITY
                        if ( isset( $visibility ) && $not_is_variation )
                            update_post_meta( $id, '_visibility', $visibility );

                        // EDIT DOWNLOAD TYPE
                        if ( isset( $download_type ) && $not_is_variation )
                            update_post_meta( $id, '_download_type', $download_type );


                        // EDIT BUTTON TEXT
                        if ( isset( $button_text ) && $not_is_variation )
                            update_post_meta( $id, '_button_text', $button_text );

                        // EDIT PRODUCT URL
                        if ( isset( $product_url ) && $not_is_variation )
                            update_post_meta( $id, '_product_url', $product_url );


                        // EDIT ATTRIBUTES
                        $attr_data          = array();
                        $var_attributes     = array();
                        $removed_attributes = array();
                        if ( count( $attributes_array ) > 0 ) {
                            foreach ( $attributes_array as $key => $value ) {
                                if ( !!$value ) {
                                    if ( isset( $value[ 2 ] ) && is_array( $value[ 2 ] ) && count( $value[ 2 ] ) > 0 ) {
                                        $vals = array_map( 'intval', $value[ 2 ] );
                                    } else {
                                        $vals                 = array();
                                        $removed_attributes[] = $key;
                                    }

                                    if ( $not_is_variation ) {
                                        wp_set_object_terms( $id, $vals, $key );
                                    } else {
                                        // VARIATIONS
                                        if ( isset( $vals[ 0 ] ) ) {
                                            $var_attributes[ $key ] = $vals[ 0 ];
                                        } else {
                                            $var_attributes[ $key ] = array();
                                        }
                                    }

                                    $attr_data[ $key ] = array(
                                        'name'         => $key,
                                        'is_visible'   => !!$value[ 0 ] ? $value[ 0 ] : 0,
                                        'is_variation' => !!$value[ 1 ] ? $value[ 1 ] : 0,
                                        'is_taxonomy'  => '1'
                                    );

                                    if ( !$not_is_variation ) {
                                        $attr_data[ $key ][ 'value' ]        = $var_attributes[ $key ];
                                        $attr_data[ $key ][ 'is_variation' ] = 1;
                                    } else {
                                        $attr_data[ $key ][ 'value' ] = $vals;
                                    }
                                }
                            }
                        }
                        if ( count( $attr_data ) > 0 && $not_is_variation ) {
                            $product_attributes = get_post_meta( $id, '_product_attributes', true );

                            foreach ( $attr_data as $key => $value ) {
                                if ( in_array( $key, $removed_attributes ) && isset( $product_attributes[ $key ] ) ) {
                                    unset( $product_attributes[ $key ] );
                                } else {
                                    $product_attributes[ $key ] = $value;
                                }
                            }
                            update_post_meta( $id, '_product_attributes', $product_attributes );
                        }
                        if ( count( $var_attributes ) > 0 && !$not_is_variation ) {
                            //update_post_meta( $id, '_default_attributes', $default_attributes );
                            foreach ( $var_attributes as $key => $value ) {
                                $t      = get_term_by( 'id', $value, $key );
                                $t_slug = $t ? $t->slug : '';
                                var_dump( $t_slug, $key );
                                $old_meta = get_post_meta( $id, 'attribute_' . $key );
                                var_dump( $old_meta );
                                update_post_meta( $id, 'attribute_' . $key, $t_slug );

                            }
                        }


                        // UP SELLS
                        if ( isset( $upsells ) && $not_is_variation )
                            update_post_meta( $id, '_upsell_ids', $upsells );
                        // CROSS SELLS
                        if ( isset( $crosssells ) && $not_is_variation )
                            update_post_meta( $id, '_crosssell_ids', $crosssells );

                        if ( isset( $image ) ) {
                            if ( $not_is_variation ) {
                                if ( $image != '' ) {
                                    set_post_thumbnail( $id, $image );
                                } else {
                                    delete_post_thumbnail( $id );
                                }
                            } else {
                                if ( $image != '' ) {
                                    update_post_meta( $id, '_thumbnail_id', absint( $image ) );
                                } else {
                                    delete_post_meta( $id, '_thumbnail_id' );
                                }
                            }
                        }

                        // IMAGE GALLERY
                        if ( isset( $image_gallery ) && $not_is_variation ) {
                            $image_gallery = is_array( $image_gallery ) ? implode( ',', $image_gallery ) : $image_gallery;
                            update_post_meta( $id, '_product_image_gallery', $image_gallery );
                        }

                        // DOWNLOADABLE FILES
                        if ( isset( $downloadable_files ) && is_array( $downloadable_files ) ) {
                            $file_names = array();
                            $file_urls  = array();
                            $files      = array();

                            foreach ( $downloadable_files as $file ) {
                                $file_names[] = $file[ 0 ];
                                $file_urls[]  = trim( $file[ 1 ] );
                            }

                            $file_url_size      = sizeof( $file_urls );
                            $allowed_file_types = get_allowed_mime_types();

                            for ( $i = 0; $i < $file_url_size; $i++ ) {
                                if ( !empty( $file_urls[ $i ] ) ) {
                                    // Find type and file URL
                                    if ( 0 === strpos( $file_urls[ $i ], 'http' ) ) {
                                        $file_is  = 'absolute';
                                        $file_url = esc_url_raw( $file_urls[ $i ] );
                                    } elseif ( '[' === substr( $file_urls[ $i ], 0, 1 ) && ']' === substr( $file_urls[ $i ], -1 ) ) {
                                        $file_is  = 'shortcode';
                                        $file_url = wc_clean( $file_urls[ $i ] );
                                    } else {
                                        $file_is  = 'relative';
                                        $file_url = wc_clean( $file_urls[ $i ] );
                                    }

                                    $file_name = wc_clean( $file_names[ $i ] );
                                    $file_hash = md5( $file_url );

                                    // Validate the file extension
                                    if ( in_array( $file_is, array( 'absolute', 'relative' ) ) ) {
                                        $file_type  = wp_check_filetype( strtok( $file_url, '?' ) );
                                        $parsed_url = parse_url( $file_url, PHP_URL_PATH );
                                        $extension  = pathinfo( $parsed_url, PATHINFO_EXTENSION );

                                        if ( !empty( $extension ) && !in_array( $file_type[ 'type' ], $allowed_file_types ) ) {
                                            echo sprintf( __( 'The downloadable file %s cannot be used as it does not have an allowed file type. Allowed types include: %s', 'woocommerce' ), '<code>' . basename( $file_url ) . '</code>', '<code>' . implode( ', ', array_keys( $allowed_file_types ) ) . '</code>' );
                                            continue;
                                        }
                                    }

                                    // Validate the file exists
                                    if ( 'relative' === $file_is && !apply_filters( 'woocommerce_downloadable_file_exists', file_exists( $file_url ), $file_url ) ) {
                                        echo sprintf( __( 'The downloadable file %s cannot be used as it does not exist on the server.', 'woocommerce' ), '<code>' . $file_url . '</code>' );
                                        continue;
                                    }

                                    $files[ $file_hash ] = array(
                                        'name' => $file_name,
                                        'file' => $file_url
                                    );
                                }
                            }
                            update_post_meta( $id, '_downloadable_files', $files );
                        } else if ( $downloadable_files == '' ) {
                            update_post_meta( $id, '_downloadable_files', array() );
                        }

                        // SYNC FOR VARIATIONS
                        $prod_id = $id;
                        if ( !$not_is_variation ) {
                            $parent_id = $prod->parent->id;
                            WC_Product_Variable::sync( $parent_id );
                            $prod_id = $parent_id;
                        }

                        wc_delete_product_transients( $prod_id );

                        $is_variation = !$not_is_variation;
                        do_action( 'yith_wcbep_update_product', $prod, $matrix_keys, $single_modify, $is_variation );

                        /**
                         * WPML Compatilbility
                         *
                         * I changed the pagenow to post.php to use
                         * standard WPML method WCML_Products->sync_post_action
                         */
                        $old_pagenow = $pagenow;
                        $pagenow     = 'post.php';
                        do_action( 'save_post', $prod->id, $prod->post );
                        $pagenow = $old_pagenow;
                        /* ------------------------------- */
                    }


                }
                if ( $counter > 0 )
                    echo sprintf( _n( '%s product edited', '%s products edited', $counter, 'yith-woocommerce-bulk-product-editing' ), $counter );
                if ( $counter_new > 0 )
                    echo sprintf( _n( '%s new product', '%s new products', $counter_new, 'yith-woocommerce-bulk-product-editing' ), $counter_new );
            }
            die();
        }

        public function admin_enqueue_scripts() {
            parent::admin_enqueue_scripts();

            $screen   = get_current_screen();
            $is_panel = strpos( $screen->id, '_page_yith_wcbep_panel' ) > -1;
            if ( $is_panel ) {
                wp_enqueue_script( 'yith_wcbep_enabled_columns_tab_js', YITH_WCBEP_ASSETS_URL . '/js/enabled_columns_tab.js', array( 'jquery' ), '1.0.0', true );
                wp_enqueue_script( 'yith_wcbep_custom_fields_tab_js', YITH_WCBEP_ASSETS_URL . '/js/custom_fields_tab.js', array( 'jquery' ), '1.0.0', true );
            }
        }

        /**
         * Register plugins for activation tab
         *
         * @return void
         * @since 2.0.0
         */
        public function register_plugin_for_activation() {
            if ( !class_exists( 'YIT_Plugin_Licence' ) ) {
                require_once( YITH_WCBEP_DIR . 'plugin-fw/lib/yit-plugin-licence.php' );
            }

            YIT_Plugin_Licence()->register( YITH_WCBEP_INIT, YITH_WCBEP_SECRET_KEY, YITH_WCBEP_SLUG );
        }

        /**
         * Register plugins for update tab
         *
         * @return void
         * @since 2.0.0
         */
        public function register_plugin_for_updates() {
            if ( !class_exists( 'YIT_Upgrade' ) ) {
                require_once( YITH_WCBEP_DIR . 'plugin-fw/lib/yit-upgrade.php' );
            }

            YIT_Upgrade()->register( YITH_WCBEP_SLUG, YITH_WCBEP_INIT );
        }
    }
}

/**
 * Unique access to instance of YITH_WCBEP_Admin_Premium class
 *
 * @return \YITH_WCBEP_Admin_Premium
 * @since 1.0.0
 */
function YITH_WCBEP_Admin_Premium() {
    return YITH_WCBEP_Admin_Premium::get_instance();
}

?>
