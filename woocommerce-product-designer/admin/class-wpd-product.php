<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class-wpd-product
 *
 * @author HL
 */
class WPD_Product {

    public $variation_id;
    public $root_product_id;
    public $product;
    public $settings;
    public $variation_settings;

    public function __construct($id) {
        if ($id) {
            $this->root_product_id = $this->get_parent($id);
            //If it's a variable product
            if ($id != $this->root_product_id)
                $this->variation_id = $id;
            else
                $this->variation_id = $this->root_product_id;
            
            $this->product = wc_get_product($id);
            $this->settings= get_post_meta($this->root_product_id, "wpc-metas", true);
            $this->variation_settings=  get_proper_value($this->settings, $this->variation_id, array());
        }
    }

    function wpc_register_product_metabox() {
        add_meta_box('customizable-product', __('Customizable Product'), array($this, 'add_customizable_meta_box'), 'product', 'side', 'default');
    }

    function add_customizable_meta_box($product) {
        $wpc_metas = get_post_meta($product->ID, 'wpc-metas', true);
        $templates_page = get_proper_value($wpc_metas, 'templates-page', "");
        $selected_options_form = get_proper_value($wpc_metas, 'ninja-form-options', "");
        $is_checked = $this->get_checkbox_value($wpc_metas, 'is-customizable', '');

        echo "<label for='is-customizable'>";
        echo "<input type='checkbox' name='wpc-metas[is-customizable]' id='is-customizable' value='1' $is_checked />Customizable product</label><br>";
        $is_checked = $this->get_checkbox_value($wpc_metas, 'can-design-from-blank', '');

        echo "<label for='can-design-from-blank'>";
        echo "<input type='checkbox' name='wpc-metas[can-design-from-blank]' id='can-design-from-blank' value='1' $is_checked />The clients can design from blank</label><br>";
        $is_checked = $this->get_checkbox_value($wpc_metas, 'can-upload-custom-design', '');

        echo "<label for='can-upload-custom-design'>";
        echo "<input type='checkbox' name='wpc-metas[can-upload-custom-design]' id='can-upload-custom-design' value='1' $is_checked />The clients can upload their designs</label><br>";
        ?>
        <div>
            <select name="wpc-metas[templates-page]" class="mg-top-10"> 
                <option value="">
                    <?php echo _e("No templates page needed", "wpd"); ?></option>
                <?php
                $args = array("post_status" => "publish");
                $pages = get_pages($args);
                foreach ($pages as $page) {
                    if ($templates_page == $page->ID)
                        $option = '<option value="' . $page->ID . '" selected>';
                    else
                        $option = '<option value="' . $page->ID . '">';
                    $option .= $page->post_title;
                    $option .= '</option>';
                    echo $option;
                }
                ?>
            </select>
        </div>
        <?php
        if (function_exists('ninja_forms_get_all_forms')) {
            $forms = ninja_forms_get_all_forms();
            ?>
            <div>
                <h4><?php echo _e("Customized product options form", "wpd"); ?></h4>
                <select name="wpc-metas[ninja-form-options]" class="mg-top-10"> 
                    <option value="">
                        <?php echo _e("No option form needed", "wpd"); ?></option>
                    <?php
                    foreach ($forms as $form) {
                        if ($selected_options_form == $form['id'])
                            $option = '<option value="' . $form['id'] . '" selected>';
                        else
                            $option = '<option value="' . $form['id'] . '">';
                        $option .= $form['data']['form_title'];
                        $option .= '</option>';
                        echo $option;
                    }
                    ?>
                </select>
            </div>
            <?php
        }
    }

    private function get_checkbox_value($values, $search_key, $default_value) {
        if (get_proper_value($values, $search_key, $default_value) == 1)
            $is_checked = "checked='checked'";
        else
            $is_checked = "";
        return $is_checked;
    }

    function get_customizable_product_errors() {
        $post_type = get_post_type();
        if ($post_type == "product") {
            $product_id = get_the_ID();
            $wpd_product = new WPD_Product($product_id);
            $wpc_metas = get_post_meta($product_id, 'wpc-metas', true);
            if (isset($wpc_metas['is-customizable']) && !empty($wpc_metas['is-customizable'])) {
                $parts = get_option("wpc-parts");
                if (empty($parts)) {
                    $wpc_metas['is-customizable'] = "";
                    update_post_meta($product_id, 'wpc-metas', $wpc_metas);
                    ?>
                    <div class="error">
                        <p><?php _e('Error: empty product parts list. At least one part is required to create a customizable product.', 'wpd'); ?></p>
                    </div>
                    <?php
                } else if (!$wpd_product->has_part()) {
                    $wpc_metas['is-customizable'] = "";
                    update_post_meta($product_id, 'wpc-metas', $wpc_metas);
                    ?>
                    <div class="error">
                        <p><?php _e('Error: No active part defined for this product. A customizable product should have at least one part defined.', 'wpd'); ?></p>
                    </div>
                    <?php
                }
            }
        }
    }

    /**
     * Saves the product custom data
     * @param type $product_id Product ID
     */
//    function save_customizable_meta($product_id) {
//        if (isset($_POST['wpc-metas']))
//        {
//            update_post_meta($product_id, 'wpc-metas', $_POST['wpc-metas']);
//        }
//    }

    /**
     * Adds new tabs in the product page
     */
    function get_product_tab_label() {
        ?>
        <li class="wpc_canvas_tab show_if_simple"><a href="#wpc_canvas_tab_data"><?php _e('Canvas', 'wpd'); ?></a></li>
        <li class="wpc_bbox_tab show_if_simple"><a href="#wpc_bbox_tab_data"><?php _e('Bounding box', 'wpd'); ?></a></li>
        <li class="wpc_parts_tab show_if_simple"><a href="#wpc_parts_tab_data"><?php _e('Product parts', 'wpd'); ?></a></li>
        <li class="wpc_additional_price_tab"><a href="#wpc_additional_price_tab_data"><?php _e('Pricing rules', 'wpd'); ?></a></li>
        <li class="wpc_output_setting_tab show_if_simple"><a href="#wpc_output_setting_tab_data"><?php _e('Output settings', 'wpd'); ?></a></li>
        <li class="wpc_related_products_tab show_if_variable"><a href="#wpc_related_products_tab_data"><?php _e('Related Products / Quantities', 'wpd'); ?></a></li>
        <?php
    }

    /**
     * Adds the Custom column to the default products list to help identify which ones are custom
     * @param array $defaults Default columns
     * @return array
     */
    function get_product_columns($defaults) {
        $defaults['is_customizable'] = __('Custom', 'wpd');
        return $defaults;
    }

    /**
     * Sets the Custom column value on the products list to help identify which ones are custom
     * @param type $column_name Column name
     * @param type $id Product ID
     */
    function get_products_columns_values($column_name, $id) {
        if ($column_name === 'is_customizable') {
            $wpc_metas = get_post_meta($id, 'wpc-metas', true);
            $is_customizable = get_proper_value($wpc_metas, 'is-customizable', "");
            if (!empty($is_customizable))
                _e("Yes", "wpd");
            else
                _e("No", "wpd");
        }
    }

    private function get_rule_tpl($params, $with_price = false, $default_param = false, $default_operator = "<", $default_value = "", $default_price = "", $default_scope = "per_item", $count = 1) {
        ob_start();
        $operators = array("<" => __("is less than", "wpd"),
            "<=" => __("is less or equal to", "wpd"),
            "==" => __("equals", "wpd"),
            ">" => __("more than", "wpd"),
            ">=" => __("more or equal to", "wpd"));
        $scopes = array(
            "item" => __("Per item", "wpd"),
            "additional-items" => __("Per additional item", "wpd"),
            "design" => __("On whole design", "wpd")
        );
        ?>
        <tr data-id="rule_{rule-group}">
            <td class="param">
                <select id="wpc-group_{rule-group}_rule_{rule-index}_param" class="select wpc-pricing-group-param" name="wpc-metas[pricing-rules][group_{rule-group}][rules][rule_{rule-index}][param]">
                    <?php
                    foreach ($params as $param_key => $param_val) {
                        if ($param_key == $default_param) {
                            ?><option value='<?php echo $param_key; ?>' selected="selected"><?php echo $param_val; ?></option><?php
                        } else {
                            ?><option value='<?php echo $param_key; ?>'><?php echo $param_val; ?></option><?php
                        }
                    }
                    ?>
                </select>
            </td>
            <td class="operator">
                <select id="wpc-pricing-group_{rule-group}_rule_{rule-index}_operator" class="select" name="wpc-metas[pricing-rules][group_{rule-group}][rules][rule_{rule-index}][operator]">
                    <?php
                    foreach ($operators as $operator_key => $operator_val) {
                        if ($operator_key == $default_operator) {
                            ?><option value='<?php echo $operator_key; ?>' selected="selected"><?php echo $operator_val; ?></option><?php
                        } else {
                            ?><option value='<?php echo $operator_key; ?>'><?php echo $operator_val; ?></option><?php
                        }
                    }
                    ?>
                </select>
            </td>
            <td class="value">
                <input type="text" name="wpc-metas[pricing-rules][group_{rule-group}][rules][rule_{rule-index}][value]" value="<?php echo $default_value; ?>" placeholder="number">
            </td>
            <?php
            if ($with_price) {
                ?>
                <td class="a_price" rowspan="<?php echo $count; ?>">
                    <input type="number" name="wpc-metas[pricing-rules][group_{rule-group}][a_price]" value="<?php echo $default_price; ?>" placeholder="price">
                    <select id="wpc-pricing-group_{rule-group}_rule_{rule-index}_scope" class="select" name="wpc-metas[pricing-rules][group_{rule-group}][scope]">
                        <?php
                        foreach ($scopes as $scope_key => $scope_val) {
                            if ($scope_key == $default_scope) {
                                ?><option value='<?php echo $scope_key; ?>' selected="selected"><?php echo $scope_val; ?></option><?php
                            } else {
                                ?><option value='<?php echo $scope_key; ?>'><?php echo $scope_val; ?></option><?php
                            }
                        }
                        ?>
                    </select>
                </td>
                <?php
            }
            ?>
            <td class="add">
                <a class="wpc-add-rule button" data-group='{rule-group}'><?php echo __("and", "wpd"); ?></a>
            </td>
            <td class="remove">
                <a class="wpc-remove-rule acf-button-remove"></a>
            </td>
        </tr>
        <?php
        $rule_tpl = ob_get_contents();
        ob_end_clean();
        return $rule_tpl;
    }

