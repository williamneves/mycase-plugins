<?php

function generate_adobe_thumb($working_dir, $input_filename, $output_filename) {
    $pos = strrpos($input_filename, ".");
    $input_extension = substr($input_filename, $pos + 1);
    $input_path = $working_dir . "/$input_filename";
    $output_extension = "png";
    $image = new Imagick($input_path);
    $image->setResolution(300, 300);
    $image->setImageFormat($output_extension);
    if ($input_extension == "psd") {
        $image->setIteratorIndex(0);
    }
    $success = $image->writeImage($working_dir . "/$output_filename");
    return $success;
}

function wpd_get_custom_products() {
    global $wpdb;
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
    return $products;
}

function wpd_generate_css($values) {
    ?>
    <style>
    <?php
    foreach ($values as $key => $value) {
        echo $key;
        ?>
            {
        <?php
        foreach ($value as $attr => $val) {
            echo $attr . ':' . $val . '!important;';
        }
        ?>
            }
        <?php
    }
    ?>
    </style>
    <?php
}

/**
 * Generates the css code related to each interface element
 * @param array $options Options group
 * @param string $id Selector ID
 * @param type $icon_field Icon field
 * @param type $keyword Item keyword in options
 */
function wpd_generate_css_tab($options, $id, $icon_field, $keyword, $icons_properties=array(),$class=false) {
    $param_color = $keyword . '-background-color';
    $param_color_selected = $keyword . '-background-color-hover';
    $param_label_color = $keyword . '-text-color';
    $tab_text_color_normal = get_proper_value($options, $param_color, "");
    $tab_text_color_hover = get_proper_value($options, $param_color_selected, "");
    $label_color = get_proper_value($options, $param_label_color, "");

    $icon_id = get_proper_value($options, $icon_field, "");
    $attribut_value_array = array();
    if (!empty($tab_text_color_normal)) {
        $attribut_value_array["background-color"] = $tab_text_color_normal;
        if ($keyword == 'plus' || $keyword == 'minus') {
            $attribut_value_array["background"] = $tab_text_color_normal;
            $attribut_value_array["box-shadow"] = "inset 0px 1px 0px 0px " . $tab_text_color_normal;
            $attribut_value_array["-moz-box-shadow"] = "inset 0px 1px 0px 0px " . $tab_text_color_normal;
            $attribut_value_array["-webkit-box-shadow"] = "inset 0px 1px 0px 0px " . $tab_text_color_normal;
        }
    }
    if (!empty($label_color))
        $attribut_value_array["color"] = $label_color;

    if (!empty($tab_text_color_hover)) {
         if($class)
             $id_hover = "." . $id . ':hover';
         else
        $id_hover = "#" . $id . ':hover';
        $selector_attribut[$id_hover]["background-color"] = $tab_text_color_hover;
    }
    if (!empty($icon_id)) {
        $icon_url = wp_get_attachment_url($icon_id, 'thumbnail');
        $icon_css = "url($icon_url)";
        $attribut_value_array["background-image"] = $icon_css;
        if(empty($icons_properties))
        {            
            $attribut_value_array["background-size"] = '30px';
            $attribut_value_array["background-position"] = '3%';
        }
        else
           $attribut_value_array=  array_merge ($attribut_value_array, $icons_properties);
    }
    if($class)
        $selector=".".$id;
    else
        $selector = "#" . $id;
    $selector_attribut[$selector] = $attribut_value_array;
    wpd_generate_css($selector_attribut);
}

/*
 * Returns the fields used for the user interface options
 */

function wpd_get_ui_options_fields() {
    $fields = array(
        "text" => array(
            "title" => __("Text Tab", "wpd"),
            "icon" => true,
        ),
        "shapes" => array(
            "title" => __("Shapes Tab", "wpd"),
            "icon" => true,
        ),
        "uploads" => array(
            "title" => __("Uploads Tab", "wpd"),
            "icon" => true,
        ),
        "cliparts" => array(
            "title" => __("Cliparts Tab", "wpd"),
            "icon" => true,
        ),
        "facebook" => array(
            "title" => __("Facebook Tab", "wpd"),
            "icon" => true,
        ),
        "facebook-btn" => array(
            "title" => __("Facebook Button", "wpd"),
            "icon" => true,
        ),
        "instagram" => array(
            "title" => __("Instagram Tab", "wpd"),
            "icon" => true,
        ),
        "instagram-btn" => array(
            "title" => __("Instagram Button", "wpd"),
            "icon" => true,
        ),
        "my-designs" => array(
            "title" => __("My Designs Tab", "wpd"),
            "icon" => true,
        ),
        "related-products" => array(
            "title" => __("Related Products Tab", "wpd"),
            "icon" => true,
        ),
        "preview-btn" => array(
            "title" => __("Preview Button", "wpd"),
            "icon" => true,
        ),
        "download-btn" => array(
            "title" => __("Download Button", "wpd"),
            "icon" => true,
        ),
        "save-btn" => array(
            "title" => __("Save Button", "wpd"),
            "icon" => true,
        ),
        "add-to-cart-btn" => array(
            "title" => __("Add to Cart Button", "wpd"),
            "icon" => true,
        ),
        "action-box" => array(
            "title" => __("Actions Box Title", "wpd"),
        ),
        "cart-box" => array(
            "title" => __("Cart Box Title", "wpd"),
        ),
        "plus-btn" => array(
            "title" => __("Plus Button", "wpd"),
        ),
        "minus-btn" => array(
            "title" => __("Minus Button", "wpd"),
        ),
        "design-from-blank-btn" => array(
            "title" => __("Design From Blank Button", "wpd"),
        ),
        "browse-our-templates-btn" => array(
            "title" => __("Browse Our templates Button", "wpd"),
        ),
        "upload-my-own-design-btn" => array(
            "title" => __("Upload My Own Design Button", "wpd"),
        ),
        "use-this-template-btn" => array(
            "title" => __("Use this template Button", "wpd"),
        )
    );
    return $fields;
}

function wpd_generate_design_buttons_css()
{
    global $wpc_options_settings;
    $ui_options = get_proper_value($wpc_options_settings, 'wpc-ui-options', array());
    wpd_generate_css_tab($ui_options, "wpc-customize-product", "", "design-from-blank-btn",array(),true);
    wpd_generate_css_tab($ui_options, "tpl", "", "browse-our-templates-btn",array(),true);
    wpd_generate_css_tab($ui_options, "wpc-upload-product-design", "", "upload-my-own-design-btn",array(),true);
    wpd_generate_css_tab($ui_options, "btn-choose", "", "tpl-btn",array(),true);
    wpd_generate_css_tab($ui_options, "btn-choose", "", "use-this-template-btn",array(),true);
}
