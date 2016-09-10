<?php
/**
 * Templates code
 *
 * @link       http://orionorigin.com
 * @since      3.0
 *
 * @package    Wpd
 * @subpackage Wpd/includes
 */

/**
 * Templates code
 *
 * This class defines all code necessary for templates
 *
 * @since      3.0
 * @package    Wpd
 * @subpackage Wpd/includes
 * @author     ORION <support@orionorigin.com>
 */
class WPD_Template {

    /**
     * Registers the template CPT
     */
    public function register_cpt_template() {

        $labels = array(
            'name' => __('Templates', 'wpc-template'),
            'singular_name' => __('Template', 'wpc-template'),
            'add_new' => __('New template', 'wpc-template'),
            'add_new_item' => __('New template', 'wpc-template'),
            'edit_item' => __('Edit template', 'wpc-template'),
            'new_item' => __('New template', 'wpc-template'),
            'view_item' => __('View', 'wpc-template'),
            'search_items' => __('Search templates', 'wpc-template'),
            'not_found' => __('No template found', 'wpc-template'),
            'not_found_in_trash' => __('No template in the trash', 'wpc-template'),
            'menu_name' => __('Templates', 'wpc-template'),
        );

        $args = array(
            'labels' => $labels,
            'hierarchical' => false,
            'description' => 'Templates for the products customizer.',
            'supports' => array('title', 'thumbnail'),
            'public' => false,
            'menu_icon' => 'dashicons-media-default',
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_nav_menus' => false,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'has_archive' => false,
            'query_var' => false,
            'can_export' => true
        );

        register_post_type('wpc-template', $args);
    }

    /**
     * Register the templates categories
     */
    public function register_cpt_template_taxonomy() {
        $labels = array(
            'name' => __('Categories', 'Taxonomy General Name', 'wpd'),
            'singular_name' => __('Category', 'Taxonomy Singular Name', 'wpd'),
            'menu_name' => __('Categories', 'wpd'),
            'all_items' => __('All templates categories', 'wpd'),
        );
        $args = array(
            'labels' => $labels,
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => true,
            'query_var' => true,
        );
        register_taxonomy('wpc-template-cat', 'wpc-template', $args);
    }

    /**
     * Adds the base product column to the templates list
     * @param array $defaults Default columns
     * @return array
     */
    public function get_templates_columns($defaults) {
        $defaults['base_product'] = __('Base product', 'wpd');
        return $defaults;
    }

    /**
     * Returns the values for the columns in the templates list page
     * @param string $column_name Column key
     * @param type $id Template ID
     */
    public function get_templates_columns_values($column_name, $id) {
        if ($column_name === 'base_product') {

            $base_product = get_post_meta($id, "base-product", true);
            $pdt_name = get_the_title($base_product);
            $product = wc_get_product($base_product);
            if ($product->product_type == "variation")
                $link = get_edit_post_link($product->parent->id);
            else
                $link = get_edit_post_link($base_product);
            echo "<a href='$link'>$pdt_name</a>";
        }
    }

    /**
     * Adds the template design metabox on the template edition page
     */
    public function get_template_metabox() {

        $screens = array('wpc-template');

        foreach ($screens as $screen) {

            add_meta_box(
                    'wpc-template-box', __('Template', 'wpd'), array($this, 'get_template_metabox_content'), $screen
            );
        }
    }

    /**
     * Returns selected value for the select
     * @param int product_id
     * @param int current_id
     */
    private function is_selected($product_id, $current_id) {
        if ($product_id == $current_id)
            $selected = "selected";
        else
            $selected = "";
        return $selected;
    }