    private function get_bounding_box_settings_tab() {
        $begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpd-bbox-settings',
//                        'table' => 'options',
        );

        $box_x = array(
            'title' => __('X', 'wpd'),
            'name' => 'wpc-metas[bounding_box][x]',
            'type' => 'number',
            'desc' => __('in pixels. Box X coordinate on the canvas.', 'wpd'),
        );
        $box_y = array(
            'title' => __('Y', 'wpd'),
            'name' => 'wpc-metas[bounding_box][y]',
            'type' => 'number',
            'desc' => __('in pixels. Box Y coordinate on the canvas.', 'wpd'),
        );
        $width = array(
            'title' => __('Width', 'wpd'),
            'name' => 'wpc-metas[bounding_box][width]',
            'type' => 'number',
            'desc' => __('Box width on the canvas.', 'wpd'),
        );
        $height = array(
            'title' => __('Height', 'wpd'),
            'name' => 'wpc-metas[bounding_box][height]',
            'type' => 'number',
            'desc' => __('Box height on the canvas.', 'wpd'),
        );
        $radius_rec = array(
            'title' => __('Radius (Rounded rect)', 'wpd'),
            'name' => 'wpc-metas[bounding_box][r_radius]',
            'type' => 'number',
            'default' => 0,
            'desc' => __('Box radius (used to create rounded rectangles box).', 'wpd'),
        );
        $radius_circ = array(
            'title' => __('Radius (Circle)', 'wpd'),
            'name' => 'wpc-metas[bounding_box][radius]',
            'type' => 'text',
            'default' => 0,
            'desc' => __('Box radius. Used if the box shape is a circle', 'wpd'),
        );
        $type = array(
            'title' => __('Box shape', 'wpd'),
            'name' => 'wpc-metas[bounding_box][type]',
            'type' => 'select',
            'options' => array(
                "rect" => __("Rectangle", "wpd"),
                "arc" => __("Circle", "wpd")
            ),
            'desc' => __('Shape of the bounding box', 'wpd'),
        );
        $border_color = array(
            'title' => __('Border color', 'wpd'),
            'name' => 'wpc-metas[bounding_box][border_color]',
            'type' => 'text',
            'desc' => __('Box border color', 'wpd'),
        );

