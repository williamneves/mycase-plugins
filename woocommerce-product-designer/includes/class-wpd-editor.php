<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class-wpd-editor
 *
 * @author HL
 */
class WPD_Editor {

    private $item_id;
    private $root_item_id;
    private $wpd_product;

    public function __construct($item_id) {
        if ($item_id) {
            $this->item_id = $item_id;
            $this->wpd_product=new WPD_Product($item_id);
            $this->root_item_id = $this->wpd_product->root_product_id;
        }
    }

    function get_editor() {
        GLOBAL $wpc_options_settings, $wp_query, $wpdb;
        $wpd_query_vars = array();

        ob_start();
        $product = wc_get_product($this->item_id);
        if (!$this->wpd_product->has_part()) {
            _e('Error: No active part defined for this product. A customizable product should have at least one part defined.', 'wpd');
            return;
        }
        $wpc_metas = get_post_meta($this->root_item_id, 'wpc-metas', true);
        $general_options = $wpc_options_settings['wpc-general-options'];
        $product_price = $product->price;
//        $shop_currency_symbol=get_woocommerce_currency_symbol();
        $colors_options = $wpc_options_settings['wpc-colors-options'];
        $wpc_output_options = $wpc_options_settings['wpc-output-options'];
        if (isset($wpc_output_options['wpc-generate-layers']) && $wpc_output_options['wpc-generate-layers'] === "yes")
            $generate_layers = true;
        else
            $generate_layers = false;

        if (isset($wpc_output_options['wpc-generate-svg']) && $wpc_output_options['wpc-generate-svg'] === "yes")
            $generate_svg = true;
        else
            $generate_svg = false;

        $product_metas = get_proper_value($wpc_metas, $this->item_id, array());

        $canvas_w = $this->wpd_product->get_option($product_metas, $general_options, "canvas-w", 800);
        $canvas_h = $this->wpd_product->get_option($product_metas, $general_options, "canvas-h", 500);
        $watermark = get_proper_value($product_metas, "watermark", "");

        $bounding_box_array = get_proper_value($wpc_metas, 'bounding_box', array());
        $clip_w = get_proper_value($bounding_box_array, "width", "");
        $clip_h = get_proper_value($bounding_box_array, "height", "");
        $clip_x = get_proper_value($bounding_box_array, "x", "");
        $clip_y = get_proper_value($bounding_box_array, "y", "");
        $clip_radius = get_proper_value($bounding_box_array, "radius", "");
        $clip_radius_rect = get_proper_value($bounding_box_array, "r_radius", 0);
        $clip_type = get_proper_value($bounding_box_array, "type", "");
        $clip_border = get_proper_value($bounding_box_array, "border_color", "");

        $wpc_output_product_settings = get_proper_value($product_metas, 'output-settings', array());
        $output_w = $this->wpd_product->get_option($wpc_output_product_settings, $wpc_output_options, "wpc-min-output-width", $canvas_w);
        $output_loop_delay = $this->wpd_product->get_option($wpc_output_product_settings, $wpc_output_options, "wpc-output-loop-delay", 1000);

        $svg_colorization = get_proper_value($colors_options, "wpc-svg-colorization", 'by-path');
        $wpc_palette_type = get_proper_value($colors_options, 'wpc-color-palette', 'unlimited');
        $palette = get_proper_value($colors_options, 'wpc-custom-palette', '');
        $palette_tpl = "";

        if (isset($general_options['wpc-redirect-after-cart']) && !empty($general_options['wpc-redirect-after-cart']))
            $redirect_after = $general_options['wpc-redirect-after-cart'];
        else
            $redirect_after = 0;
        
        if (isset($general_options['responsive']) && !empty($general_options['responsive']))
            $responsive = $general_options['responsive'];
        else
            $responsive = 0;

        $wpc_img_format = "png";

        if (!empty($palette) && is_array($palette)) {
            foreach ($palette as $color) {
                $hex = str_replace("#", "", $color);
                $palette_tpl.='<span style="background-color: ' . $color . '" data-color="' . $hex . '" class="wpc-custom-color"></span>';
            }
        }
        if (isset($wp_query->query_vars["tpl"])) {
            $tpl_id = $wp_query->query_vars["tpl"];
            $wpd_query_vars["tpl"] = $tpl_id;
            $data = get_post_meta($tpl_id, "data", true);
//        Fix serialisation issue after moving the data
            if ($data === false) {
//            var_dump("Trying to fix");
                $data = $this->fix_template_data($tpl_id);
            }
        } else if (is_admin() && get_post_type() == "wpc-template") {
            $tpl_id = get_the_ID();
            $data = get_post_meta($tpl_id, "data", true);
            //        Fix serialisation issue after moving the data
            if ($data === false) {
//            var_dump("Trying to fix");
                $data = $this->fix_template_data($tpl_id);
            }
        } else if (isset($wp_query->query_vars["edit"])) {
            $variation_id = $wp_query->query_vars["product_id"];
            $cart_item_key = $wp_query->query_vars["edit"];
            $wpd_query_vars["edit"] = $cart_item_key;
            //Normal cart item edit
            if (isset($_SESSION["wpc_generated_data"][$variation_id][$cart_item_key]))
                $data = $_SESSION["wpc_generated_data"][$variation_id][$cart_item_key];
            else {
                //The user is maybe editing a cart item and then switched to another product
                global $woocommerce;
                $cart = $woocommerce->cart->get_cart();
                if (isset($cart[$cart_item_key])) {
                    if (isset($cart[$cart_item_key]["variation_id"]))
                        $old_variation_id = $cart[$cart_item_key]["variation_id"];
                    else if (isset($cart[$cart_item_key]["product_id"]))
                        $old_variation_id = $cart[$cart_item_key]["product_id"];
                    $data = $_SESSION["wpc_generated_data"][$old_variation_id][$cart_item_key];
                }
            }
            //Useful when editing cart item
            if ($data)
                $data = stripslashes_deep($data);
        } else if (isset($wp_query->query_vars["design_index"])) {
            global $current_user;
            $design_index = $wp_query->query_vars["design_index"];
            $wpd_query_vars["design_index"] = $design_index;
            $user_designs = get_user_meta($current_user->ID, 'wpc_saved_designs');
            $data = $user_designs[$design_index][2];
        } else if (isset($wp_query->query_vars["oid"])) {
            $order_item_id = $wp_query->query_vars["oid"];
            $wpd_query_vars["oid"] = $order_item_id;
            $sql = "select meta_value FROM " . $wpdb->prefix . "woocommerce_order_itemmeta where order_item_id=$order_item_id and meta_key='wpc_data'";
            //echo $sql;
            $wpc_data = $wpdb->get_var($sql);
            $data = unserialize($wpc_data);
        }

        //Previous data to load overwrites everything
        if (isset($_SESSION["wpd-data-to-load"]) && !empty($_SESSION["wpd-data-to-load"])) {
            $previous_design_str = stripslashes_deep($_SESSION["wpd-data-to-load"]);
            $previous_design = json_decode($previous_design_str);
            if(is_object($previous_design))
                $previous_design=(array)$previous_design;
            //We make sure the structure of the data matches the one loaded by the plugin
            foreach ($previous_design as $part_key=>$part_data)
            {
//                $last_data=$part_data[count($part_data)-1];
                $previous_design[$part_key]=array("json"=>$part_data);
            }
//            var_dump($previous_design);
//            var_dump($_COOKIE["wpd-data-to-load"]);
            $data=$previous_design;
//            setcookie("wpd-data-to-load");
            unset($_SESSION["wpd-data-to-load"]);
        }

        if (isset($data) && !empty($data)) {
            $design = new WPD_Design();
            $a_price = $design->get_additional_price($this->root_item_id, $data);
            $product_price+=$a_price;
            ?>
            <script>
                var to_load =<?php echo json_encode($data); ?>;
            </script>
            <?php
        }
        $available_variations = array();
        if ($product->product_type == "variable")
            $available_variations = $this->get_available_variations();

        $editor_params = array(
            "canvas_w" => $canvas_w,
            "canvas_h" => $canvas_h,
            "watermark" => $watermark,
            "clip_w" => $clip_w,
            "clip_h" => $clip_h,
            "clip_x" => $clip_x,
            "clip_r" => $clip_radius,
            "clip_rr" => $clip_radius_rect,
            "clip_y" => $clip_y,
            "clip_type" => $clip_type,
            "clip_border" => $clip_border,
            "output_w" => $output_w,
            "output_loop_delay" => $output_loop_delay,
            "svg_colorization" => $svg_colorization,
            "palette_type" => $wpc_palette_type,
            "print_layers" => $generate_layers,
            "generate_svg" => $generate_svg,
            "output_format" => $wpc_img_format,
            "global_variation_id" => $this->item_id,
            "redirect_after" => $redirect_after,
            "responsive" => $responsive,
            "palette_tpl" => $palette_tpl,
            "translated_strings" => array(
                "deletion_error_msg" => __("The deletion of this object is not allowed", "wpd"),
                "loading_msg" => __("Just a moment", "wpd"),
                "empty_object_msg" => __("The edition area is empty.", "wpd"),
                "delete_all_msg" => __("Do you really want to delete all items in the design area ?", "wpd"),
                "delete_msg" => __("Do you really want to delete the selected items ?", "wpd"),
                "empty_txt_area_msg" => __("Please enter the text to add.", "wpd"),
                "cart_item_edition_switch" => __("You're editing a cart item. If you switch to another product and update the cart, the previous item will be removed from the cart. Do you really want to continue?", "wpd"),
                "svg_background_tooltip" => __("Background color (SVG files only)", "wpd"),
            ),
            "query_vars" => $wpd_query_vars,
            "thousand_sep" => wc_get_price_thousand_separator(),
            "decimal_sep" => wc_get_price_decimal_separator(),
            "nb_decimals" => wc_get_price_decimals(),
            "variations" => $available_variations
        );

        $this->register_styles();
        $this->register_scripts();

//        $related_products_options = get_proper_value($wpc_options_settings, 'wpc-related-products', array());
        $text_options = get_proper_value($wpc_options_settings, 'wpc-texts-options', array());
        $shapes_options = get_proper_value($wpc_options_settings, 'wpc-shapes-options', array());
        $cliparts_options = get_proper_value($wpc_options_settings, 'wpc-images-options', array());
        $uploads_options = get_proper_value($wpc_options_settings, 'wpc-upload-options', array());
        $designs_options = get_proper_value($wpc_options_settings, 'wpc-designs-options', array());
        $wpc_social_networks = get_proper_value($wpc_options_settings, 'wpc_social_networks', array());
        $ui_options = get_proper_value($wpc_options_settings, 'wpc-ui-options', array());

        $facebook_app_id = get_proper_value($wpc_social_networks, 'wpc-facebook-app-id', "");
        $facebook_app_secret = get_proper_value($wpc_social_networks, 'wpc-facebook-app-secret', "");
        $instagram_app_id = get_proper_value($wpc_social_networks, 'wpc-instagram-app-id', "");
        $instagram_app_secret = get_proper_value($wpc_social_networks, 'wpc-instagram-app-secret', "");

        $text_tab_visible = get_proper_value($text_options, 'visible-tab', 'yes');
        $shape_tab_visible = get_proper_value($shapes_options, 'visible-tab', 'yes');
        $clipart_tab_visible = get_proper_value($cliparts_options, 'visible-tab', 'yes');
        $design_tab_visible = get_proper_value($designs_options, 'visible-tab', 'yes');
        $upload_tab_visible = get_proper_value($uploads_options, 'visible-tab', 'yes');
        
        $ui_fields=  wpd_get_ui_options_fields();
        foreach ($ui_fields as $key=>$field) {
            $icon_field_name="";
            if(isset($field["icon"]))
            {
                $icon_field_name=$key."-icon";
            }
            
            wpd_generate_css_tab($ui_options, "$key-tools", $icon_field_name, $key);
        }
        ?>
        <script>
            var wpd =<?php echo json_encode($editor_params); ?>;
        </script>
        <div class='wpc-container'>
            <?php $this->get_toolbar(); ?>
            <div class="wpc-editor-wrap">
                <div class="wpc-editor-col">
                    <div id="wpc-tools-box-container" class="Accordion" tabindex="0">
                        <?php
                        if (isset($wpc_metas['related-products']) && !empty($wpc_metas['related-products']) &&($product->product_type == "variation")) {
                            ?>
                            <div class="AccordionPanel" id="related-products-panel">
                                <div id="related-products" class="AccordionPanelTab"><?php _e("PICK A PRODUCT", "wpd"); ?></div>
                                <div class="AccordionPanelContent">
                                    <?php
                                    $related_attributes = $wpc_metas['related-products'];
                                    $wpd_root_product=new WPD_Product($this->root_item_id);
                                    $usable_attributes = $wpd_root_product->extract_usable_attributes();
                                    $variation = wc_get_product($this->item_id);
                                    $selected_attributes = $variation->get_variation_attributes();
                                    $to_search = array();
                                    $edit_mode_indic="";

//                                    var_dump($usable_attributes);

                                    foreach ($usable_attributes as $attribute_name => $attribute_data) {
                                        $attribute_key = $attribute_data["key"];
                                        if (in_array($attribute_key, $related_attributes)) {
                                            echo $attribute_data["label"] . ":<br>";
                                            ?>
                                            <div class="wpd-rp-attributes-container">
                                                <?php
                                                foreach ($attribute_data["values"] as $attribute_value) {
                                                    $to_search = $selected_attributes;
                                                    if (is_object($attribute_value)) {//Taxonomy
                                                        $sanitized_value = $attribute_value->slug;
                                                        $label = $attribute_value->name;
                                                    } else {
                                                        $sanitized_value = sanitize_title($attribute_value);
                                                        $label = $attribute_value;
                                                    }
                                                    $to_search[$attribute_key] = sanitize_title($sanitized_value);//$attribute_value;//sanitize_title($sanitized_value);
                                                    
                                                    $variation_to_load = $this->get_variation_from_attributes($to_search);
                                                    
                                                    if (!$variation_to_load)
                                                        continue;
                                                    $variation = wc_get_product($variation_to_load);
                                                    $img_id = $variation->get_image_id();
                                                    if ($img_id)
                                                        $glimpse = "<img src='" . wp_get_attachment_url($img_id) . "'>";
                                                    else
                                                        $glimpse = $label;

                                                    $design_index = false;
                                                    if (isset($wp_query->query_vars["design_index"]))
                                                        $design_index = $wp_query->query_vars["design_index"];

                                                    $cart_item_key = false;
                                                    if (isset($wp_query->query_vars["edit"]))
                                                    {
                                                        $cart_item_key = $wp_query->query_vars["edit"];
                                                        $edit_mode_indic="cart-item-edit";
                                                    }

                                                    $order_item_id = false;
                                                    if (isset($wp_query->query_vars["oid"]))
                                                        $order_item_id = $wp_query->query_vars["oid"];

                                                    $tpl_id = false;
                                                    if (isset($wp_query->query_vars["tpl"]))
                                                        $tpl_id = $wp_query->query_vars["tpl"];

                                                    $wpd_product=new WPD_Product($variation_to_load);
                                                    $design_url = $wpd_product->get_design_url($design_index, $cart_item_key, $order_item_id, $tpl_id);
                                                    $selected_class = ($variation_to_load == $this->item_id) ? "selected" : "";
                                                    
                                                    $wpd_variation_to_load=new WPD_Product($variation_to_load);
                                                    
                                                    ?>
                                                <a class="wpd-rp-attribute <?php echo $selected_class." ".$edit_mode_indic; ?>" href="<?php echo $design_url; ?>" data-original-title="<?php echo $label; ?>" data-desc="<?php echo $wpd_variation_to_load->get_related_product_desc();?>"><?php echo $glimpse; ?></a>
                                                    <?php
                                                }
                                                ?>
                                                    <div id="wpd-rp-desc">
                                                        <?php echo $this->wpd_product->get_related_product_desc();?>
                                                    </div>
                                            </div>
                                            <?php
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                        <?php if ($text_tab_visible == "yes") { ?>
                            <div class="AccordionPanel" id="text-panel">
                                <div id="text-tools" class="AccordionPanelTab"><?php _e("TEXT", "wpd"); ?></div>
                                <div class="AccordionPanelContent">
                                    <?php $this->get_text_tools($text_options,$colors_options); ?>
                                </div>
                            </div>
                            <?php
                        }
                        if ($shape_tab_visible == "yes") {
                            ?>
                            <div class="AccordionPanel" id="shapes-panel">
                                <div id="shapes-tools" class="AccordionPanelTab"><?php _e("SHAPES", "wpd"); ?></div>
                                <div class="AccordionPanelContent">
                                    <?php $this->get_shapes_tools($shapes_options,$colors_options); ?>
                                </div>
                            </div>
                            <?php
                        }
                        if ($upload_tab_visible == "yes") {
//Create a conflict for admin post page so we disable it
//                            if (!is_admin()) {
                            ?>
                            <div class="AccordionPanel" id="uploads-panel">
                                <div id="uploads-tools" class="AccordionPanelTab"><?php _e("UPLOADS", "wpd"); ?></div>
                                <div class="AccordionPanelContent">
                                    <?php $this->get_uploads_tools($uploads_options); ?>                                 
                                </div>
                            </div>
                            <?php
//                                    }
                        }
                        if ($clipart_tab_visible == "yes") {
                            ?>
                            <div class="AccordionPanel" id="cliparts-panel">
                                <div id="cliparts-tools" class="AccordionPanelTab"><?php _e("CLIPARTS", "wpd"); ?></div>
                                <div class="AccordionPanelContent">
                                    <?php $this->get_images_tools($cliparts_options); ?>                                 
                                </div>
                            </div>
                            <?php
                        }
                        if (!empty($facebook_app_id) && !empty($facebook_app_secret)) {
                            ?>
                            <div class="AccordionPanel" id="facebook-panel">
                                <div id="facebook-tools" class="AccordionPanelTab"><?php _e("FACEBOOK", "wpd"); ?></div>
                                <div class="AccordionPanelContent">
                                    <?php $this->get_facebook_tools(); ?>                                 
                                </div>
                            </div>
                            <?php
                        }
                        if (!empty($instagram_app_id) && !empty($instagram_app_secret)) {
                            ?>
                            <div class="AccordionPanel" id="instagram-panel">
                                <div id="instagram-tools" class="AccordionPanelTab"><?php _e("INSTAGRAM", "wpd"); ?></div>
                                <div class="AccordionPanelContent">
                                    <?php $this->get_instagram_tools(); ?>                                 
                                </div>
                            </div>
                            <?php
                        }

                        if ($design_tab_visible == "yes") {
                            ?>

                            <div class="AccordionPanel" id="user-designs-panel">
                                <div id="my-designs-tools" class="AccordionPanelTab"><?php _e("MY DESIGNS", "wpd"); ?></div>
                                <div class="AccordionPanelContent">
                                    <?php $this->get_user_designs_tools(); ?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>

                    </div>

                </div>
                <div class="wpc-editor-col-2">
                    <div id="wpc-editor-container">
                        <canvas id="wpc-editor" ></canvas>
                    </div>

                    <div id="product-part-container" class="">
                        <?php $this->get_parts(); ?>
                    </div>
                    <?php
                    if (!is_admin()) {
                        WPD_Design::get_option_form($this->root_item_id, $wpc_metas);
                    }
                    ?>
                    <div id="debug"></div>

                </div>
                <?php
                //We don't show the column at all if there is nothing to show inside

                if (isset($general_options['wpc-download-btn']))
                    $download_btn = $general_options['wpc-download-btn'];

                if (isset($general_options['wpc-preview-btn']))
                    $preview_btn = $general_options['wpc-preview-btn'];

                if (isset($general_options['wpc-save-btn']))
                    $save_btn = $general_options['wpc-save-btn'];

                if (isset($general_options['wpc-cart-btn']))
                    $cart_btn = $general_options['wpc-cart-btn'];

                if (
                        (isset($preview_btn) && $preview_btn !== "0") ||
                        (isset($download_btn) && $download_btn !== "0") ||
                        (isset($save_btn) && $save_btn !== "0") ||
                        (isset($cart_btn) && $cart_btn !== "0")
                ) {
                    ?>
                    <div class=" wpc-editor-col">
                        <?php
                        $this->get_design_actions_box();

                        if (!is_admin())
                            $this->get_cart_actions_box();
                        ?>                      
                    </div>
                    <?php
                }
                ?>
            </div>

        </div>
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    private function get_variation_from_attributes($attributes) {
        $available_variations = $this->get_available_variations();
        foreach ($available_variations as $variation_id => $variation_attributes) {
            $diff = array_udiff($attributes, $variation_attributes, 'strcasecmp');
//            if ($attributes == $variation_attributes)
            if(empty($diff))
                return $variation_id;
        }
        return false;
    }

    function get_available_variations() {
        $root_product = wc_get_product($this->root_item_id);
        $default_available_variations = $root_product->get_available_variations();
        $variations = array();
        foreach ($default_available_variations as $variation_data) {
            $variations[$variation_data["variation_id"]] = $variation_data["attributes"];
        }

        return $variations;
    }

    private function fix_template_data($tpl_id) {
        GLOBAL $wpdb;
        $sql = "select meta_value from $wpdb->postmeta where post_id='$tpl_id' and meta_key='data'";
//            var_dump($sql);
        $value = $wpdb->get_var($sql);
        //Replace the line breaks (create an issue during the import)
        $value = mb_eregi_replace("\n", "|n", $value);

        $data = preg_replace('!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", $value);
        $data = unserialize($data);
//            var_dump($data);
        if ($data)
            update_post_meta($tpl_id, "data", stripslashes_deep($data));
        return $data;
    }

    private function get_toolbar() {
        GLOBAL $wpc_options_settings;
//        $general_options = $wpc_options_settings['wpc-general-options'];
        $ui_options = get_proper_value($wpc_options_settings, 'wpc-ui-options', array());

        $options_array = array(
            'grid-btn' => "grid",
            'clear_all_btn' => "clear",
            'delete_btn' => "delete",
            'copy_paste_btn' => "duplicate",
            'send_to_back_btn' => "back",
            'bring_to_front_btn' => "bring",
            'flip_v_btn' => "flipV",
            'flip_h_btn' => "flipH",
            'align_h_btn' => "centerH",
            'align_v_btn' => "centerV",
            'undo-btn' => "undo",
            'redo-btn' => "redo"
        );
        
        $attribut_value_array["background-size"] = '30px';
        $attribut_value_array["background-position"] = 'center';
            
        foreach ($options_array as $id => $field_name) {
            wpd_generate_css_tab($ui_options, $id, $field_name, '', $attribut_value_array);
        }
        ?>
        <div id="wpc-buttons-bar">
        <!--        <button id="zoom-in-btn" data-placement="top" data-original-title="<?php // _e("Zoom in","wpd");    ?>"></button>
        <button id="zoom-out-btn" data-placement="top" data-original-title="<?php // _e("Zoom out","wpd");    ?>"></button>
        <button id="zoom-reset-btn" data-placement="top" data-original-title="<?php // _e("Zoom reset","wpd");    ?>"></button>-->
            <span id="grid-btn" data-placement="top" data-original-title="<?php _e("grid", "wpd"); ?>"></span>
            <span id="clear_all_btn" data-placement="top" data-original-title="<?php _e("Clear all", "wpd"); ?>"></span>
            <span id="delete_btn" data-placement="top" data-original-title="<?php _e("Delete", "wpd"); ?>"></span>
            <span id="copy_paste_btn" data-placement="top" data-original-title="<?php _e("Duplicate", "wpd"); ?>"></span>
            <span id="send_to_back_btn" data-placement="top" data-original-title="<?php _e("Send to back", "wpd"); ?>"></span>
            <span id="bring_to_front_btn" data-placement="top" data-original-title="<?php _e("Bring to front", "wpd"); ?>"></span>
            <span id="flip_h_btn" data-placement="top" data-original-title="<?php _e("Flip horizontally", "wpd"); ?>"></span>
            <span id="flip_v_btn" data-placement="top" data-original-title="<?php _e("Flip vertically", "wpd"); ?>"></span>
            <span id="align_h_btn" data-placement="top" data-original-title="<?php _e("Center horizontally", "wpd"); ?>"></span>
            <span id="align_v_btn" data-placement="top" data-original-title="<?php _e("Center vertically", "wpd"); ?>"></span>
            <span id="undo-btn" data-placement="top" data-original-title="<?php _e("Undo", "wpd"); ?>"></span>
            <span id="redo-btn" data-placement="top" data-original-title="<?php _e("Redo", "wpd"); ?>"></span>
        </div>
        <?php
    }

    private function get_text_tools($text_options) {
        GLOBAL $wpc_options_settings;
        $setting_text = get_proper_value($wpc_options_settings, "wpc-texts-options", array());
        $ui_options = get_proper_value($wpc_options_settings, 'wpc-ui-options', array());
        $default_text_color=get_proper_value($ui_options, 'default-text-color');
        $default_text_bg_color=get_proper_value($ui_options, 'default-bg-color');
        $default_outline_bg_color=get_proper_value($ui_options, 'default-outline-bg-color');
        
        $options_array = array('font-family', 'font-size', 'bold', 'italic', 'text-color', 'background-color', 'outline-width', 'outline', 'curved',
            'text-radius', 'text-spacing', 'opacity', 'text-alignment', 'underline', 'text-strikethrough', 'text-overline');

        foreach ($options_array as $option) {
            $text_components[$option] = get_proper_value($text_options, $option, 'yes');
        }

        $fonts = get_option("wpc-fonts");
        if (empty($fonts)) {
            $fonts = $this->get_default_fonts();
        }
        ?>
        <div class="text-tool-container dspl-table">
            <div >
                <span class="text-label"><?php _e("Text", "wpd"); ?></span>
                <span class="">
                    <textarea id = "new-text" class="text-element-border text-container "></textarea>
                    <button id="wpc-add-text" class="wpc-btn-effect"><?php _e("ADD", "wpd"); ?></button>
                </span>
            </div>
            <?php
            if ($text_components['font-family'] == "yes") {
                ?>
                <div >
                    <span ><?php _e("Font", "wpd"); ?></span>
                    <span class="font-selector-container ">
                        <select id="font-family-selector" class="text-element-border">
                            <?php
                            $preload_div="";
                            foreach ($fonts as $font) {
                                $font_label = $font[0];
                                echo "<optgroup style='font-family:$font_label'><option>$font_label</option></optgroup>";
                                $preload_div.="<span style='font-family: $font_label;'>.</span>";
                            }
                            ?>

                        </select>
                    </span>
                </div>
                <?php
                echo "<div id='wpd-fonts-preloader'>$preload_div</div>";
            }
            if ($text_components['font-size'] == "yes") {
                ?>
                <div >
                    <span><?php _e("Size", "wpd"); ?></span>
                    <span >
                        <!--<input id="font-size-selector" type="number" class="text-element-border size-set" value="14">-->
                        <?php
                        $options = array();
                        $max_filtered_size=  apply_filters("wpd-max-font-size", 30);
                        $min_filtered_size=  apply_filters("wpd-min-font-size", 8);
                        $selected_filtered_size=  apply_filters("wpd-default-font-size", 30);
                        
                        
                        $default_size = intval(get_proper_value($setting_text, "default-font-size" ,$selected_filtered_size));
                        $min_size = intval(get_proper_value($setting_text, "min-font-size" ,$min_filtered_size));
                        $max_size = intval(get_proper_value($setting_text, "max-font-size" ,$max_filtered_size));
                        
                        for ($i = $min_size; $i <= $max_size; $i++) {
                            $options[$i] = $i;
                        }
                        echo $this->get_html_select("font-size-selector", "font-size-selector", "text-element-border text-tools-select", $options, $default_size);
                        ?>
                    </span>
                </div>
                <?php
            }
            if ($text_components['bold'] == "yes" || $text_components['italic'] == "yes" || $text_components['text-color'] == "yes" || $text_components['background-color'] == "yes") {
                ?>
                <div >
                    <span>
                        <?php _e("Style", "wpd"); ?>
                    </span> 
                    <div class="mg-r-element ">
                        <?php
                        if ($text_components['bold'] == "yes") {
                            ?>
                            <input type="checkbox" id="bold-cb" class="custom-cb">
                            <label for="bold-cb" data-placement="top" data-original-title="<?php _e("Bold", "wpd"); ?>"></label>
                            <?php
                        }
                        if ($text_components['italic'] == "yes") {
                            ?>
                            <input type="checkbox" id="italic-cb" class="custom-cb">
                            <label for="italic-cb" data-placement="top" data-original-title="<?php _e("Italic", "wpd"); ?>"></label>
                            <?php
                        }
                        if ($text_components['text-color'] == "yes") {
                            ?>
                            <span id="txt-color-selector" class=" "  data-placement="top" data-original-title="<?php _e("Text color", "wpd"); ?>" style="background-color: <?php echo $default_text_color;?>;"></span>
                            <?php
                        }
                        if ($text_components['background-color'] == "yes") {
                            ?>
                            <span id="txt-bg-color-selector" class="bg-color-selector " data-placement="top" data-original-title="<?php _e("Background color", "wpd"); ?>" style="background-color: <?php echo $default_text_bg_color;?>;"></span>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
            if ($text_components['outline-width'] == "yes" || $text_components['outline'] == "yes") {
                ?>
                <div>
                    <span ><?php _e("Outline", "wpd"); ?>
                    </span>
                    <div>
                        <?php
                        if ($text_components['outline-width'] == "yes") {
                            ?>
                            <label  for="o-thickness-slider" class=" width-label"><?php _e("Width", "wpd"); ?></label>
                            <?php
                            $options = array(0 => __("None", "wpd"), 1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
                            echo $this->get_html_select("o-thickness-slider", "o-thickness-slider", "text-element-border text-tools-select", $options);
                        }
                        if ($text_components['outline'] == "yes") {
                            ?>
                            <div class="color-container">
                                <label for="color" class=" color-label"><?php _e("Color", "wpd"); ?></label> 
                                <span id="txt-outline-color-selector" class="bg-color-selector " data-placement="top" data-original-title="<?php _e("Background color", "wpd"); ?>" style="background-color: <?php echo $default_outline_bg_color;?>;"></span>
                            </div>
                            <?php
                        }
                        ?>
                    </div>

                </div>
                <?php
            }
            if ($text_components['curved'] == "yes") {
                ?>
                <div >
                    <span><?php _e("Curved", "wpd"); ?></span>
                    <div>
                        <input type="checkbox" id="cb-curved" class="custom-cb checkmark"> 
                        <label for="cb-curved" id="cb-curved-label" ></label>

                        <label for="radius" class="radius-label "><?php _e("Radius", "wpd"); ?></label>
                        <?php
                        $options = array();
                        for ($i = 1; $i <= 20; $i++) {
                            array_push($options, $i);
                        }
                        echo $this->get_html_select("spacing", "curved-txt-spacing-slider", "text-element-border text-tools-select", $options, 9);
                        ?>
                        <div class="spacing-container">
                            <label for="spacing" class="spacing-label "><?php _e("Spacing", "wpd"); ?></label>
                            <?php
                            $options = array();
                            for ($i = 0; $i <= 30; $i++) {
                                $options[$i * 10] = $i * 10;
                            }
                            echo $this->get_html_select("radius", "curved-txt-radius-slider", "text-element-border text-tools-select", $options, 150);
                            ?>
                        </div>
                    </div>
                </div>
                <?php
            }

            if ($text_components['opacity'] == "yes") {
                ?>
                <div>
                    <span ><?php _e("Opacity", "wpd"); ?></span>
                    <span >
                        <?php
                        $this->get_opacity_dropdown("opacity", "opacity-slider", "text-element-border text-tools-select");
                        ?>
                    </span>
                </div>
                <?php
            }
            if ($text_components['text-alignment'] == "yes") {
                ?>
                <div>
                    <span><?php _e("Alignment", "wpd"); ?></span>
                    <div class="mg-r-element">
                        <input type="radio" id="txt-align-left" name="radio" class="txt-align" value="left"/>
                        <label for="txt-align-left" ><span></span></label>

                        <input type="radio" id="txt-align-center" name="radio" class="txt-align" value="center"/>
                        <label for="txt-align-center"><span ></span></label>

                        <input type="radio" id="txt-align-right" name="radio" class="txt-align" value="right"/>
                        <label for="txt-align-right"><span ></span></label>

                    </div>

                </div>
                <?php
            }
            if ($text_components['underline'] == "yes" || $text_components['text-strikethrough'] == "yes" || $text_components['text-overline'] == "yes") {
                ?>
                <div >
                    <span><?php _e("Decoration", "wpd"); ?></span>
                    <div class=" mg-r-element">
                        <?php
                        if ($text_components['underline'] == "yes") {
                            ?>
                            <input type="radio" id="underline-cb" name="txt-decoration" class="txt-decoration" value="underline">
                            <label for="underline-cb" data-placement="top" data-original-title="<?php _e("Underline", "wpd"); ?>"><span></span></label>
                            <?php
                        }
                        if ($text_components['text-strikethrough'] == "yes") {
                            ?>
                            <input type="radio" id="strikethrough-cb" name="txt-decoration" class="txt-decoration" value="line-through">
                            <label for="strikethrough-cb" data-placement="top" data-original-title="<?php _e("Strikethrough", "wpd"); ?>"><span></span></label>
                            <?php
                        }
                        if ($text_components['text-overline'] == "yes") {
                            ?>
                            <input type="radio" id="overline-cb" name="txt-decoration" class="txt-decoration" value="overline">
                            <label for="overline-cb" data-placement="top" data-original-title="<?php _e("Overline", "wpd"); ?>"><span></span></label>
                            <?php
                        }
                        ?>
                        <input type="radio" id="txt-none-cb" name="txt-decoration" class="txt-decoration" value="none">
                        <label for="txt-none-cb" data-placement="top" data-original-title="<?php _e("None", "wpd"); ?>"><span></span></label>
                    </div>
                </div>
            <?php } ?>
        </div>
        <?php
    }

    private function get_shapes_tools($shapes_options,$colors_options) {
        global $wpc_options_settings;
        $general_default_color=get_proper_value($colors_options, 'default-color');
        $options_array = array('background-color', 'outline-width', 'outline', 'opacity', 'square', 'r-square', 'circle', 'triangle', 'heart', 'polygon', 'star');
        $ui_options = get_proper_value($wpc_options_settings, 'wpc-ui-options', array());
        $default_shape_color=get_proper_value($ui_options, 'default-shape-color');
        $default_shape_outline_bg_color=get_proper_value($ui_options, 'default-shape-outline-bg-color');
        foreach ($options_array as $option) {
            $shapes_components[$option] = get_proper_value($shapes_options, $option, 'yes');
        }
        ?>
        <div class="dspl-table">
            <?php
            if ($shapes_components['background-color'] == "yes") {
                ?>
                <div>
                    <span class="text-label"><?php _e("Background", "wpd"); ?></span>
                    <span class="">
                        <span id="shape-bg-color-selector" class="bg-color-selector " data-placement="top" data-original-title="<?php _e("Background color", "wpd"); ?>" style="background-color: <?php echo $default_shape_color;?>;"></span>
                    </span>
                </div>
                <?php
            }
            if ($shapes_components['outline-width'] == "yes" || $shapes_components['outline'] == "yes") {
                ?>
                <div>
                    <span class="text-label"><?php _e("Outline", "wpd"); ?></span>
                    <span class="">
                        <?php if ($shapes_components['outline-width'] == "yes") { ?>
                            <label class="width-label"><?php _e("Width", "wpd"); ?></label>
                            <?php
                            $options = array(0 => "None", 1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
                            echo $this->get_html_select("shape-thickness-slider", "shape-thickness-slider", "text-element-border text-tools-select", $options);
                        }
                        if ($shapes_components['outline'] == "yes") {
                            ?>
                            <div class="color-container">
                                <label class=" color-label"><?php _e("Color", "wpd"); ?></label> 
                                <span id="shape-outline-color-selector" class="bg-color-selector " data-placement="top" data-original-title="<?php _e("Outline color", "wpd"); ?>" style="background-color: <?php echo $default_shape_outline_bg_color;?>;"></span>
                            </div>
                        <?php } ?>
                    </span>
                </div>
                <?php
            }
            if ($shapes_components['opacity'] == "yes") {
                ?>
                <div>
                    <span class="text-label"><?php _e("Opacity", "wpd"); ?></span>
                    <span class="">
                        <?php
                        echo $this->get_opacity_dropdown("shape-opacity-slider", "shape-opacity-slider", "");
                        ?>
                    </span>
                </div>
                <?php
            }
            if ($shapes_components['square'] == "yes" || $shapes_components['r-square'] == "yes" || $shapes_components['circle'] == "yes" || $shapes_components['triangle'] == "yes" || $shapes_components['heart'] == "yes" || $shapes_components['polygon'] == "yes" || $shapes_components['star'] == "yes") {
                ?>
                <div>
                    <span class="text-label">
                        <?php _e("Shapes", "wpd"); ?>
                    </span>
                    <div class="img-container shapes">
                        <?php if ($shapes_components['square'] == "yes") { ?>
                            <span id="square-btn"></span>
                        <?php }if ($shapes_components['r-square'] == "yes") { ?>
                            <span id="r-square-btn"></span>
                        <?php }if ($shapes_components['circle'] == "yes") { ?>
                            <span id="circle-btn"></span>
                        <?php }if ($shapes_components['triangle'] == "yes") { ?>
                            <span id="triangle-btn"></span>
                        <?php }if ($shapes_components['heart'] == "yes") { ?>
                            <span id="heart-btn"></span>
                        <?php }if ($shapes_components['polygon'] == "yes") { ?>
                            <span id="polygon5" class="polygon-btn" data-num="5"></span>
                            <span id="polygon6" class="polygon-btn" data-num="6"></span>
                            <span id="polygon7" class="polygon-btn" data-num="7"></span>
                            <span id="polygon8" class="polygon-btn" data-num="8"></span>
                            <span id="polygon9" class="polygon-btn" data-num="9"></span>
                            <span id="polygon10" class="polygon-btn" data-num="10"></span>
                        <?php }if ($shapes_components['star'] == "yes") { ?>
                            <span id="star5" class="star-btn" data-num="5"></span>
                            <span id="star6" class="star-btn" data-num="6"></span>
                            <span id="star7" class="star-btn" data-num="7"></span>
                            <span id="star8" class="star-btn" data-num="8"></span>
                            <span id="star9" class="star-btn" data-num="9"></span>
                            <span id="star10" class="star-btn" data-num="10"></span>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
        <?php
    }

    private function get_uploads_tools($options) {
        $opacity = get_proper_value($options, 'opacity', 'yes');
        if (isset($options['wpc-uploader']))
            $uploader = $options['wpc-uploader'];
        $form_class = "custom-uploader";
        if ($uploader == "native")
            $form_class = "native-uploader";
        if (!is_admin()) {
            ?>
            <form id="userfile_upload_form" class="<?php echo $form_class; ?>" method="POST" action="<?php echo admin_url('admin-ajax.php'); ?>" enctype="multipart/form-data">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('wpc-picture-upload-nonce'); ?>">
                <input type="hidden" name="action" value="handle_picture_upload">
                <?php
                if ($uploader == "native") {
                    ?>
                    <input type="file" name="userfile" id="userfile">
                    <?php
                } else {
                    ?>        
                    <div id="drop">
                        <a><?php _e("Pick a file", "wpd"); ?></a>
                        <label for="userfile"></label>
                        <input type="file" name="userfile" id="userfile"/>
                        <div class="acd-upload-info"></div>
                    </div>
                    <?php
                }
                ?>
            </form>

            <div id="acd-uploaded-img" class="img-container"></div>
            <?php
        } else
            echo "<span class='filter-set-label' style='display: inline-block;'></span><a id='wpc-add-img' class='button' style='margin-bottom: 10px;'>".__("Add image", "wpd")."</a>";

        $options_array = array('grayscale', 'invert', 'sepia1', 'sepia2', 'blur', 'sharpen', 'emboss');
        foreach ($options_array as $option) {
            $filters_settings[$option] = get_proper_value($options, $option, 'yes');
        }
        ?>

        <div class="filter-set-container">
            <?php
            if ($filters_settings['grayscale'] == "yes" || $filters_settings['invert'] == "yes" || $filters_settings['sepia1'] == "yes" || $filters_settings['sepia2'] == "yes" || $filters_settings['blur'] == "yes" || $filters_settings['sharpen'] == "yes" || $filters_settings['emboss'] == "yes") {
                ?>
                <span class="filter-set-label"><?php _e("Filters", "wpd"); ?></span>
                <span>
                    <div class="mg-r-element ">

                        <?php $this->get_image_filters(2, $options); ?>

                    </div>

                </span>
                <?php
            }
            ?>

        </div>
        <?php if ($opacity == "yes") { ?>
            <div>
                <span ><?php _e("Opacity", "wpd"); ?></span>
                <span >   
                    <?php
                    $this->get_opacity_dropdown("img-opacity-slider", "img-opacity-slider", "text-element-border text-tools-select");
                    ?>
                </span>
            </div>
            <?php
        }
    }

    private function get_opacity_dropdown($name, $id, $class = "") {
        $options = array();
        for ($i = 0; $i <= 10; $i++) {
            $key = $i / 10;
            $value = $i * 10;
            $options["$key"] = "$value%";
        }
        echo $this->get_html_select($name, $id, $class, $options, 1);
    }

    private function get_image_filters($index, $options) {
        $options_array = array('grayscale', 'invert', 'sepia1', 'sepia2', 'blur', 'sharpen', 'emboss');
        foreach ($options_array as $option) {
            $filters_settings[$option] = get_proper_value($options, $option, 'yes');
        }

        if ($filters_settings['grayscale'] == "yes") {
            ?> 
            <input type="checkbox" id="grayscale-<?php echo $index; ?>"  class="custom-cb filter-cb acd-grayscale">
            <label for="grayscale-<?php echo $index; ?>"><?php _e("Grayscale", "wpd"); ?></label>
            <?php
        }
        if ($filters_settings['invert'] == "yes") {
            ?>
            <input type="checkbox" id="invert-<?php echo $index; ?>" class="custom-cb filter-cb acd-invert">
            <label for="invert-<?php echo $index; ?>"><?php _e("Invert", "wpd"); ?></label>
            <?php
        }
        if ($filters_settings['sepia1'] == "yes") {
            ?>
            <input type="checkbox" id="sepia-<?php echo $index; ?>" class="custom-cb filter-cb acd-sepia">
            <label for="sepia-<?php echo $index; ?>"><?php _e("Sepia 1", "wpd"); ?></label>
            <?php
        }
        if ($filters_settings['sepia2'] == "yes") {
            ?>
            <input type="checkbox" id="sepia2-<?php echo $index; ?>" class="custom-cb filter-cb acd-sepia2">
            <label for="sepia2-<?php echo $index; ?>"><?php _e("Sepia 2", "wpd"); ?></label>
            <?php
        }
        if ($filters_settings['blur'] == "yes") {
            ?>
            <input type="checkbox" id="blur-<?php echo $index; ?>" class="custom-cb filter-cb acd-blur">
            <label for="blur-<?php echo $index; ?>"><?php _e("Blur", "wpd"); ?></label>
            <?php
        }
        if ($filters_settings['sharpen'] == "yes") {
            ?>
            <input type="checkbox" id="sharpen-<?php echo $index; ?>" class="custom-cb filter-cb acd-sharpen">
            <label for="sharpen-<?php echo $index; ?>"><?php _e("Sharpen", "wpd"); ?></label>
            <?php
        }
        if ($filters_settings['emboss'] == "yes") {
            ?>
            <input type="checkbox" id="emboss-<?php echo $index; ?>" class="custom-cb filter-cb acd-emboss">
            <label for="emboss-<?php echo $index; ?>"><?php _e("Emboss", "wpd"); ?></label>
            <?php
        }
    }

    private function get_images_tools($options) {
        GLOBAL $wpc_options_settings;
        $cliparts_options = get_proper_value($wpc_options_settings, 'wpc-images-options', array());
        $use_lazy_load = get_proper_value($cliparts_options, 'lazy', 'yes');
        if ($use_lazy_load=='yes') {
            $clipart_class = 'o-lazy';
            $src_attr="data-original";
        }else{
            $clipart_class = '';
            $src_attr="src";
        }
        
        $opacity = get_proper_value($options, 'opacity', 'yes');
        ?>
        <!--<div class="">-->
        <?php
        $args = array(
            'numberposts' => -1,
            'post_type' => 'wpc-cliparts'
        );
        $cliparts_groups = get_posts($args);
        echo '<div id="img-cliparts-accordion" class="Accordion minimal" tabindex="0">';
        foreach ($cliparts_groups as $cliparts_group) {
            $cliparts = get_post_meta($cliparts_group->ID, "wpc-cliparts", true);
            $cliparts_prices = get_post_meta($cliparts_group->ID, "wpc-cliparts-prices", true);
            if (!empty($cliparts)) {
                echo '<div class="AccordionPanel">
                                    <div class="AccordionPanelTab">' . $cliparts_group->post_title . ' (' . count($cliparts) . ')</div>
                                    <div class="AccordionPanelContent img-container">';

                foreach ($cliparts as $i => $clipart_id) {
                    $attachment_url = wp_get_attachment_url($clipart_id);
                    $price = 0;
                    if (isset($cliparts_prices[$i]))
                        $price = $cliparts_prices[$i];
                    echo "<span class='clipart-img'><img class='$clipart_class' $src_attr='$attachment_url' data-price='$price'></span>";
                }
                echo '</div>
                            </div>';
            }
        }
        echo '</div>';

        $options_array = array('grayscale', 'invert', 'sepia1', 'sepia2', 'blur', 'sharpen', 'emboss');
        foreach ($options_array as $option) {
            $filters_settings[$option] = get_proper_value($options, $option, 'yes');
        }
        ?>

        <div class="filter-set-container">
            <?php
            if ($filters_settings['grayscale'] == "yes" || $filters_settings['invert'] == "yes" || $filters_settings['sepia1'] == "yes" || $filters_settings['sepia2'] == "yes" || $filters_settings['blur'] == "yes" || $filters_settings['sharpen'] == "yes" || $filters_settings['emboss'] == "yes") {
                ?>
                <span class="filter-set-label"><?php _e("Filters", "wpd"); ?></span>
                <?php
            }
            ?>
            <span>
                <div class="mg-r-element ">
                    <?php $this->get_image_filters(1, $options); ?>
                    <div id="clipart-bg-color-container"></div>

                </div>

            </span>

        </div>
        <?php if ($opacity == "yes") { ?>
            <div>
                <span ><?php _e("Opacity", "wpd"); ?></span>
                <span >   
                    <?php $this->get_opacity_dropdown("opacity", "txt-opacity-slider", "text-element-border text-tools-select"); ?>
                </span>
            </div>
        <?php } ?>
        <?php
    }
    
    function get_social_login_url($network)
    {
        $url = $_SERVER["REQUEST_URI"];
        
        $url_parts = parse_url($url);
        if(!isset($url_parts['query']))
            $url_parts['query']="";
        parse_str($url_parts['query'], $params);

        $params['social-login'] = $network;
        
        $output_url=  "?";
        $count=1;
        foreach ($params as $key=>$value)
        {
            $output_url.="$key=$value";
            if($count<count($params))
                $output_url.="&";
        }
        
        
        return $output_url;
    }

    private function get_facebook_tools() {
        
        ?>
        <div class="wpc-rs-app">
            <a class="wpc-facebook acd-social-login" href="<?php echo $this->get_social_login_url("facebook");?>"><?php _e("Extract my pictures", "wpd"); ?></a>

        </div>
        <div class="img-container">
            <?php
            if (isset($_SESSION["wpd-facebook-images"])) {
                foreach ($_SESSION["wpd-facebook-images"] as $facebook_img) {
                    echo "<span class='clipart-img'><img src='$facebook_img'></span>";
                }
            }
            ?>
        </div>

        <?php
    }

    private function get_instagram_tools() {
        ?>
        <div class="wpc-rs-app">
            <a class="wpc-instagram acd-social-login" href="<?php echo $this->get_social_login_url("instagram");?>"><?php _e("Extract my pictures", "wpd"); ?></a>
        </div>
        <div class="img-container">
            <?php
            if (isset($_SESSION["wpd-instagram-images"])) {
                foreach ($_SESSION["wpd-instagram-images"] as $facebook_img) {
                    echo "<span class='clipart-img'><img src='$facebook_img'></span>";
                }
            }
            ?>
        </div>

        <?php
    }

    private function get_user_designs_tools() {
        if (is_user_logged_in()) {
            GLOBAL $current_user;
            GLOBAL $wpc_options_settings;
            $designs_options = get_proper_value($wpc_options_settings, 'wpc-designs-options', array());
            $saved_visible = get_proper_value($designs_options, 'saved', 'yes');
            $orders_visible = get_proper_value($designs_options, 'orders', 'yes');
            $user_designs = get_user_meta($current_user->ID, 'wpc_saved_designs');
            $user_orders_designs = $this->get_user_orders_designs($current_user->ID);
            ?>
            <div id="my-designs-accordion" class="Accordion minimal" tabindex="0">
                <?php
                if ($saved_visible === "yes") {
                    ?>
                    <div class="AccordionPanel">
                        <div class="AccordionPanelTab"><?php _e("Saved Designs", "wpd"); ?></div>
                        <div class="AccordionPanelContent">
                            <?php echo $this->get_user_design_output_block($user_designs); ?>
                        </div>
                    </div>
                    <?php
                }

                if ($orders_visible === "yes") {
                    ?>
                    <div class="AccordionPanel">
                        <div class="AccordionPanelTab"><?php _e("Past Orders", "wpd"); ?></div>
                        <div class="AccordionPanelContent">
                            <?php echo $this->get_user_design_output_block($user_orders_designs); ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php
        } else {
            _e("You need to be logged in before loading your designs.", "wpd");
        }
    }

    /**
     * Returns user ordered designs
     * @global object $wpdb
     * @param type $user_id
     * @return array
     */
    private function get_user_orders_designs($user_id) {
        global $wpdb;
        $designs = array();
        $args = array(
            'numberposts' => -1,
            'meta_key' => '_customer_user',
            'meta_value' => $user_id,
            'post_type' => 'shop_order',
            'post_status' => array('wc-processing', 'wc-completed')
        );

        $orders = get_posts($args);
        foreach ($orders as $order) {
            $sql_1 = "select distinct order_item_id FROM " . $wpdb->prefix . "woocommerce_order_items where order_id=$order->ID";
            $order_items_id = $wpdb->get_col($sql_1);
            foreach ($order_items_id as $order_item_id) {
                $sql_2 = "select meta_key, meta_value FROM " . $wpdb->prefix . "woocommerce_order_itemmeta where order_item_id=$order_item_id and meta_key in ('_product_id', '_variation_id', 'wpc_data')";
                $order_item_metas = $wpdb->get_results($sql_2);
                $normalized_item_metas = array();
                foreach ($order_item_metas as $order_item_meta) {
                    $normalized_item_metas[$order_item_meta->meta_key] = $order_item_meta->meta_value;
                }
                if (!isset($normalized_item_metas["wpc_data"]))
                    continue;

                if ($normalized_item_metas["_variation_id"])
                    $product_id = $normalized_item_metas["_variation_id"];
                else
                    $product_id = $normalized_item_metas["_product_id"];
                array_push($designs, array($product_id, $order->post_date, unserialize($normalized_item_metas["wpc_data"]), $order_item_id));
            }
        }
        return $designs;
    }

    private function get_user_design_output_block($user_designs) {
        $output = "";
        foreach ($user_designs as $s_index => $user_design) {
            if (!empty($user_design)) {
                $variation_id = $user_design[0];
                $save_time = $user_design[1];
                $design_data = $user_design[2];
                $order_item_id = "";
                //Comes from an order
                if (count($user_design) >= 4)
                    $order_item_id = $user_design[3];
                $output.="<div class='wpc_order_item' data-item='$variation_id'>";
                if (count($user_design) > 1)
                    $output.="<span data-original-title='$save_time' class='info-icon'></span>";
                if (is_array($design_data)) {
                    //            var_dump($design_data);
                    $new_version = false;
                    $upload_dir = wp_upload_dir();
                    if (isset($design_data["output"]["files"])) {
                        $tmp_dir = $design_data["output"]["working_dir"];
                        $design_data = $design_data["output"]["files"];
                        $new_version = true;
                    }
                    foreach ($design_data as $data_key => $data) {
                        if (!empty($data)) {
                            if ($new_version) {
                                $generation_url = $upload_dir["baseurl"] . "/WPC/$tmp_dir/$data_key/";
                                $img_src = $generation_url . $data["image"];
                                $original_part_img_url = "";
                            } else {
                                if (!isset($data["image"]))
                                    continue;
                                $img_src = $data["image"];
                                $original_part_img_url = $data["original_part_img"];
                            }

                            if ($order_item_id)
                                $modal_id = $order_item_id . "_$variation_id" . "_$data_key";
                            else
                                $modal_id = $s_index . "_$variation_id" . "_$data_key";

                            $output.='<span><a class="wpd-button" data-toggle="modal" data-target="#' . $modal_id . '">' . ucfirst($data_key) . '</a></span>';
                            $modal = '<div class="modal fade wpc-modal wpc_part" id="' . $modal_id . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                          <div class="modal-content">
                                            <div class="modal-header">
                                              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                              <h4 class="modal-title" id="myModalLabel' . $modal_id . '">'.__("Preview", "wpd").'</h4>
                                            </div>
                                            <div class="modal-body">
                                                <div style="background-image:url(' . $original_part_img_url . ')"><img src="' . $img_src . '"></div>
                                            </div>
                                          </div>
                                        </div>
                                      </div>';
                            array_push(wpd_retarded_actions::$code, $modal);
                            add_action('wp_footer', array('wpd_retarded_actions', 'display_code'), 10, 1);
                        }
                    }
                    
                    $wpd_product=new WPD_Product($variation_id);
                    if ($order_item_id)
                        $output.='<a class="wpd-button" href="' . $wpd_product->get_design_url(false, false, $order_item_id) . '">' . __("Load", "wpc") . '</a>';
                    else {
                        $output.='<a class="wpd-button" href="' . $wpd_product->get_design_url($s_index) . '">' . __("Load", "wpc") . '</a>';
                        $output.='<a class="wpd-button wpd-delete-design" data-index="' . $s_index . '">' . __("Delete", "wpc") . '</a>';
                    }
                }
                $output.="</div>";
            }
        }
        return $output;
    }

    private function get_parts() {
        $parts = get_option("wpc-parts");
        $is_first = true;
        $wpc_metas = get_post_meta($this->root_item_id, 'wpc-metas', true);
        ?>
        <div id="product-part-container">
            <ul id="wpc-parts-bar">
                <?php
                foreach ($parts as $part) {
                    $part_key = sanitize_title($part);
                    if (get_proper_value($wpc_metas, $this->item_id, array()) && get_proper_value($wpc_metas[$this->item_id], 'parts', array()) && get_proper_value($wpc_metas[$this->item_id]['parts'], $part_key, array())) {
                        $bg_included_id = get_proper_value($wpc_metas[$this->item_id]['parts'][$part_key], 'bg-inc');
                        $bg_not_included_id = get_proper_value($wpc_metas[$this->item_id]['parts'][$part_key], 'bg-not-inc');
                        if (get_proper_value($wpc_metas[$this->item_id]['parts'][$part_key], 'ov')) {
                            $part_ov_img = get_proper_value($wpc_metas[$this->item_id]['parts'][$part_key]['ov'], 'img');
                            $overlay_included = get_proper_value($wpc_metas[$this->item_id]['parts'][$part_key]['ov'], 'inc', "-1");
                            $enabled = get_proper_value($wpc_metas[$this->item_id]['parts'][$part_key], 'enabled', false);
                        }
                    }
//                    if ((!($bg_included_id || $bg_included_id == "0"))||!$enabled)
                    if (!$enabled)
                        continue;
                    $class = "";
                    if ($is_first)
                        $class = "class='active'";
                    $is_first = false;
                    $img_ov_src = "";

                    if (isset($part_ov_img)) {
                        $img_ov_src = wp_get_attachment_url($part_ov_img);
                    }

                    $bg_not_included_src = "";
                    if (!empty($bg_not_included_id))
                        $bg_not_included_src = wp_get_attachment_url($bg_not_included_id);

                    if ($bg_included_id == "0") {
                        $bg_included_src = "";
                        $part_img = $part;
                    } else {
                        $bg_included_src = wp_get_attachment_url($bg_included_id);
                        if($bg_included_src)
                            $part_img = '<img src="' . $bg_included_src . '">';
                        else
                            $part_img=$part;
                    }
                    ?>
                    <li data-id="<?php echo $part_key; ?>" data-url="<?php echo $bg_not_included_src; ?>" data-bg="<?php echo $bg_included_src; ?>" <?php echo $class; ?> data-placement="top" data-original-title="<?php echo $part; ?>" data-ov="<?php echo $img_ov_src; ?>" data-ovni="<?php echo $overlay_included; ?>">
                        <?php echo $part_img; ?>
                    </li>
                    <?php
                }
                ?>
            </ul>
        </div>
        <?php
    }

    private function get_design_actions_box() {
        GLOBAL $wpc_options_settings;
        $general_options = $wpc_options_settings['wpc-general-options'];
        $ui_options = get_proper_value($wpc_options_settings, 'wpc-ui-options', array());
        wpd_generate_css_tab($ui_options, "wpc-action-title", false, "cart-box");
        wpd_generate_css_tab($ui_options, "preview-btn", "icon", "preview-btn");
        wpd_generate_css_tab($ui_options, "download-btn", "icon", "download-btn");
        wpd_generate_css_tab($ui_options, "save-btn", "icon", "save-btn");
        

        if (isset($general_options['wpc-download-btn']))
            $download_btn = $general_options['wpc-download-btn'];
        if (isset($general_options['wpc-preview-btn']))
            $preview_btn = $general_options['wpc-preview-btn'];
        if (isset($general_options['wpc-save-btn']))
            $save_btn = $general_options['wpc-save-btn'];

        $design_index = -1;
        if (isset($_GET["design_index"])) {
            $design_index = $_GET["design_index"];
        }
        //We don't show the box at all if there is nothing to show inside
        if (isset($preview_btn) && $preview_btn === "0" && isset($download_btn) && $download_btn === "0" && isset($save_btn) && $save_btn === "0")
            return;
        ?>
        <div id="wpc-design-btn-box" >
            <div class="title" id="wpc-action-title"><?php _e("ACTIONS", "wpd"); ?></div>
            <?php
            if (isset($preview_btn) && $preview_btn !== "0") {
                ?>
                <button id="preview-btn" class="wpc-btn-effect"><?php _e("PREVIEW", "wpd"); ?></button>
                <?php
            }
            if (!is_admin()) {
                if (isset($download_btn) && $download_btn !== "0") {
                    ?>
                    <button id="download-btn" class="wpc-btn-effect"><?php _e("DOWNLOAD", "wpd"); ?></button>
                    <?php
                }
                if (isset($save_btn) && $save_btn !== "0") {
                    ?>
                    <button id="save-btn" class="wpc-btn-effect" data-index="<?php echo $design_index; ?>"><?php _e("SAVE", "wpd"); ?></button>
                    <?php
                }
            }
            ?>
        </div>
        <?php
        $modal = '<div class="modal fade wpd-modal" id="wpd-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  <h4 class="modal-title" id="myModalLabel">' . __('PREVIEW', 'wpd') . '</h4>
                </div>
                <div class="modal-body txt-center">
                </div>
              </div>
            </div>
        </div>';
        if(!is_admin())
        {
            array_push(wpd_retarded_actions::$code, $modal);
            add_action('wp_footer', array('wpd_retarded_actions', 'display_code'), 10, 1);
        }
        else
            echo $modal;
    }

    private function get_cart_actions_box() {
        GLOBAL $wpc_options_settings;
        $general_options = $wpc_options_settings['wpc-general-options'];
        $ui_options = get_proper_value($wpc_options_settings, 'wpc-ui-options', array());
        wpd_generate_css_tab($ui_options, "cart_title", "", "action-box");
        wpd_generate_css_tab($ui_options, "minus-btn", "", "minus");
        wpd_generate_css_tab($ui_options, "plus-btn", "", "plus");
        wpd_generate_css_tab($ui_options, "add-to-cart-btn", "icon", "add-to-cart-btn");
        if (isset($general_options['wpc-cart-btn']))
            $cart_btn = $general_options['wpc-cart-btn'];

        $product = wc_get_product($this->item_id);
        if(!$product->price)
            $product->price=0;
        $thousand_sep = wc_get_price_thousand_separator();
        $decimal_sep = wc_get_price_decimal_separator();
        $nb_decimals = wc_get_price_decimals();
//
//        $product_price = ' <span class="total_order">' . number_format($product->price, $nb_decimals, $decimal_sep, $thousand_sep) . '</span>';
        $shop_currency_symbol = '<span>' . get_woocommerce_currency_symbol() . '</span>';
        $price_format = get_woocommerce_price_format();
//        $price_html = sprintf($price_format, $shop_currency_symbol, $product_price);
//
////        $min_qty=1;
////        $max_qty=1;
////        $step=1;
////        
////        if($product->product_type=="simple")
////        {
//        $step = apply_filters('woocommerce_quantity_input_step', '1', $product);
//        $min_qty = apply_filters('woocommerce_quantity_input_min', 1, $product);
//        $max_qty = apply_filters('woocommerce_quantity_input_max', $product->backorders_allowed() ? '' : $product->get_stock_quantity(), $product);
//        }
//        echo $price_html;
        if (isset($cart_btn) && $cart_btn !== "0") {
            GLOBAL $wp_query;
            $add_to_cart_label = __("ADD TO CART", "wpd");
            if (isset($wp_query->query_vars["edit"]))
                $add_to_cart_label = __("UPDATE CART ITEM", "wpd");
            ?>
            <div id="wpc-cart-box" class="">
                <div class="title" id="cart_title"><?php _e("CART", "wpd");?></div>
            <?php

            $wpc_metas = get_post_meta($this->root_item_id, 'wpc-metas', true);
            if (isset($wpc_metas['related-quantities']) &&!empty($wpc_metas['related-quantities'])&& $product->product_type == "variation") {
                $related_attributes = $wpc_metas['related-quantities'];
                $wpd_root_product=new WPD_Product($this->root_item_id);
                $usable_attributes = $wpd_root_product->extract_usable_attributes();
                $variation = wc_get_product($this->item_id);
                $selected_attributes = $variation->get_variation_attributes();
                $to_search = array();
                foreach ($usable_attributes as $attribute_name => $attribute_data) {
                    $attribute_key = $attribute_data["key"];
                    if (in_array($attribute_key, $related_attributes)) {
//					echo $attribute_data["label"].":<br>";
                        ?>
                        <div class="wpd-rp-attributes-container">
                            <?php
                            foreach ($attribute_data["values"] as $attribute_value) {
                                $to_search = $selected_attributes;
                                if (is_object($attribute_value)) {//Taxonomy
                                    $sanitized_value = $attribute_value->slug;
                                    $label = $attribute_value->name;
                                } else {
                                    $sanitized_value = sanitize_title($attribute_value);
                                    $label = $attribute_value;
                                }
                                $to_search[$attribute_key] = sanitize_title($sanitized_value);//$attribute_value;//sanitize_title($sanitized_value);
//                                                var_dump($to_search);
                                $variation_to_load = $this->get_variation_from_attributes($to_search);
                                //if(!$variation_to_load||$variation_to_load==$this->item_id)
                                if (!$variation_to_load)
                                    continue;
                                
                                $variation_to_load_ob = wc_get_product($variation_to_load);
                                
                                $wpd_variation=new WPD_Product($variation_to_load);
                                $purchase_properties=  $wpd_variation->get_purchase_properties();
                                
                                //Variation properties
                                $price=$variation_to_load_ob->get_price();
//                                var_dump($price);
                                $product_price = ' <span class="total_order">' . number_format($price*$purchase_properties["min_to_purchase"], $nb_decimals, $decimal_sep, $thousand_sep) . '</span>';
                                $price_html = sprintf($price_format, $shop_currency_symbol, $product_price);
                                
                                
                                $variation_to_load_attributes = $variation_to_load_ob->get_variation_attributes();
                                $attribute_str = "";

                                foreach ($variation_to_load_attributes as $variation_to_load_attribute_key => $variation_to_load_attribute) {
                                    if (in_array($variation_to_load_attribute_key, $related_attributes)) {
                                        if (!empty($attribute_str))
                                            $attribute_str.="+";
                                        $attribute_str.=$variation_to_load_attribute;
                                    }
                                }
                                ?>
                                <div class="wpc-qty-container" data-id="<?php echo $variation_to_load;?>">
                                    <label><?php echo $attribute_str;?></label>
                                    <input type="button" value="-" class="minus wpc-custom-right-quantity-input-set wpc-btn-effect">
                                    <input type="number" step="<?php echo $purchase_properties["step"]; ?>" value="<?php echo $purchase_properties["min_to_purchase"]; ?>" class="wpc-custom-right-quantity-input wpd-qty" min="<?php echo $purchase_properties["min"]; ?>" max="<?php echo $purchase_properties["max"]; ?>" dntmesecondfocus="true" uprice="<?php echo $price; ?>">
                                    <input type="button" value="+" class="plus wpc-custom-right-quantity-input-set wpc-btn-effect">

                                    <div class="total-price">
                                        <?php echo $price_html; ?>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                }
            }
            else
            {
                $purchase_properties=  $this->wpd_product->get_purchase_properties();
                $price=$product->get_price();
                $product_price = ' <span class="total_order">' . number_format($price*$purchase_properties["min_to_purchase"], $nb_decimals, $decimal_sep, $thousand_sep) . '</span>';                                
                $price_html = sprintf($price_format, $shop_currency_symbol, $product_price);
                
            ?>            
                <div class="wpc-qty-container" data-id="<?php echo $this->item_id;?>">
                    <input type="button" value="-" class="minus wpc-custom-right-quantity-input-set wpc-btn-effect">
                    <input type="number" step="<?php echo $purchase_properties["step"]; ?>" value="<?php echo $purchase_properties["min_to_purchase"]; ?>" class="wpc-custom-right-quantity-input wpd-qty" min="<?php echo $purchase_properties["min"]; ?>" max="<?php echo $purchase_properties["max"]; ?>" dntmesecondfocus="true" uprice="<?php echo $price; ?>">
                    <input type="button" value="+" class="plus wpc-custom-right-quantity-input-set wpc-btn-effect">

                    <div class="total-price">
                        <?php echo $price_html; ?>
                    </div>
                </div>
                
            <?php
            }
            do_action('wpd_cart_box', $this->wpd_product);
            ?>
                <button id="add-to-cart-btn" class="wpc-btn-effect" data-id="<?php echo $this->item_id ?>"><?php echo $add_to_cart_label; ?></button>
            </div>
            <?php
        }
    }

    private function register_scripts() {
        wp_enqueue_script('wpd-qtip', WPD_URL . 'public/js/jquery.qtip-1.0.0-rc3.min.js', array('jquery'), WPD_VERSION, false);
        wp_enqueue_script('wpd-number-js', WPD_URL . 'public/js/jquery.number.min.js', array('jquery'), WPD_VERSION, false);
        wp_enqueue_script('wpd-fabric-js', WPD_URL . 'public/js/fabric.all.min.js', array('jquery'), WPD_VERSION, false);
        wp_enqueue_script('wpd-editor-js', WPD_URL . 'public/js/editor.js', array('jquery'), WPD_VERSION, false);
        wp_enqueue_script('wpd-editor-text-controls', WPD_URL . 'public/js/editor.text.js', array('jquery'), WPD_VERSION, false);
        wp_enqueue_script('wpd-editor-toolbar-js', WPD_URL . 'public/js/editor.toolbar.js', array('jquery'), WPD_VERSION, false);
        wp_enqueue_script('wpd-editor-shapes-js', WPD_URL . 'public/js/editor.shapes.js', array('jquery'), WPD_VERSION, false);
        wp_enqueue_script('wpd-accordion-js', WPD_URL . 'public/js/SpryAssets/SpryAccordion.min.js', array('jquery'), WPD_VERSION, false);
        wp_enqueue_script('wpd-block-UI-js', WPD_URL . 'public/js/blockUI/jquery.blockUI.min.js', array('jquery'), WPD_VERSION, false);
        wp_enqueue_script('wpd-lazyload-js', WPD_URL . 'public/js/jquery.lazyload.min.js', array('jquery'), WPD_VERSION, false);
        wp_enqueue_script('wpd-editor-img-js', WPD_URL . 'public/js/editor.img.js', array('jquery', 'wpd-lazyload-js'), WPD_VERSION, false);
        
        self::register_upload_scripts();
    }

    public static function register_upload_scripts() {
        GLOBAL $wpc_options_settings;
        $options = $wpc_options_settings['wpc-upload-options'];
        $uploader = $options['wpc-uploader'];
        if ($uploader == "native") {
            wp_register_script('wpd-jquery-form-js', WPD_URL . 'public/js/jquery.form.min.js');
            wp_enqueue_script('wpd-jquery-form-js', array('jquery'), WPD_VERSION, false);
        } else {
            wp_register_script('wpd-widget', WPD_URL . 'public/js/upload/js/jquery.ui.widget.min.js');
            wp_enqueue_script('wpd-widget', array('jquery'), WPD_VERSION, false);

            wp_register_script('wpd-fileupload', WPD_URL . 'public/js/upload/js/jquery.fileupload.min.js');
            wp_enqueue_script('wpd-fileupload', array('jquery'), WPD_VERSION, false);

            wp_register_script('wpd-iframe-transport', WPD_URL . 'public/js/upload/js/jquery.iframe-transport.min.js');
            wp_enqueue_script('wpd-iframe-transport', array('jquery'), WPD_VERSION, false);

            wp_register_script('wpd-knob', WPD_URL . 'public/js/upload/js/jquery.knob.min.js');
            wp_enqueue_script('wpd-knob', array('jquery'), WPD_VERSION, false);
        }
    }

    private function register_styles() {
        wp_enqueue_style("wpd-SpryAccordion-css", WPD_URL . 'public/js/SpryAssets/SpryAccordion.min.css', array(), WPD_VERSION, 'all');
        wp_enqueue_style("wpd-editor", WPD_URL . 'public/css/editor.css', array(), WPD_VERSION, 'all');
        wp_enqueue_style("wpd-fancyselect-css", WPD_URL . 'public/css/fancySelect.min.css', array(), WPD_VERSION, 'all');
        $this->register_fonts();
    }

    private function register_fonts() {
        $fonts = get_option("wpc-fonts");
        if (empty($fonts)) {
            $fonts = $this->get_default_fonts();
        }

        foreach ($fonts as $font) {
            $font_label = $font[0];
            $font_url = str_replace('http://', '//', $font[1]);
            if ($font_url) {
                $handler = sanitize_title($font_label) . "-css";
                wp_register_style($handler, $font_url, array(), false, 'all');
                wp_enqueue_style($handler);
            }
        }
    }

    /**
     * Return the default fonts list
     * @return array
     */
    public function get_default_fonts() {
        $default = array(
            array("Shadows Into Light", "http://fonts.googleapis.com/css?family=Shadows+Into+Light"),
            array("Droid Sans", "http://fonts.googleapis.com/css?family=Droid+Sans:400,700"),
            array("Abril Fatface", "http://fonts.googleapis.com/css?family=Abril+Fatface"),
            array("Arvo", "http://fonts.googleapis.com/css?family=Arvo:400,700,400italic,700italic"),
            array("Lato", "http://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic"),
            array("Just Another Hand", "http://fonts.googleapis.com/css?family=Just+Another+Hand")
        );

        return $default;
    }

    /**
     * Builds a select dropdpown
     * @param type $name Name
     * @param type $id ID
     * @param type $class Class
     * @param type $options Options
     * @param type $selected Selected value
     * @param type $multiple Can select multiple values
     * @return string HTML code
     */
    private function get_html_select($name, $id, $class, $options, $selected = '', $multiple = false) {
        ob_start();
        ?>
        <select name="<?php echo $name; ?>" <?php echo ($id) ? "id=\"$id\"" : ""; ?> <?php echo ($class) ? "class=\"$class\"" : ""; ?> <?php echo ($multiple) ? "multiple" : ""; ?> >
            <?php
            if (is_array($options) && !empty($options)) {
                foreach ($options as $name => $label) {
                    if (!$multiple && $name == $selected) {
                        ?> <option value="<?php echo $name ?>"  selected="selected" > <?php echo $label; ?></option> <?php
                    } else if ($multiple && in_array($name, $selected)) {
                        ?> <option value="<?php echo $name ?>"  selected="selected" > <?php echo $label; ?></option> <?php
                    } else {
                        ?> <option value="<?php echo $name ?>"> <?php echo $label; ?></option> <?php
                    }
                }
            }
            ?>
        </select>
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

}
