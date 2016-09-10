<?php
if ( !defined( 'YITH_WCBEP' ) ) {
    exit;
} // Exit if accessed directly

?>

    <div id="yith-wcbep-my-page-wrapper">
        <div class="yith-wcbep-filter-wrap"><h2><?php _e( 'Filters', 'yith-woocommerce-bulk-product-editing' ); ?></h2>

            <form id="yith-wcbep-filter-form" method="post">
                <table style="width:50%">
                    <tr>
                        <td class="yith-wcbep-filter-form-label-col">
                            <label><?php _e( 'Title', 'woocommerce' ) ?></label>
                        </td>
                        <td class="yith-wcbep-filter-form-content-col">
                            <select id="yith-wcbep-title-filter-select" name="yith-wcbep-title-filter-select"
                                    class="yith-wcbep-miniselect is_resetable">
                                <option value="cont"><?php _e( 'Contains', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                                <option value="notcont"><?php _e( 'Does not contain', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                                <option value="starts"><?php _e( 'Starts with', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                                <option value="ends"><?php _e( 'Ends with', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            </select>
                            <input type="text" id="yith-wcbep-title-filter-value" name="yith-wcbep-title-filter-value"
                                   class="yith-wcbep-minifield is_resetable">
                        </td>
                    </tr>
                    <tr>
                        <td class="yith-wcbep-filter-form-label-col">
                            <label><?php _e( 'SKU', 'woocommerce' ) ?></label>
                        </td>
                        <td class="yith-wcbep-filter-form-content-col">
                            <select id="yith-wcbep-sku-filter-select" name="yith-wcbep-sku-filter-select"
                                    class="yith-wcbep-miniselect is_resetable">
                                <option value="cont"><?php _e( 'Contains', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                                <option value="notcont"><?php _e( 'Does not contain', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                                <option value="starts"><?php _e( 'Starts with', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                                <option value="ends"><?php _e( 'Ends with', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            </select>
                            <input type="text" id="yith-wcbep-sku-filter-value" name="yith-wcbep-sku-filter-value"
                                   class="yith-wcbep-minifield is_resetable">
                        </td>
                    </tr>
                    <?php
                    // C A T E G O R I E S   F I L T E R
                    $cat_args = array(
                        'hide_empty' => true,
                        'orderby'    => 'name',
                        'order'      => 'ASC'
                    );
                    $categories = get_terms( 'product_cat', $cat_args );

                    if ( !empty( $categories ) ) {
                        ?>
                        <tr>
                            <td class="yith-wcbep-filter-form-label-col">
                                <label><?php _e( 'Categories', 'woocommerce' ) ?></label>
                            </td>
                            <td class="yith-wcbep-filter-form-content-col">
                                <select id="yith-wcbep-categories-filter" name="yith-wcbep-categories-filter[]"
                                        class="chosen is_resetable" multiple xmlns="http://www.w3.org/1999/html">
                                    <?php
                                    foreach ( $categories as $c ) {
                                        ?>
                                        <option value="<?php echo $c->term_id; ?>"><?php echo $c->name; ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <?php
                    } ?>

                    <?php
                    // T A G S  F I L T E R
                    $tag_args = array(
                        'hide_empty' => true,
                        'orderby'    => 'name',
                        'order'      => 'ASC'
                    );
                    $tags = get_terms( 'product_tag', $tag_args );

                    if ( !empty( $tags ) ) {
                        ?>
                        <tr>
                            <td class="yith-wcbep-filter-form-label-col">
                                <label><?php _e( 'Tags', 'woocommerce' ) ?></label>
                            </td>
                            <td class="yith-wcbep-filter-form-content-col">
                                <select id="yith-wcbep-tags-filter" name="yith-wcbep-tags-filter[]"
                                        class="chosen is_resetable" multiple xmlns="http://www.w3.org/1999/html">
                                    <?php
                                    foreach ( $tags as $t ) {
                                        ?>
                                        <option value="<?php echo $t->term_id; ?>"><?php echo $t->name; ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <?php
                    } ?>


                    <?php
                    // A T T R I B U T E S
                    $attribute_taxonomies = wc_get_attribute_taxonomies();
                    //echo '<pre>'; var_dump($attribute_taxonomies); echo '</pre>';
                    if ( $attribute_taxonomies ) {
                        foreach ( $attribute_taxonomies as $tax ) {
                            $attribute_taxonomy_name = wc_attribute_taxonomy_name( $tax->attribute_name );
                            $attr_label = $tax->attribute_label;
                            $terms = get_terms( $attribute_taxonomy_name, array( 'hide_empty' => '0' ) );
                            if ( count( $terms ) > 0 ) {
                                ?>
                                <tr>
                                    <td class="yith-wcbep-filter-form-label-col">
                                        <label><?php echo $attr_label; ?></label>
                                    </td>
                                    <td class="yith-wcbep-filter-form-content-col">
                                        <select id="yith-wcbep-attr-filter-<?php echo $attribute_taxonomy_name; ?>"
                                                data-taxonomy-name="<?php echo $attribute_taxonomy_name; ?>"
                                                name="yith-wcbep-attr-filter-<?php echo $attribute_taxonomy_name; ?>[]"
                                                class="chosen is_resetable yith_webep_attr_chosen" multiple
                                                xmlns="http://www.w3.org/1999/html">
                                            <?php
                                            foreach ( $terms as $t ) {
                                                ?>
                                                <option
                                                    value="<?php echo $t->term_id; ?>"><?php echo $t->name; ?></option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                    }
                    ?>

                    <?php do_action( 'yith_wcbep_filters_after_attribute_fields' ); ?>

                    <tr>
                        <td class="yith-wcbep-filter-form-label-col">
                            <label><?php _e( 'Regular Price', 'woocommerce' ) ?></label>
                        </td>
                        <td class="yith-wcbep-filter-form-content-col">
                            <select id="yith-wcbep-regular-price-filter-select"
                                    name="yith-wcbep-regular-price-filter-select"
                                    class="yith-wcbep-miniselect is_resetable">
                                <option value="mag"> ></option>
                                <option value="min"> <</option>
                                <option value="ug"> ==</option>
                                <option value="magug"> >=</option>
                                <option value="minug"> <=</option>
                            </select>
                            <input type="text" id="yith-wcbep-regular-price-filter-value"
                                   name="yith-wcbep-regular-price-filter-value"
                                   class="yith-wcbep-minifield is_resetable">
                        </td>
                    </tr>
                    <tr>
                        <td class="yith-wcbep-filter-form-label-col">
                            <label><?php _e( 'Sale Price', 'woocommerce' ) ?></label>
                        </td>
                        <td class="yith-wcbep-filter-form-content-col">
                            <select id="yith-wcbep-sale-price-filter-select" name="yith-wcbep-sale-price-filter-select"
                                    class="yith-wcbep-miniselect is_resetable">
                                <option value="mag"> ></option>
                                <option value="min"> <</option>
                                <option value="ug"> ==</option>
                                <option value="magug"> >=</option>
                                <option value="minug"> <=</option>
                            </select>
                            <input type="text" id="yith-wcbep-sale-price-filter-value"
                                   name="yith-wcbep-sale-price-filter-value" class="yith-wcbep-minifield is_resetable">
                        </td>
                    </tr>
                    <tr>
                        <td class="yith-wcbep-filter-form-label-col">
                            <label><?php _e( 'Weight', 'woocommerce' ) ?></label>
                        </td>
                        <td class="yith-wcbep-filter-form-content-col">
                            <select id="yith-wcbep-weight-filter-select" name="yith-wcbep-weight-filter-select"
                                    class="yith-wcbep-miniselect is_resetable">
                                <option value="mag"> ></option>
                                <option value="min"> <</option>
                                <option value="ug"> ==</option>
                                <option value="magug"> >=</option>
                                <option value="minug"> <=</option>
                            </select>
                            <input type="text" id="yith-wcbep-weight-filter-value"
                                   name="yith-wcbep-weight-filter-value" class="yith-wcbep-minifield is_resetable">
                        </td>
                    </tr>
                    <tr>
                        <td class="yith-wcbep-filter-form-label-col">
                            <label><?php _e( 'Products per page', 'yith-woocommerce-bulk-product-editing' ) ?></label>
                        </td>
                        <td class="yith-wcbep-filter-form-content-col">
                            <input type="text" id="yith-wcbep-per-page-filter" name="yith-wcbep-per-page-filter"
                                   class="" value="10">
                        </td>
                    </tr>
                    <tr>
                        <td class="yith-wcbep-filter-form-label-col">
                            <label><?php _e( 'Include Variations', 'yith-woocommerce-bulk-product-editing' ) ?></label>
                        </td>
                        <td class="yith-wcbep-filter-form-content-col">
                            <input type="checkbox" id="yith-wcbep-show-variations-filter"
                                   name="yith-wcbep-show-variations-filter">
                        </td>
                    </tr>
                </table>
                <input id="yith-wcbep-get-products" type="button" class="button button-primary button-large"
                       value="<?php _e( 'Get products', 'yith-woocommerce-bulk-product-editing' ) ?>">
                <input id="yith-wcbep-reset-filters" type="button" class="button button-secondary button-large"
                       value="<?php _e( 'Reset filters', 'yith-woocommerce-bulk-product-editing' ) ?>">
                <input id="yith-wcbep-check-by-filters" type="button" class="button button-secondary button-large"
                       value="<?php _e( 'Select on filters', 'yith-woocommerce-bulk-product-editing' ) ?>">
            </form>
        </div>

        <h2><?php _e( 'Products', 'yith-woocommerce-bulk-product-editing' ) ?></h2>

        <div id="yith-wcbep-actions-button-wrapper">
            <input id="yith-wcbep-save" type="button" class="button button-primary button-large"
                   value="<?php _e( 'Save', 'yith-woocommerce-bulk-product-editing' ) ?>">
            <input id="yith-wcbep-bulk-edit-btn" type="button" class="button button-secondary button-large"
                   value="<?php _e( 'Bulk editing', 'yith-woocommerce-bulk-product-editing' ) ?>">

            <span class="yith-wcbep-white-space"></span>

            <input id="yith-wcbep-cols-settings-btn" type="button" class="button button-secondary button-large"
                   value="<?php _e( 'Show/Hide Columns', 'yith-woocommerce-bulk-product-editing' ) ?>">

            <span class="yith-wcbep-white-space"></span>

            <input id="yith-wcbep-undo" type="button" class="button button-secondary button-large"
                   value="<?php _e( 'Undo', 'yith-woocommerce-bulk-product-editing' ) ?>">
            <input id="yith-wcbep-redo" type="button" class="button button-secondary button-large"
                   value="<?php _e( 'Redo', 'yith-woocommerce-bulk-product-editing' ) ?>">

            <span class="yith-wcbep-white-space"></span>

            <input id="yith-wcbep-export-form-btn" type="button"
                   class="button button-secondary button-large"
                   value="<?php _e( 'Export Selected', 'yith-woocommerce-bulk-product-editing' ) ?>">

            <!-- <input id="yith-wcbep-import" type="button" class="button button-secondary button-large"
                   value="<?php _e( 'Import', 'yith-woocommerce-bulk-product-editing' ) ?>">-->

            <span class="yith-wcbep-white-space"></span>
            <input id="yith-wcbep-new" type="button" class="button button-secondary button-large"
                   value="<?php _e( 'New Product', 'yith-woocommerce-bulk-product-editing' ) ?>">
            <input id="yith-wcbep-delete" type="button" class="button button-secondary button-large"
                   value="<?php _e( 'Delete Selected', 'yith-woocommerce-bulk-product-editing' ) ?>">

            <form id="yith-wcbep-export-form" action="<?php echo add_query_arg(array('yith_wcbep_action' => 'export'))?>" method="POST">
                <input type="hidden" id="yith-wcbep-export-ids" name="export_ids" value="[]">
            </form>

        </div>

        <div id="yith-wcbep-message">
            <p></p>
        </div>

        <div id="yith-wcbep-percentual-container">
        </div>

        <div id="yith-wcbep-resize-table">
            <?php _e( 'Resize Table', 'yith-woocommerce-bulk-product-editing' ); ?>
        </div>
        <div id="yith-wcbep-table-wrap">
            <?php
            $table = new YITH_WCBEP_List_Table_Premium();
            $table->prepare_items();
            $table->display();
            ?>
        </div>
    </div>

<?php
/*if ( isset( $_GET[ 'my_action' ] ) && $_GET[ 'my_action' ] == 'export' ) {
    $exporter = new YITH_WCBEP_Exporter();
    $exporter->export_products( array( 568, 569 ) );
}*/
?>