        $end = array('type' => 'sectionend');
        $settings = apply_filters("wpd_bbox_settings", array(
            $begin,
            $box_x,
            $box_y,
            $width,
            $height,
            $radius_rec,
            $radius_circ,
            $type,
            $border_color,
            $end
        ));
        echo o_admin_fields($settings);
    }

    private function get_canvas_settings_tab($product_id) {
        GLOBAL $wpc_options_settings;
	$canvas_global_settings=get_proper_value($wpc_options_settings, 'wpc-general-options', array());

        $args = array("post_status" => "publish");
        $pages = get_pages($args);
        $pages_arr = array("" => __("No templates page needed", "wpd"));

        foreach ($pages as $page) {
            $pages_arr[$page->ID] = $page->post_title;
        }
        $begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpd-canvas-settings',
//                        'table' => 'options',
        );

        $templates_page = array(
            'title' => __('Templates page', 'wpd'),
            'name' => "wpc-metas[$product_id][templates-page]",
            'type' => 'select',
            'options' => $pages_arr,
        );
        $max_width = array(
            'title' => __('Canvas max width', 'wpd'),
            'name' => "wpc-metas[$product_id][canvas-w]",
            'type' => 'number',
            'desc' => __('in pixels. Canvas max width. Leave this field empty to use the value defined in the global settings.', 'wpd'),
            'default' => get_proper_value($canvas_global_settings, 'canvas-w')
        );
        $max_height = array(
            'title' => __('Canvas max height', 'wpd'),
            'name' => "wpc-metas[$product_id][canvas-h]",
            'type' => 'number',
            'desc' => __('in pixels. Canvas max width. Leave this field empty to use the value defined in the global settings.', 'wpd'),
            'default' => get_proper_value($canvas_global_settings, 'canvas-h')
        );
        $watermark = array(
            'title' => __('Watermark', 'wpd'),
            'name' => "wpc-metas[$product_id][watermark]",
            'type' => 'image',
            'desc' => 'Image which will be applied on top of all previews to prevent the copy of the designs from the website.'
        );


        $end = array('type' => 'sectionend');
        $settings = apply_filters("wpd_canvas_settings", array(
            $begin,
            $templates_page,
            $max_width,
            $max_height,
            $watermark,
            $end
        ), $product_id);
        echo o_admin_fields($settings);
    }

    private function get_parts_settings_tab($product_id) {


        $parts = get_option("wpc-parts");
        if (is_array($parts)) {
            foreach ($parts as $part) {
                $part_key = sanitize_title($part);
                $begin = array(
                    'type' => 'sectionbegin',
                    'id' => uniqid('wpd-parts-settings'),
                        //                        'table' => 'options',
                );

                $part_enabled = array(
                    'title' => $part,
                    'name' => "wpc-metas[$product_id][parts][$part_key][enabled]",
                    'type' => 'checkbox',
                    'value' => 1
//                    'desc' => __('Enable / Disable this part for this product'),
                );

                $bg_inc = array(
                    'title' => __('Background (included)', 'wpd'),
                    'name' => "wpc-metas[$product_id][parts][$part_key][bg-inc]",
                    'type' => 'image',
                    'desc' => __('Canvas background image included in the output.', 'wpd'),
                );
                $bg_not_inc = array(
                    'title' => __('Background (not included)', 'wpd'),
                    'name' => "wpc-metas[$product_id][parts][$part_key][bg-not-inc]",
                    'type' => 'image',
                    'desc' => __('Canvas background image not included in the output.', 'wpd'),
                );
                $overlay = array(
                    'title' => __('Image', 'wpd'),
                    'name' => "wpc-metas[$product_id][parts][$part_key][ov][img]",
                    'type' => 'image',
                );
                $overlay_inc = array(
                    'title' => __('Included in the output', 'wpd'),
                    'name' => "wpc-metas[$product_id][parts][$part_key][ov][inc]",
                    'type' => 'checkbox',
                    'value' => 1,
                );

                $overlay_group = array(
                    'title' => __('Overlay', 'wpd'),
                    'type' => 'groupedfields',
                    'fields' => array($overlay, $overlay_inc),
                    'desc' => __('Image which will be on top of everything  on the canvas.', 'wpd'),
                );

                $end = array('type' => 'sectionend');
                $settings = apply_filters("wpd_parts_settings", array(
                    $begin,
                    $part_enabled,
                    $bg_inc,
                    $bg_not_inc,
                    $overlay_group,
                    $end
                ));
                echo o_admin_fields($settings);
            }
        }

//                            $parts = get_option("wpc-parts");
//                            if (is_array($parts)) {
//                                foreach ($parts as $part) {
//                                    $part_key = sanitize_title($part);
//                                    $selector = "wpc_" . $part_key . "_" . $variation_id;
//                                    $canvas_bg_selector = "wpc_bg_" . $part_key . "_" . $variation_id;
//                                    $canvas_ov_selector = "wpc_ov_" . $part_key . "_" . $variation_id;
    }

    function get_product_tab_data() {
        $product_id = get_the_ID();
        $wpc_metas = get_post_meta($product_id, 'wpc-metas', true);
        ?>
        <div id="wpc_canvas_tab_data" class="panel woocommerce_options_panel">
            <?php
            $this->get_canvas_settings_tab($product_id);
            ?>
        </div>
        <div id="wpc_bbox_tab_data" class="panel woocommerce_options_panel">
            <?php
            $this->get_bounding_box_settings_tab();
            ?>
        </div>
        <div id="wpc_parts_tab_data" class="panel woocommerce_options_panel">
            <?php
            //$this->get_product_tab_data_content("wpc_parts_tab_data");
            //_e("Loading...", "wpd");
            $this->get_parts_settings_tab($product_id);
            ?>
        </div>
        <div id="wpc_additional_price_tab_data" class="panel woocommerce_options_panel wpc-sh-triggerable">
            <?php $this->get_rules_tab($wpc_metas); ?>

        </div>
        <div id="wpc_output_setting_tab_data" class="panel woocommerce_options_panel">
            <?php
            //$this->get_product_tab_data_content("wpc_output_setting_tab_data");
//            _e("Loading...", "wpd");
            $this->get_outputs_settings($product_id);
            ?>
        </div>
        <div id="wpc_related_products_tab_data" class="panel woocommerce_options_panel wpc-sh-triggerable">
            <div class="related-products-container">
                <?php
                $this->get_related_products_quantities_content($product_id, $wpc_metas);
                ?>
            </div>
        </div>
        <?php
    }

    function get_rules_tab($wpc_metas) {
        $params = array(
            "txt_nb_chars" => __("NB chars in text", "wpd"),
            "txt_nb_lines" => __("NB lines in text", "wpd"),
            "img_nb" => __("NB images", "wpd"),
            "path_nb" => __("NB vectors", "wpd")
        );
        $first_rule = $this->get_rule_tpl($params, true);
        $rule_tpl = $this->get_rule_tpl($params, false);
        ?>
        <div class='wpc-rules-table-container'>
            <textarea id='wpc-rule-tpl' style='display: none;'>
                <?php echo $rule_tpl; ?>
            </textarea>
            <textarea id='wpc-first-rule-tpl' style='display: none;'>
                <?php echo $first_rule; ?>
            </textarea>
            <?php
            $pricing_rules = array();
            if (isset($wpc_metas['pricing-rules']))
                $pricing_rules = $wpc_metas['pricing-rules'];
            if (is_array($pricing_rules) && !empty($pricing_rules)) {
                $rule_group = 0;
                foreach ($pricing_rules as $rules_group) {
                    $rule_index = 0;
                    $rules = $rules_group["rules"];
                    $a_price = $rules_group["a_price"];
                    $scope = $rules_group["scope"];
                    ?>
                    <table class="wpc-rules-table widefat">
                        <tbody>
                            <?php
                            foreach ($rules as $rule_arr) {
                                if ($rule_index == 0)
                                    $rule_html = $this->get_rule_tpl($params, true, $rule_arr["param"], $rule_arr["operator"], $rule_arr["value"], $a_price, $scope, count($rules));
                                else
                                    $rule_html = $this->get_rule_tpl($params, false, $rule_arr["param"], $rule_arr["operator"], $rule_arr["value"]);
                                $r1 = str_replace("{rule-group}", $rule_group, $rule_html);
                                $r2 = str_replace("{rule-index}", $rule_index, $r1);
                                echo $r2;
                                $rule_index++;
                            }
                            $rule_group++;
                            ?>
                        </tbody>
                    </table>
                    <?php
                }
            }
            else {
                ?>
                <table class="wpc-rules-table widefat">
                    <tbody>
                        <?php
                        $rule_group = 0;
                        $rule_index = 0;
                        $r1 = str_replace("{rule-group}", $rule_group, $first_rule);
                        $r2 = str_replace("{rule-index}", $rule_index, $r1);
                        echo $r2;
                        ?>
                    </tbody>
                </table>
                <?php
            }
            ?>

        </div>
        <a class="button wpc-add-group mg-bot-10i">Add rule group</a>
        <?php
    }

    function get_related_products_content_ajx() {
        $product_id = $_POST["product_id"];
        $wpc_metas = get_post_meta($product_id, 'wpc-metas', true);
        $this->get_related_products_quantities_content($product_id, $wpc_metas);
        die();
    }

    private function get_related_products_quantities_content($product_id, $wpc_metas) {
        $product = wc_get_product($product_id);
        $wpd_product = new WPD_Product($product_id);
        if ($product->product_type == "variable") {
            $usable_attributes = $wpd_product->extract_usable_attributes();

            $keys = array(
                "related-products" => __("Which attributes should be used for the related products box?", "wpd"),
                "related-quantities" => __("Which attributes should be used for the related quantities box?", "wpd"));

            foreach ($keys as $key => $desc) {
                echo $desc . "<br>";

                $related_products = array();
                if (isset($wpc_metas[$key]))
                    $related_products = $wpc_metas[$key];

                foreach ($usable_attributes as $attribute_name => $attribute_data) {
                    $variation_label = $attribute_data["label"];
                    $is_checked = (in_array($attribute_data["key"], $related_products)) ? "checked='checked'" : "";
                    ?>
                    <input type="checkbox" name="wpc-metas[<?php echo $key; ?>][]" value="<?php echo $attribute_data["key"]; ?>" <?php echo $is_checked; ?>>
                    <?php echo $variation_label; ?>
                    <br>
                    <?php
                }

                echo "<br>";
            }
        } else
            echo "This feature is only available for variable products.";
    }

    public function extract_usable_attributes() {
        $product = $this->product;
        $attributes = $product->get_attributes();
        $usable_attributes = array();
        foreach ($attributes as $attribute) {
            $sanitized_name = sanitize_title($attribute["name"]);
            if ($attribute["is_visible"] && $attribute["is_variation"]) {
                if ($attribute["is_taxonomy"]) {
                    $values = wc_get_product_terms($product->id, $attribute['name'], array('fields' => 'all'));
                    $taxonomy = get_taxonomy($attribute["name"]);
                    $key = "attribute_" . $sanitized_name;
                    $usable_attributes[$attribute["name"]] = array("key" => $key, "label" => $taxonomy->labels->name, "values" => $values); //$values;
//                        var_dump($values);
                } else {
                    $key = "attribute_" . $sanitized_name;
                    // Convert pipes to commas and display values
                    $values = array_map('trim', explode(WC_DELIMITER, $attribute['value']));
                    $usable_attributes[$attribute["name"]] = array("key" => $key, "label" => $attribute["name"], "values" => $values);
                }
            }
        }

        return $usable_attributes;
    }

    private function get_outputs_settings($product_id) {
        GLOBAL $wpc_options_settings;
	$output_global_settings=get_proper_value($wpc_options_settings, 'wpc-output-options', array());
	
        $options = array();
        $output_w = array(
            'title' => __('Output width (px)', 'wpd'),
            'name' => 'wpc-metas[' . $product_id . '][output-settings][wpc-min-output-width]',
            'type' => 'text',
            'default' => get_proper_value($output_global_settings, 'wpc-min-output-width')
        );
        $output_loop_delay = array(
            'title' => __('Output loop delay (milliseconds)', 'wpd'),
            'name' => 'wpc-metas[' . $product_id . '][output-settings][wpc-output-loop-delay]',
            'type' => 'text',
            'default' => get_proper_value($output_global_settings, 'wpc-output-loop-delay')
        );
        $zip_folder_name= array(
            'title' => __('Zip output folder name', 'wpd'),
            'name' => 'wpc-metas[' . $product_id . '][output-settings][zip-folder-name]',
            'type' => 'text',
        );
        $cmyk_conversion = array(
            'title' => __('CMYK conversion (Requires ImageMagick)', 'wpd'),
            'name' => 'wpc-metas[' . $product_id . '][output-settings][wpc-cmyk-conversion]',
            'type' => 'radio',
            'options' => array(
                'no' => __('No', 'wpd'),
                'yes' => __('Yes', 'wpd')
            ),
            'default' => get_proper_value($output_global_settings, 'wpc-cmyk-conversion')
//            'class' => 'chosen_select_nostd'
        );

        $pdf_format = array(
            'title' => __('PDF Format', 'wpd'),
            'name' => 'wpc-metas[' . $product_id . '][output-settings][pdf-format]',
            'type' => 'groupedselect',
            'data-id' => $product_id,
            'options' => array('ISO 216 A Series + 2 SIS 014711 extensions' => array('A0' => 'A0 (841x1189 mm ; 33.11x46.81 in)', 'A1' => 'A1 (594x841 mm ; 23.39x33.11 in)', 'A2' => 'A2 (420x594 mm ; 16.54x23.39 in)', 'A3' => 'A3 (297x420 mm ; 11.69x16.54 in)', 'A4' => 'A4 (210x297 mm ; 8.27x11.69 in)', 'A5' => 'A5 (148x210 mm ; 5.83x8.27 in)', 'A6' => 'A6 (105x148 mm ; 4.13x5.83 in)', 'A7' => 'A7 (74x105 mm ; 2.91x4.13 in)', 'A8' => 'A8 (52x74 mm ; 2.05x2.91 in)', 'A9' => 'A9 (37x52 mm ; 1.46x2.05 in)', 'A10' => 'A10 (26x37 mm ; 1.02x1.46 in)', 'A11' => 'A11 (18x26 mm ; 0.71x1.02 in)', 'A12' => 'A12 (13x18 mm ; 0.51x0.71 in)',), 'ISO 216 B Series + 2 SIS 014711 extensions' => array('B0' => 'B0 (1000x1414 mm ; 39.37x55.67 in)', 'B1' => 'B1 (707x1000 mm ; 27.83x39.37 in)', 'B2' => 'B2 (500x707 mm ; 19.69x27.83 in)', 'B3' => 'B3 (353x500 mm ; 13.90x19.69 in)', 'B4' => 'B4 (250x353 mm ; 9.84x13.90 in)', 'B5' => 'B5 (176x250 mm ; 6.93x9.84 in)', 'B6' => 'B6 (125x176 mm ; 4.92x6.93 in)', 'B7' => 'B7 (88x125 mm ; 3.46x4.92 in)', 'B8' => 'B8 (62x88 mm ; 2.44x3.46 in)', 'B9' => 'B9 (44x62 mm ; 1.73x2.44 in)', 'B10' => 'B10 (31x44 mm ; 1.22x1.73 in)', 'B11' => 'B11 (22x31 mm ; 0.87x1.22 in)', 'B12' => 'B12 (15x22 mm ; 0.59x0.87 in)',), 'ISO 216 C Series + 2 SIS 014711 extensions + 2 EXTENSION' => array('C0' => 'C0 (917x1297 mm ; 36.10x51.06 in)', 'C1' => 'C1 (648x917 mm ; 25.51x36.10 in)', 'C2' => 'C2 (458x648 mm ; 18.03x25.51 in)', 'C3' => 'C3 (324x458 mm ; 12.76x18.03 in)', 'C4' => 'C4 (229x324 mm ; 9.02x12.76 in)', 'C5' => 'C5 (162x229 mm ; 6.38x9.02 in)', 'C6' => 'C6 (114x162 mm ; 4.49x6.38 in)', 'C7' => 'C7 (81x114 mm ; 3.19x4.49 in)', 'C8' => 'C8 (57x81 mm ; 2.24x3.19 in)', 'C9' => 'C9 (40x57 mm ; 1.57x2.24 in)', 'C10' => 'C10 (28x40 mm ; 1.10x1.57 in)', 'C11' => 'C11 (20x28 mm ; 0.79x1.10 in)', 'C12' => 'C12 (14x20 mm ; 0.55x0.79 in)', 'C76' => 'C76 (81x162 mm ; 3.19x6.38 in)', 'DL' => 'DL (110x220 mm ; 4.33x8.66 in)',), 'SIS 014711 E Series' => array('E0' => 'E0 (879x1241 mm ; 34.61x48.86 in)', 'E1' => 'E1 (620x879 mm ; 24.41x34.61 in)', 'E2' => 'E2 (440x620 mm ; 17.32x24.41 in)', 'E3' => 'E3 (310x440 mm ; 12.20x17.32 in)', 'E4' => 'E4 (220x310 mm ; 8.66x12.20 in)', 'E5' => 'E5 (155x220 mm ; 6.10x8.66 in)', 'E6' => 'E6 (110x155 mm ; 4.33x6.10 in)', 'E7' => 'E7 (78x110 mm ; 3.07x4.33 in)', 'E8' => 'E8 (55x78 mm ; 2.17x3.07 in)', 'E9' => 'E9 (39x55 mm ; 1.54x2.17 in)', 'E10' => 'E10 (27x39 mm ; 1.06x1.54 in)', 'E11' => 'E11 (19x27 mm ; 0.75x1.06 in)', 'E12' => 'E12 (13x19 mm ; 0.51x0.75 in)',), 'SIS 014711 G Series' => array('G0' => 'G0 (958x1354 mm ; 37.72x53.31 in)', 'G1' => 'G1 (677x958 mm ; 26.65x37.72 in)', 'G2' => 'G2 (479x677 mm ; 18.86x26.65 in)', 'G3' => 'G3 (338x479 mm ; 13.31x18.86 in)', 'G4' => 'G4 (239x338 mm ; 9.41x13.31 in)', 'G5' => 'G5 (169x239 mm ; 6.65x9.41 in)', 'G6' => 'G6 (119x169 mm ; 4.69x6.65 in)', 'G7' => 'G7 (84x119 mm ; 3.31x4.69 in)', 'G8' => 'G8 (59x84 mm ; 2.32x3.31 in)', 'G9' => 'G9 (42x59 mm ; 1.65x2.32 in)', 'G10' => 'G10 (29x42 mm ; 1.14x1.65 in)', 'G11' => 'G11 (21x29 mm ; 0.83x1.14 in)', 'G12' => 'G12 (14x21 mm ; 0.55x0.83 in)',), 'ISO Press' => array('RA0' => 'RA0 (860x1220 mm ; 33.86x48.03 in)', 'RA1' => 'RA1 (610x860 mm ; 23.02x33.86 in)', 'RA2' => 'RA2 (430x610 mm ; 16.93x23.02 in)', 'RA3' => 'RA3 (305x430 mm ; 12.01x16.93 in)', 'RA4' => 'RA4 (215x305 mm ; 8.46x12.01 in)', 'SRA0' => 'SRA0 (900x1280 mm ; 35.43x50.39 in)', 'SRA1' => 'SRA1 (640x900 mm ; 25.20x35.43 in)', 'SRA2' => 'SRA2 (450x640 mm ; 17.72x25.20 in)', 'SRA3' => 'SRA3 (320x450 mm ; 12.60x17.72 in)', 'SRA4' => 'SRA4 (225x320 mm ; 8.86x12.60 in)',), 'German DIN 476' => array('4A0' => '4A0 (1682x2378 mm ; 66.22x93.62 in)', '2A0' => '2A0 (1189x1682 mm ; 46.81x66.22 in)',), 'Variations on the ISO Standard' => array('A2_EXTRA' => 'A2_EXTRA (445x619 mm ; 17.52x24.37 in)', 'A3+' => 'A3+ (329x483 mm ; 12.95x19.02 in)', 'A3_EXTRA' => 'A3_EXTRA (322x445 mm ; 12.68x17.52 in)', 'A3_SUPER' => 'A3_SUPER (305x508 mm ; 12.01x20.00 in)', 'SUPER_A3' => 'SUPER_A3 (305x487 mm ; 12.01x19.17 in)', 'A4_EXTRA' => 'A4_EXTRA (235x322 mm ; 9.25x12.68 in)', 'A4_SUPER' => 'A4_SUPER (229x322 mm ; 9.02x12.68 in)', 'SUPER_A4' => 'SUPER_A4 (227x356 mm ; 8.94x13.02 in)', 'A4_LONG' => 'A4_LONG (210x348 mm ; 8.27x13.70 in)', 'F4' => 'F4 (210x330 mm ; 8.27x12.99 in)', 'SO_B5_EXTRA' => 'SO_B5_EXTRA (202x276 mm ; 7.95x10.87 in)', 'A5_EXTRA' => 'A5_EXTRA (173x235 mm ; 6.81x9.25 in)',), 'ANSI Series' => array('ANSI_E' => 'ANSI_E (864x1118 mm ; 33.00x43.00 in)', 'ANSI_D' => 'ANSI_D (559x864 mm ; 22.00x33.00 in)', 'ANSI_C' => 'ANSI_C (432x559 mm ; 17.00x22.00 in)', 'ANSI_B' => 'ANSI_B (279x432 mm ; 11.00x17.00 in)', 'ANSI_A' => 'ANSI_A (216x279 mm ; 8.50x11.00 in)',), 'Traditional "Loose" North American Paper Sizes' => array('LEDGER, USLEDGER' => 'LEDGER, USLEDGER (432x279 mm ; 17.00x11.00 in)', 'TABLOID, USTABLOID, BIBLE, ORGANIZERK' => 'TABLOID, USTABLOID, BIBLE, ORGANIZERK (279x432 mm ; 11.00x17.00 in)', 'LETTER, USLETTER, ORGANIZERM' => 'LETTER, USLETTER, ORGANIZERM (216x279 mm ; 8.50x11.00 in)', 'LEGAL, USLEGAL' => 'LEGAL, USLEGAL (216x356 mm ; 8.50x13.00 in)', 'GLETTER, GOVERNMENTLETTER' => 'GLETTER, GOVERNMENTLETTER (203x267 mm ; 8.00x10.50 in)', 'JLEGAL, JUNIORLEGAL' => 'JLEGAL, JUNIORLEGAL (203x127 mm ; 8.00x5.00 in)',), 'Other North American Paper Sizes' => array('QUADDEMY' => 'QUADDEMY (889x1143 mm ; 35.00x45.00 in)', 'SUPER_B' => 'SUPER_B (330x483 mm ; 13.00x19.00 in)', 'QUARTO' => 'QUARTO (229x279 mm ; 9.00x11.00 in)', 'FOLIO, GOVERNMENTLEGAL' => 'FOLIO, GOVERNMENTLEGAL (216x330 mm ; 8.50x13.00 in)', 'EXECUTIVE, MONARCH' => 'EXECUTIVE, MONARCH (184x267 mm ; 7.25x10.50 in)', 'MEMO, STATEMENT, ORGANIZERL' => 'MEMO, STATEMENT, ORGANIZERL (140x216 mm ; 5.50x8.50 in)', 'FOOLSCAP' => 'FOOLSCAP (210x330 mm ; 8.27x13.00 in)', 'COMPACT' => 'COMPACT (108x171 mm ; 4.25x6.75 in)', 'ORGANIZERJ' => 'ORGANIZERJ (70x127 mm ; 2.75x5.00 in)',), 'Canadian standard CAN 2-9.60M' => array('P1' => 'P1 (560x860 mm ; 22.05x33.86 in)', 'P2' => 'P2 (430x560 mm ; 16.93x22.05 in)', 'P3' => 'P3 (280x430 mm ; 11.02x16.93 in)', 'P4' => 'P4 (215x280 mm ; 8.46x11.02 in)', 'P5' => 'P5 (140x215 mm ; 5.51x8.46 in)', 'P6' => 'P6 (107x140 mm ; 4.21x5.51 in)',), 'North American Architectural Sizes' => array('ARCH_E' => 'ARCH_E (914x1219 mm ; 36.00x48.00 in)', 'ARCH_E1' => 'ARCH_E1 (762x1067 mm ; 30.00x42.00 in)', 'ARCH_D' => 'ARCH_D (610x914 mm ; 23.00x36.00 in)', 'ARCH_C, BROADSHEET' => 'ARCH_C, BROADSHEET (457x610 mm ; 18.00x23.00 in)', 'ARCH_B' => 'ARCH_B (305x457 mm ; 12.00x18.00 in)', 'ARCH_A' => 'ARCH_A (229x305 mm ; 9.00x12.00 in)',), 'Announcement Envelopes' => array('ANNENV_A2' => 'ANNENV_A2 (111x146 mm ; 4.37x5.75 in)', 'ANNENV_A6' => 'ANNENV_A6 (121x165 mm ; 4.75x6.50 in)', 'ANNENV_A7' => 'ANNENV_A7 (133x184 mm ; 5.25x7.25 in)', 'ANNENV_A8' => 'ANNENV_A8 (140x206 mm ; 5.50x8.12 in)', 'ANNENV_A10' => 'ANNENV_A10 (159x244 mm ; 6.25x9.62 in)', 'ANNENV_SLIM' => 'ANNENV_SLIM (98x225 mm ; 3.87x8.87 in)',), 'Commercial Envelopes' => array('COMMENV_N6_1/4' => 'COMMENV_N6_1/4 (89x152 mm ; 3.50x6.00 in)', 'COMMENV_N6_3/4' => 'COMMENV_N6_3/4 (92x165 mm ; 3.62x6.50 in)', 'COMMENV_N8' => 'COMMENV_N8 (98x191 mm ; 3.87x7.50 in)', 'COMMENV_N9' => 'COMMENV_N9 (98x225 mm ; 3.87x8.87 in)', 'COMMENV_N10' => 'COMMENV_N10 (105x241 mm ; 4.12x9.50 in)', 'COMMENV_N11' => 'COMMENV_N11 (114x263 mm ; 4.50x10.37 in)', 'COMMENV_N12' => 'COMMENV_N12 (121x279 mm ; 4.75x11.00 in)', 'COMMENV_N14' => 'COMMENV_N14 (127x292 mm ; 5.00x11.50 in)',), 'Catalogue Envelopes' => array('CATENV_N1' => 'CATENV_N1 (152x229 mm ; 6.00x9.00 in)', 'CATENV_N1_3/4' => 'CATENV_N1_3/4 (165x241 mm ; 6.50x9.50 in)', 'CATENV_N2' => 'CATENV_N2 (165x254 mm ; 6.50x10.00 in)', 'CATENV_N3' => 'CATENV_N3 (178x254 mm ; 7.00x10.00 in)', 'CATENV_N6' => 'CATENV_N6 (191x267 mm ; 7.50x10.50 in)', 'CATENV_N7' => 'CATENV_N7 (203x279 mm ; 8.00x11.00 in)', 'CATENV_N8' => 'CATENV_N8 (210x286 mm ; 8.25x11.25 in)', 'CATENV_N9_1/2' => 'CATENV_N9_1/2 (216x267 mm ; 8.50x10.50 in)', 'CATENV_N9_3/4' => 'CATENV_N9_3/4 (222x286 mm ; 8.75x11.25 in)', 'CATENV_N10_1/2' => 'CATENV_N10_1/2 (229x305 mm ; 9.00x12.00 in)', 'CATENV_N12_1/2' => 'CATENV_N12_1/2 (241x318 mm ; 9.50x12.50 in)', 'CATENV_N13_1/2' => 'CATENV_N13_1/2 (254x330 mm ; 10.00x13.00 in)', 'CATENV_N14_1/4' => 'CATENV_N14_1/4 (286x311 mm ; 11.25x12.25 in)', 'CATENV_N14_1/2' => 'CATENV_N14_1/2 (292x368 mm ; 11.50x14.50 in)', 'Japanese' => 'Japanese (JIS P 0138-61) Standard B-Series', 'JIS_B0' => 'JIS_B0 (1030x1456 mm ; 40.55x57.32 in)', 'JIS_B1' => 'JIS_B1 (728x1030 mm ; 28.66x40.55 in)', 'JIS_B2' => 'JIS_B2 (515x728 mm ; 20.28x28.66 in)', 'JIS_B3' => 'JIS_B3 (364x515 mm ; 14.33x20.28 in)', 'JIS_B4' => 'JIS_B4 (257x364 mm ; 10.12x14.33 in)', 'JIS_B5' => 'JIS_B5 (182x257 mm ; 7.17x10.12 in)', 'JIS_B6' => 'JIS_B6 (128x182 mm ; 5.04x7.17 in)', 'JIS_B7' => 'JIS_B7 (91x128 mm ; 3.58x5.04 in)', 'JIS_B8' => 'JIS_B8 (64x91 mm ; 2.52x3.58 in)', 'JIS_B9' => 'JIS_B9 (45x64 mm ; 1.77x2.52 in)', 'JIS_B10' => 'JIS_B10 (32x45 mm ; 1.26x1.77 in)', 'JIS_B11' => 'JIS_B11 (22x32 mm ; 0.87x1.26 in)', 'JIS_B12' => 'JIS_B12 (16x22 mm ; 0.63x0.87 in)',), 'PA Series' => array('PA0' => 'PA0 (840x1120 mm ; 33.07x43.09 in)', 'PA1' => 'PA1 (560x840 mm ; 22.05x33.07 in)', 'PA2' => 'PA2 (420x560 mm ; 16.54x22.05 in)', 'PA3' => 'PA3 (280x420 mm ; 11.02x16.54 in)', 'PA4' => 'PA4 (210x280 mm ; 8.27x11.02 in)', 'PA5' => 'PA5 (140x210 mm ; 5.51x8.27 in)', 'PA6' => 'PA6 (105x140 mm ; 4.13x5.51 in)', 'PA7' => 'PA7 (70x105 mm ; 2.76x4.13 in)', 'PA8' => 'PA8 (52x70 mm ; 2.05x2.76 in)', 'PA9' => 'PA9 (35x52 mm ; 1.38x2.05 in)', 'PA10' => 'PA10 (26x35 mm ; 1.02x1.38 in)',), 'Standard Photographic Print Sizes' => array('PASSPORT_PHOTO' => 'PASSPORT_PHOTO (35x45 mm ; 1.38x1.77 in)', 'E' => 'E (82x120 mm ; 3.25x4.72 in)', '3R, L' => '3R, L (89x127 mm ; 3.50x5.00 in)', '4R, KG' => '4R, KG (102x152 mm ; 3.02x5.98 in)', '4D' => '4D (120x152 mm ; 4.72x5.98 in)', '5R, 2L' => '5R, 2L (127x178 mm ; 5.00x7.01 in)', '6R, 8P' => '6R, 8P (152x203 mm ; 5.98x7.99 in)', '8R, 6P' => '8R, 6P (203x254 mm ; 7.99x10.00 in)', 'S8R, 6PW' => 'S8R, 6PW (203x305 mm ; 7.99x12.01 in)', '10R, 4P' => '10R, 4P (254x305 mm ; 10.00x12.01 in)', 'S10R, 4PW' => 'S10R, 4PW (254x381 mm ; 10.00x15.00 in)', '11R' => '11R (279x356 mm ; 10.98x13.02 in)', 'S11R' => 'S11R (279x432 mm ; 10.98x17.01 in)', '12R' => '12R (305x381 mm ; 12.01x15.00 in)', 'S12R' => 'S12R (305x456 mm ; 12.01x17.95 in)',), 'Common Newspaper Sizes' => array('NEWSPAPER_BROADSHEET' => 'NEWSPAPER_BROADSHEET (750x600 mm ; 29.53x23.62 in)', 'NEWSPAPER_BERLINER' => 'NEWSPAPER_BERLINER (470x315 mm ; 18.50x12.40 in)', 'NEWSPAPER_COMPACT, NEWSPAPER_TABLOID' => 'NEWSPAPER_COMPACT, NEWSPAPER_TABLOID (430x280 mm ; 16.93x11.02 in)',), 'Business Cards' => array('CREDIT_CARD, BUSINESS_CARD, BUSINESS_CARD_ISO7810' => 'CREDIT_CARD, BUSINESS_CARD, BUSINESS_CARD_ISO7810 (54x86 mm ; 2.13x3.37 in)', 'BUSINESS_CARD_ISO216' => 'BUSINESS_CARD_ISO216 (52x74 mm ; 2.05x2.91 in)', 'BUSINESS_CARD_IT, BUSINESS_CARD_UK, BUSINESS_CARD_FR, BUSINESS_CARD_DE, BUSINESS_CARD_ES (55x85 mm ; 2.17x3.35 in)' => 'BUSINESS_CARD_IT, BUSINESS_CARD_UK, BUSINESS_CARD_FR, BUSINESS_CARD_DE, BUSINESS_CARD_ES (55x85 mm ; 2.17x3.35 in)', 'BUSINESS_CARD_US, BUSINESS_CARD_CA' => 'BUSINESS_CARD_US, BUSINESS_CARD_CA (51x89 mm ; 2.01x3.50 in)', 'BUSINESS_CARD_JP' => 'BUSINESS_CARD_JP (55x91 mm ; 2.17x3.58 in)', 'BUSINESS_CARD_HK' => 'BUSINESS_CARD_HK (54x90 mm ; 2.13x3.54 in)', 'BUSINESS_CARD_AU, BUSINESS_CARD_DK, BUSINESS_CARD_SE' => 'BUSINESS_CARD_AU, BUSINESS_CARD_DK, BUSINESS_CARD_SE (55x90 mm ; 2.17x3.54 in)', 'BUSINESS_CARD_RU, BUSINESS_CARD_CZ, BUSINESS_CARD_FI, BUSINESS_CARD_HU, BUSINESS_CARD_IL' => 'BUSINESS_CARD_RU, BUSINESS_CARD_CZ, BUSINESS_CARD_FI, BUSINESS_CARD_HU, BUSINESS_CARD_IL (50x90 mm ; 1.97x3.54 in)',), 'Billboards' => array('4SHEET' => '4SHEET (1016x1524 mm ; 40.00x60.00 in)', '6SHEET' => '6SHEET (1200x1800 mm ; 47.24x70.87 in)', '12SHEET' => '12SHEET (3048x1524 mm ; 120.00x60.00 in)', '16SHEET' => '16SHEET (2032x3048 mm ; 80.00x120.00 in)', '32SHEET' => '32SHEET (4064x3048 mm ; 160.00x120.00 in)', '48SHEET' => '48SHEET (6096x3048 mm ; 240.00x120.00 in)', '64SHEET' => '64SHEET (8128x3048 mm ; 320.00x120.00 in)', '96SHEET' => '96SHEET (12192x3048 mm ; 480.00x120.00 in)', 'Old Imperial English' => 'Old Imperial English (some are still used in USA)', 'EN_EMPEROR' => 'EN_EMPEROR (1219x1829 mm ; 48.00x72.00 in)', 'EN_ANTIQUARIAN' => 'EN_ANTIQUARIAN (787x1346 mm ; 31.00x53.00 in)', 'EN_GRAND_EAGLE' => 'EN_GRAND_EAGLE (730x1067 mm ; 28.75x42.00 in)', 'EN_DOUBLE_ELEPHANT' => 'EN_DOUBLE_ELEPHANT (679x1016 mm ; 26.75x40.00 in)', 'EN_ATLAS' => 'EN_ATLAS (660x864 mm ; 26.00x33.00 in)', 'EN_COLOMBIER' => 'EN_COLOMBIER (597x876 mm ; 23.50x34.50 in)', 'EN_ELEPHANT' => 'EN_ELEPHANT (584x711 mm ; 23.00x28.00 in)', 'EN_DOUBLE_DEMY' => 'EN_DOUBLE_DEMY (572x902 mm ; 22.50x35.50 in)', 'EN_IMPERIAL' => 'EN_IMPERIAL (559x762 mm ; 22.00x30.00 in)', 'EN_PRINCESS' => 'EN_PRINCESS (546x711 mm ; 21.50x28.00 in)', 'EN_CARTRIDGE' => 'EN_CARTRIDGE (533x660 mm ; 21.00x26.00 in)', 'EN_DOUBLE_LARGE_POST' => 'EN_DOUBLE_LARGE_POST (533x838 mm ; 21.00x33.00 in)', 'EN_ROYAL' => 'EN_ROYAL (508x635 mm ; 20.00x25.00 in)', 'EN_SHEET, EN_HALF_POST' => 'EN_SHEET, EN_HALF_POST (495x597 mm ; 19.50x23.50 in)', 'EN_SUPER_ROYAL' => 'EN_SUPER_ROYAL (483x686 mm ; 19.00x27.00 in)', 'EN_DOUBLE_POST' => 'EN_DOUBLE_POST (483x775 mm ; 19.00x30.50 in)', 'EN_MEDIUM' => 'EN_MEDIUM (445x584 mm ; 17.50x23.00 in)', 'EN_DEMY' => 'EN_DEMY (445x572 mm ; 17.50x22.50 in)', 'EN_LARGE_POST' => 'EN_LARGE_POST (419x533 mm ; 16.50x21.00 in)', 'EN_COPY_DRAUGHT' => 'EN_COPY_DRAUGHT (406x508 mm ; 16.00x20.00 in)', 'EN_POST' => 'EN_POST (394x489 mm ; 15.50x19.25 in)', 'EN_CROWN' => 'EN_CROWN (381x508 mm ; 15.00x20.00 in)', 'EN_PINCHED_POST' => 'EN_PINCHED_POST (375x470 mm ; 14.75x18.50 in)', 'EN_BRIEF' => 'EN_BRIEF (343x406 mm ; 13.50x16.00 in)', 'EN_FOOLSCAP' => 'EN_FOOLSCAP (343x432 mm ; 13.50x17.00 in)', 'EN_SMALL_FOOLSCAP' => 'EN_SMALL_FOOLSCAP (337x419 mm ; 13.25x16.50 in)', 'EN_POTT' => 'EN_POTT (318x381 mm ; 12.50x15.00 in)',), 'Old Imperial Belgian' => array('BE_GRAND_AIGLE' => 'BE_GRAND_AIGLE (700x1040 mm ; 27.56x40.94 in)', 'BE_COLOMBIER' => 'BE_COLOMBIER (620x850 mm ; 24.41x33.46 in)', 'BE_DOUBLE_CARRE' => 'BE_DOUBLE_CARRE (620x920 mm ; 24.41x36.22 in)', 'BE_ELEPHANT' => 'BE_ELEPHANT (616x770 mm ; 24.25x30.31 in)', 'BE_PETIT_AIGLE' => 'BE_PETIT_AIGLE (600x840 mm ; 23.62x33.07 in)', 'BE_GRAND_JESUS' => 'BE_GRAND_JESUS (550x730 mm ; 21.65x28.74 in)', 'BE_JESUS' => 'BE_JESUS (540x730 mm ; 21.26x28.74 in)', 'BE_RAISIN' => 'BE_RAISIN (500x650 mm ; 19.69x25.59 in)', 'BE_GRAND_MEDIAN' => 'BE_GRAND_MEDIAN (460x605 mm ; 18.11x23.82 in)', 'BE_DOUBLE_POSTE' => 'BE_DOUBLE_POSTE (435x565 mm ; 17.13x22.24 in)', 'BE_COQUILLE' => 'BE_COQUILLE (430x560 mm ; 16.93x22.05 in)', 'BE_PETIT_MEDIAN' => 'BE_PETIT_MEDIAN (415x530 mm ; 16.34x20.87 in)', 'BE_RUCHE' => 'BE_RUCHE (360x460 mm ; 14.17x18.11 in)', 'BE_PROPATRIA' => 'BE_PROPATRIA (345x430 mm ; 13.58x16.93 in)', 'BE_LYS' => 'BE_LYS (317x397 mm ; 12.48x15.63 in)', 'BE_POT' => 'BE_POT (307x384 mm ; 12.09x15.12 in)', 'BE_ROSETTE' => 'BE_ROSETTE (270x347 mm ; 10.63x13.66 in)',), 'Old Imperial French' => array('FR_UNIVERS' => 'FR_UNIVERS (1000x1300 mm ; 39.37x51.18 in)', 'FR_DOUBLE_COLOMBIER' => 'FR_DOUBLE_COLOMBIER (900x1260 mm ; 35.43x49.61 in)', 'FR_GRANDE_MONDE' => 'FR_GRANDE_MONDE (900x1260 mm ; 35.43x49.61 in)', 'FR_DOUBLE_SOLEIL' => 'FR_DOUBLE_SOLEIL (800x1200 mm ; 31.50x47.24 in)', 'FR_DOUBLE_JESUS' => 'FR_DOUBLE_JESUS (760x1120 mm ; 29.92x43.09 in)', 'FR_GRAND_AIGLE' => 'FR_GRAND_AIGLE (750x1060 mm ; 29.53x41.73 in)', 'FR_PETIT_AIGLE' => 'FR_PETIT_AIGLE (700x940 mm ; 27.56x37.01 in)', 'FR_DOUBLE_RAISIN' => 'FR_DOUBLE_RAISIN (650x1000 mm ; 25.59x39.37 in)', 'FR_JOURNAL' => 'FR_JOURNAL (650x940 mm ; 25.59x37.01 in)', 'FR_COLOMBIER_AFFICHE' => 'FR_COLOMBIER_AFFICHE (630x900 mm ; 24.80x35.43 in)', 'FR_DOUBLE_CAVALIER' => 'FR_DOUBLE_CAVALIER (620x920 mm ; 24.41x36.22 in)', 'FR_CLOCHE' => 'FR_CLOCHE (600x800 mm ; 23.62x31.50 in)', 'FR_SOLEIL' => 'FR_SOLEIL (600x800 mm ; 23.62x31.50 in)', 'FR_DOUBLE_CARRE' => 'FR_DOUBLE_CARRE (560x900 mm ; 22.05x35.43 in)', 'FR_DOUBLE_COQUILLE' => 'FR_DOUBLE_COQUILLE (560x880 mm ; 22.05x34.65 in)', 'FR_JESUS' => 'FR_JESUS (560x760 mm ; 22.05x29.92 in)', 'FR_RAISIN' => 'FR_RAISIN (500x650 mm ; 19.69x25.59 in)', 'FR_CAVALIER' => 'FR_CAVALIER (460x620 mm ; 18.11x24.41 in)', 'FR_DOUBLE_COURONNE' => 'FR_DOUBLE_COURONNE (460x720 mm ; 18.11x28.35 in)', 'FR_CARRE' => 'FR_CARRE (450x560 mm ; 17.72x22.05 in)', 'FR_COQUILLE' => 'FR_COQUILLE (440x560 mm ; 17.32x22.05 in)', 'FR_DOUBLE_TELLIERE' => 'FR_DOUBLE_TELLIERE (440x680 mm ; 17.32x26.77 in)', 'FR_DOUBLE_CLOCHE' => 'FR_DOUBLE_CLOCHE (400x600 mm ; 15.75x23.62 in)', 'FR_DOUBLE_POT' => 'FR_DOUBLE_POT (400x620 mm ; 15.75x24.41 in)', 'FR_ECU' => 'FR_ECU (400x520 mm ; 15.75x20.47 in)', 'FR_COURONNE' => 'FR_COURONNE (360x460 mm ; 14.17x18.11 in)', 'FR_TELLIERE' => 'FR_TELLIERE (340x440 mm ; 13.39x17.32 in)', 'FR_POT' => 'FR_POT (310x400 mm ; 12.20x15.75 in)',)),
            'class' => 'o-select2',
            'default' => get_proper_value($output_global_settings, 'pdf-format')
        );

        $pdf_margin_top_bottom = array(
            'title' => __('PDF Margin Top & Bottom', 'wpd'),
            'name' => 'wpc-metas[' . $product_id . '][output-settings][pdf-margin-tb]',
            'type' => 'text',
            'default' => get_proper_value($output_global_settings, 'pdf-margin-tb', 20)
        );

        $pdf_margin_left_right = array(
            'title' => __('PDF Margin Left & Right', 'wpd'),
            'name' => 'wpc-metas[' . $product_id . '][output-settings][pdf-margin-lr]',
            'type' => 'text',
            'default' => get_proper_value($output_global_settings, 'pdf-margin-lr', 20)
        );

        $pdf_orientation = array(
            'title' => __('PDF Orientation', 'wpd'),
            'name' => 'wpc-metas[' . $product_id . '][output-settings][pdf-orientation]',
            'default' => 'P',
            'type' => 'select',
            'options' => array(
                'P' => __('Portrait', 'wpd'),
                'L' => __('Landscape', 'wpd')
            ),
            'default' => get_proper_value($output_global_settings, 'pdf-orientation')
     
        );

        $output_options_begin = array(
            'type' => 'sectionbegin',
            'title' => __('Output Settings', 'wpd'),
            'table' => 'metas',
            'id' => 'wpc_product_output'
        );

        $output_options_end = array('type' => 'sectionend',
            'id' => 'wpc_product_output'
        );

        array_push($options, $output_options_begin);
        array_push($options, $output_w);
        array_push($options, $output_loop_delay);
        array_push($options, $cmyk_conversion);
        array_push($options, $pdf_format);
        array_push($options, $pdf_margin_left_right);
        array_push($options, $pdf_margin_top_bottom);
        array_push($options, $pdf_orientation);
        array_push($options, $zip_folder_name);
        array_push($options, $output_options_end);
        echo o_admin_fields($options);
    }

    private function get_product_output_settings($variation_id, $attributes_str) {
        ?>
        <div class="wc-metaboxes-wrapper">
            <div class="wc-metabox">
                <h3>
                    <div class="handlediv" title="Click to toggle"></div>
                    <strong><?php echo "#$variation_id - $attributes_str"; ?></strong>
                </h3>
                <div class="wpc-output-dimensions-block">
                    <?php
                    $this->get_outputs_settings($variation_id);
                    ?>
                </div>

            </div>
        </div>
        <?php
    }

    /**
     * Checks the product contains at least one active part
     * @return boolean
     */
    public function has_part() {
        $parts = get_option("wpc-parts");
        $wc_product = wc_get_product($this->root_product_id);
        $wpc_metas = get_post_meta($this->root_product_id, 'wpc-metas', true);
        if ($wc_product->product_type == "variable") {
            $variations = $wc_product->get_available_variations();
            foreach ($variations as $variation) {
                $variation_id = $variation['variation_id'];
                foreach ($parts as $part) {
                    $part_key = sanitize_title($part);
                    if (
                            get_proper_value($wpc_metas, $variation_id, array()) && get_proper_value($wpc_metas[$variation_id], 'parts', array()) && get_proper_value($wpc_metas[$variation_id]['parts'], $part_key, array())) {
//                        $part_media_id = get_proper_value($wpc_metas[$variation_id]['parts'][$part_key], 'bg-inc', "");
                        $enabled = get_proper_value($wpc_metas[$variation_id]['parts'][$part_key], "enabled", false);
                    } else {
//                        $part_media_id = "";
                        $enabled = false;
                    }
                    if (/*                     * $part_media_id || $part_media_id == "0"||* */$enabled)
                        return true;
                }
            }
        }
        else {
            foreach ($parts as $part) {
                $part_key = sanitize_title($part);
                if (get_proper_value($wpc_metas, $this->root_product_id, array()) && get_proper_value($wpc_metas[$this->root_product_id], 'parts', array()) && get_proper_value($wpc_metas[$this->root_product_id]['parts'], $part_key, array())) {
//                    $part_media_id = get_proper_value($wpc_metas[$this->root_product_id]['parts'][$part_key], 'bg-inc', "");
                    $enabled = get_proper_value($wpc_metas[$this->root_product_id]['parts'][$part_key], "enabled", false);
                } else {
//                        $part_media_id = "";
                    $enabled = false;
                }
                if (/*                 * $part_media_id || $part_media_id == "0"||* */$enabled)
                    return true;
            }
        }
        return false;
    }

    /**
     * Returns the customization page URL
     * @global Array $wpc_options_settings
     * @param int $design_index Saved design index to load
     * @param mixed $cart_item_key Cart item key to edit
     * @param int $order_item_id Order item ID to load
     * @param int $tpl_id ID of the template to load
     * @return String
     */
    public function get_design_url($design_index = false, $cart_item_key = false, $order_item_id = false, $tpl_id = false) {
        GLOBAL $wpc_options_settings;

        if ($this->variation_id)
            $item_id = $this->variation_id;
        else
            $item_id = $this->root_product_id;

        $options = $wpc_options_settings['wpc-general-options'];
        $wpc_page_id = $options['wpc_page_id'];
        if (function_exists("icl_object_id")) {
            $wpc_page_id = icl_object_id($wpc_page_id, 'page', false, ICL_LANGUAGE_CODE);
        }
        $wpc_page_url = "";
        if ($wpc_page_id) {
            $wpc_page_url = get_permalink($wpc_page_id);
            if ($item_id) {
                $query = parse_url($wpc_page_url, PHP_URL_QUERY);
                // Returns a string if the URL has parameters or NULL if not
                if (get_option('permalink_structure')) {
                    if (substr($wpc_page_url, -1) != '/') {
                        $wpc_page_url .= '/';
                    }
                    if ($design_index || $design_index === 0) {
                        $wpc_page_url .= "saved-design/$item_id/$design_index/";
                    } elseif ($cart_item_key) {
                        $wpc_page_url .= "edit/$item_id/$cart_item_key/";
                    } elseif ($order_item_id) {
                        $wpc_page_url .= "ordered-design/$item_id/$order_item_id/";
                    } else {
                        $wpc_page_url .= 'design/' . $item_id . '/';
                        if ($tpl_id) {
                            $wpc_page_url .= "$tpl_id/";
                        }
                    }
                } else {
                    if ($design_index) {
                        $wpc_page_url .= '&product_id=' . $item_id . '&design_index=' . $design_index;
                    } elseif ($cart_item_key) {
                        $wpc_page_url .= '&product_id=' . $item_id . '&edit=' . $cart_item_key;
                    } elseif ($order_item_id) {
                        $wpc_page_url .= '&product_id=' . $item_id . '&oid=' . $order_item_id;
                    } else {
                        $wpc_page_url .= '&product_id=' . $item_id;
                        if ($tpl_id) {
                            $wpc_page_url .= "&tpl=$tpl_id";
                        }
                    }
                }
            }
        }

        return $wpc_page_url;
    }

    /**
     * Returns a variation root product ID
     * @param type $variation_id Variation ID
     * @return int
     */
    public function get_parent($variation_id) {
        $variable_product = wc_get_product($variation_id);
        if ($variable_product->product_type == "simple")
            $product_id = $variation_id;
        else {
            //$product_id=$variable_product->parent->id;
            $product_id = $variable_product->id;
        }

        return $product_id;
    }

    /**
     * Returns the defined value for a product setting which can be local(product metas) or global (options)
     * @param array $product_settings Product options
     * @param array $global_settings Global options
     * @param string $option_name Option name / Meta key
     * @param int $field_value Default value to return if empty
     * @return string
     */
    public function get_option($product_settings, $global_settings, $option_name, $field_value = "") {
        if (isset($product_settings[$option_name]) && !empty($product_settings[$option_name]))
            $field_value = $product_settings[$option_name];
        else if (isset($global_settings[$option_name]) && !empty($global_settings[$option_name]))
            $field_value = $global_settings[$option_name];

        return $field_value;
    }

    function set_custom_upl_cart_item_data($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
        $element_id = $product_id;
//        if (isset($variation_id) && !empty($variation_id))
//            $element_id = $variation_id;

        if (isset($_SESSION["wpc-user-uploaded-designs"][$element_id])) {
            $cart_item_data["variation"] = "wpc-custom-upl";
            if (!isset($_SESSION["wpc-uploaded-designs"][$cart_item_key]))
                $_SESSION["wpc-uploaded-designs"][$cart_item_key] = array();
            array_push($_SESSION["wpc-uploaded-designs"][$cart_item_key], $_SESSION["wpc-user-uploaded-designs"][$element_id]);
            unset($_SESSION["wpc-user-uploaded-designs"][$element_id]);
        }
        if (!isset($_SESSION["wpc_design_pricing_options"]))
            $_SESSION["wpc_design_pricing_options"] = array();

        if (isset($_POST['wpd-design-opt']))
            $_SESSION["wpc_design_pricing_options"][$cart_item_key] = $_POST['wpd-design-opt'];
    }

    /**
     * Returns the minimum and maximum order quantities
     * @return type
     */
    function get_purchase_properties() {
        if ($this->variation_id) {
            $defined_min_qty = get_post_meta($this->variation_id, 'variation_minimum_allowed_quantity', true);
            //We consider the values defined for the all of them
            if (!$defined_min_qty)
                $defined_min_qty = get_post_meta($this->root_product_id, 'minimum_allowed_quantity', true);

            if (!$defined_min_qty)
                $defined_min_qty = 0;

            $defined_max_qty = get_post_meta($this->variation_id, 'variation_maximum_allowed_quantity', true);
            //We consider the values defined for the all of them
            if (!$defined_max_qty)
                $defined_max_qty = get_post_meta($this->root_product_id, 'maximum_allowed_quantity', true);
        }
        else {
            $defined_min_qty = get_post_meta($this->root_product_id, 'minimum_allowed_quantity', true);
            if (!$defined_min_qty)
                $defined_min_qty = 0;

            $defined_max_qty = get_post_meta($this->root_product_id, 'variation_maximum_allowed_quantity', true);
        }


        $step = apply_filters('woocommerce_quantity_input_step', '1', $this->product);
        $min_qty = apply_filters('woocommerce_quantity_input_min', $defined_min_qty, $this->product);

        if (!$defined_max_qty)
            $defined_max_qty = apply_filters('woocommerce_quantity_input_max', $this->product->backorders_allowed() ? '' : $this->product->get_stock_quantity(), $this->product);

        $min_to_purchase = $min_qty;
        if (!$min_qty)
            $min_to_purchase = 1;

        return array(
            "min" => $min_qty,
            "min_to_purchase" => $min_to_purchase,
            "max" => $defined_max_qty,
            "step" => $step
        );
    }

    function get_related_product_desc() {
        $purchase_properties = $this->get_purchase_properties();
        return __("Requires a minimum purchase of ", "wpd") . $purchase_properties["min"] . __(" item(s).", "wpd");
    }

    function get_variable_products_settings($loop, $variation_data, $variation) {
        $tab_id = uniqid();
//        var_dump($loop);
//        var_dump($variation_data);
//        var_dump($variation);
        ?>
        <div id="<?php echo $tab_id; ?>" class="TabbedPanels VTabbedPanels wpd-variation-settings">
            <ul class="TabbedPanelsTabGroup ">
                <li class="TabbedPanelsTab " tabindex="1"><span><?php _e('Canvas', 'wpd'); ?></span> </li>
                <li class="TabbedPanelsTab " tabindex="2"><span><?php _e('Bounding box', 'wpd'); ?></span> </li>
                <li class="TabbedPanelsTab" tabindex="3"><span><?php _e('Product parts', 'wpd'); ?></span></li>
                <li class="TabbedPanelsTab" tabindex="4"><span><?php _e('Output settings', 'wpd'); ?></span></li>
            </ul>

            <div class="TabbedPanelsContentGroup">
                <div class="TabbedPanelsContent">
        <?php $this->get_canvas_settings_tab($variation->ID); ?>
                </div>
                <div class="TabbedPanelsContent">
        <?php $this->get_bounding_box_settings_tab($variation->ID); ?>
                </div>
                <div class="TabbedPanelsContent">
                    <?php $this->get_parts_settings_tab($variation->ID); ?>
                </div>
                <div class="TabbedPanelsContent">
                    <?php $this->get_outputs_settings($variation->ID); ?>
                </div>

            </div>
        </div>
                    <?php
                }

                public function save_product_settings_fields($item_id) {
                    $meta_key = "wpc-metas";
                    if (isset($_POST[$meta_key])) {
//           var_dump($_POST[$meta_key]);
//           echo "<hr>";
                        $variation = wc_get_product($item_id);
                        //If we're dealing with a variation, Product ID is the root ID of the product
                        if (get_class($variation) == "WC_Product_Variation")
                            $product_id = $variation->parent->id;
                        else
                            $product_id = $item_id;
                        //Careful this hooks only send the updated data, not the complete form
                        $old_metas = get_post_meta($product_id, $meta_key, true);
                        if (empty($old_metas))
                            $old_metas = array();
                        $new_metas = array_replace($old_metas, $_POST[$meta_key]);

                        //If the related products and quantities are not in the post variable, that means the user is disabling them
                        if (!isset($_POST[$meta_key]["related-products"]))
                            $new_metas["related-products"] = array();
                        if (!isset($_POST[$meta_key]["related-quantities"]))
                            $new_metas["related-quantities"] = array();
                        if (!isset($_POST[$meta_key]['can-upload-custom-design']))
                            $new_metas['can-upload-custom-design'] = '';
                        if (!isset($_POST[$meta_key]['can-design-from-blank']))
                            $new_metas['can-design-from-blank'] = '';
                        if (!isset($_POST[$meta_key]['is-customizable']))
                            $new_metas['is-customizable'] = '';
                        update_post_meta($product_id, $meta_key, $new_metas);
                    }
                }

            }
            