    /**
     * Builds the template design metabox content on the template edition page
     * @return type
     */
    public function get_template_metabox_content() {
//        global $wpdb;
//        $search = '"is-customizable";s:1:"1"';
//        $products = $wpdb->get_results(
//                "
//                           SELECT p.id
//                           FROM $wpdb->posts p
//                           JOIN $wpdb->postmeta pm on pm.post_id = p.id 
//                           WHERE p.post_type = 'product'
//                           AND pm.meta_key = 'wpc-metas'
//                           AND pm.meta_value like '%$search%'
//                           ");
        $products=  wpd_get_custom_products();

        $tmp_id = get_the_ID();
        if (isset($_GET["base-product"]))
            $base_product = $_GET["base-product"];
        else
            $base_product = get_post_meta($tmp_id, "base-product", true);
        if (empty($base_product)) {
            echo __("No base product found.", "wpd");
            return;
        }
        ob_start();
        ?>
        <div class="wrap">
            <div id="wpd-base-product">
                <label>Base Product : </label>
                <select name="base-product">
        <?php
        foreach ($products as $product) {
            $wc_product = get_product($product->id);
            if ($wc_product->product_type == "variable") {
                $variations = $wc_product->get_available_variations();
                foreach ($variations as $variation) {
                    $selected = $this->is_selected($variation["variation_id"], $base_product);
                    $attributes_str = implode(", ", $variation["attributes"]);
                    echo '<option value="' . $variation["variation_id"] . '" ' . $selected . '>' . $wc_product->post->post_title . " ($attributes_str)" . '</option>';
                }
            } else {
                $selected = $this->is_selected($product->id, $base_product);
                echo '<option value="' . $product->id . '" ' . $selected . '>' . $wc_product->post->post_title . '</option>';
            }
        }
        ?>
                </select>
            </div>
            <div id="wpc-template-container">
                    <?php
                    $editor = new WPD_Editor($base_product);
                    echo $editor->get_editor();
                    ?>
            </div>
        </div>
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        echo $output;
    }
    
    /**
     * Displays the template status select
     */
    public function get_templates_post_status() {
        if (get_post_type() == "wpc-template") {
            $statuses = array("publish", "draft");
            $current_status = get_post_status(get_the_ID());
            ?>
            <label class="mg-left-10">Status
                <select name="wpc-post-status" class="mg-bot-10">
            <?php
            foreach ($statuses as $status) {
                if ($current_status == $status)
                    echo '<option value="' . $status . '" selected>' . ucfirst($status) . '</option>';
                else
                    echo '<option value="' . $status . '">' . ucfirst($status) . '</option>';
            }
            ?>
                </select>
            </label>
            <?php
        }
    }

    /**
     * Updates a post status
     * @param int $post_id Post ID 
     * @param type $status New status
     */
    private function change_post_status($post_id, $status) {
        global $wpdb;
        $sql="update $wpdb->posts set post_status='$status' where ID=$post_id";
        $wpdb->query($sql);
//        $current_post = get_post($post_id, 'ARRAY_A');
//        $current_post['post_status'] = $status;
//        wp_update_post($current_post);
    }

    /**
     * Saves the template data
     * @param in $post_id Post ID
     */
    public function save_wpc_template($post_id) {
        if (isset($_SESSION["to_save"])) {
            update_post_meta($post_id, "data", $_SESSION["to_save"]);
            unset($_SESSION["to_save"]);
        }
        if (isset($_POST["base-product"]))
            update_post_meta($post_id, "base-product", $_POST["base-product"]);
        
        if(isset($_POST["wpc-post-status"]))
            $this->change_post_status($post_id, $_POST["wpc-post-status"]);

//            if(isset($_POST["wpc-post-status"]))
//            {
//                $template=new WPD_Template();
//                remove_action('save_post_wpc-template', array($template, 'save_wpc_template'));
//                $this->wpd_change_post_status($post_id, $_POST["wpc-post-status"]);
//                add_action('save_post_wpc-template',  array($template, 'save_wpc_template'));
//            }
    }

    /**
     * Build the base products popup when user wants to create a new template
     * @return type
     */
    public function get_product_selector() {
        global $wpdb;
        if ((isset($_GET["post_type"])) && !empty($_GET["post_type"]))
            $post_type = $_GET["post_type"];
        else
            $post_type = get_post_type();
        if ($post_type != "wpc-template")
            return;
        /* $args=array(
          'numberposts' => -1,
          'post_type'        => 'product',
          'meta_query'=> array(
          array(
          'key' => 'customizable-product',
          'value' => '1'
          )
          )
          );
          $products= get_posts($args); */
        $search = '"is-customizable";s:1:"1"';
        $products = $wpdb->get_results(
                "
                           SELECT p.id
                           FROM $wpdb->posts p
                           JOIN $wpdb->postmeta pm on pm.post_id = p.id 
                           WHERE p.post_type = 'product'
                           AND pm.meta_key = 'wpc-metas'
                           AND pm.meta_value like '%$search%'
                           ");
        ?>
        <div class="modal fade" id="wpc-products-selector-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h3 class="modal-title" id="myModalLabel">Which product would you like to use as base?</h3>
                    </div>
                    <div class="modal-body">
        <?php
        if (empty($products))
            echo __("No customizable product found. You have to create at least one customizable product before creating a template.", "wpd");
        else {
            foreach ($products as $product) {
                $wc_product = wc_get_product($product->id);
                if ($wc_product->product_type == "variable") {
                    $variations = $wc_product->get_available_variations();
                    foreach ($variations as $variation) {
                        $attributes_str = implode(", ", $variation["attributes"]);
                        echo '<div class="mg-bot-5"><label><input type="radio" name="template_base_pdt" value="' . $variation["variation_id"] . '">' . $wc_product->post->post_title . " ($attributes_str)" . '</label></div>';
                    }
                } else
                    echo '<div class="mg-bot-5"><label><input type="radio" name="template_base_pdt" value="' . $product->id . '">' . $wc_product->post->post_title . '</label></div>';
            }
            echo '<a class="button" id="wpc-select-template">' . __("Select", "wpd") . '</a>';
        }
        ?>

                    </div>
                </div>
            </div>
        </div>
                        <?php
                    }

                    /**
                     * Returns the template image url
                     * @param int $template_id Template ID
                     * @param string $size Preferred size. Default: Full
                     * @return string
                     */
                    public static function get_template_thumb($template_id = null, $size = "full") {
                        $img_url = false;
                        if (!$template_id)
                            $template_id = get_the_ID();
                        if (has_post_thumbnail($template_id)) {
                            $thumb_id = get_post_thumbnail_id($template_id);
                            $img_url = wp_get_attachment_url($thumb_id, $size);
                        }
                        return $img_url;
                    }

                    /**
                     * Builds the pagination used in the shortcodes
                     * @global object $wp_rewrite
                     * @param object $wp_query
                     * @return string
                     */
                    public static function wpc_pagination($wp_query) {

                        global $wp_rewrite;
                        $wp_query->query_vars['paged'] > 1 ? $current = $wp_query->query_vars['paged'] : $current = 1;

                        $pagination = array(
                            'base' => @add_query_arg('page', '%#%'),
                            'format' => '',
                            'total' => $wp_query->max_num_pages,
                            'current' => $current,
                            'show_all' => false,
                            'end_size' => 1,
                            'mid_size' => 2,
                            'type' => 'list',
                            'next_text' => '»',
                            'prev_text' => '«'
                        );

                        if ($wp_rewrite->using_permalinks())
                            $pagination['base'] = user_trailingslashit(trailingslashit(remove_query_arg('s', get_pagenum_link(1))) . 'page/%#%/', 'paged');

                        if (!empty($wp_query->query_vars['s']))
                            $pagination['add_args'] = array('s' => str_replace(' ', '+', get_query_var('s')));

                        return str_replace('page/1/', '', paginate_links($pagination));
                    }

                    /**
                     * Adds the template elements locking options metabox
                     */
                    public function register_tmp_object_locking_options() {
                        add_meta_box('wpc-tmp-locking-options', __('Locking options'), array($this, 'get_tmp_object_locking_options'), 'wpc-template', 'side', 'default');
                    }

                    /**
                     * Builds the template elements locking options metabox content
                     * @param type $product
                     */
                    public function get_tmp_object_locking_options($product) {
                        ?>
        <label><input type='checkbox' id='lock-mvt-x' data-property="lockMovementX" />Lock movement X</label><br>
        <label><input type='checkbox' id='lock-mvt-y' data-property="lockMovementY" />Lock movement Y</label><br>
        <label><input type='checkbox' id='lock-scl-x' data-property="lockScalingX" />Lock scaling X</label><br>
        <label><input type='checkbox' id='lock-scl-y' data-property="lockScalingY" />Lock scaling Y</label><br>
        <label><input type='checkbox' id='lock-Deletion' data-property="lockDeletion" />Lock deletion</label><br>
        <?php
    }
    
    public function get_wpd_template_screen_layout_columns( $columns ) {
            $columns['wpc-template'] = 1;
            return $columns;
        }

        public function my_screen_layout_wpc_template() {
            return 1;
        }
        
        public function metabox_order($order)
        {
            $order["advanced"]="wpc-template-box,wpc-tmp-locking-options,wpc-template-catdiv,submitdiv";
            return $order;
        }

}
