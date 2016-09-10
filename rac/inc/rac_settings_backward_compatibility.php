<?php

function rac_settings_backward_compatibility() {

    if (!function_exists('woocommerce_admin_fields')) {

        function woocommerce_admin_fields($options) {
            global $woocommerce;

            foreach ($options as $value) {
                if (!isset($value['type']))
                    continue;
                if (!isset($value['id']))
                    $value['id'] = '';
                if (!isset($value['name']))
                    $value['name'] = '';
                if (!isset($value['class']))
                    $value['class'] = '';
                if (!isset($value['css']))
                    $value['css'] = '';
                if (!isset($value['std']))
                    $value['std'] = '';
                if (!isset($value['desc']))
                    $value['desc'] = '';
                if (!isset($value['desc_tip']))
                    $value['desc_tip'] = false;

                if ($value['desc_tip'] === true) {
                    //$description = '<img class="help_tip" data-tip="' . esc_attr( $value['desc'] ) . '" src="' . $woocommerce->plugin_url() . '/assets/images/help.png" />';
                } elseif ($value['desc_tip']) {
                    //$description = '<img class="help_tip" data-tip="' . esc_attr( $value['desc_tip'] ) . '" src="' . $woocommerce->plugin_url() . '/assets/images/help.png" />';
                } else {
                    $description = '<span class="description">' . $value['desc'] . '</span>';
                }

                switch ($value['type']) {
                    case 'title':
                        if (isset($value['name']) && $value['name'])
                            echo '<h3>' . $value['name'] . '</h3>';
                        if (isset($value['desc']) && $value['desc'])
                            echo wpautop(wptexturize($value['desc']));
                        echo '<table class="form-table">' . "\n\n";
                        if (isset($value['id']) && $value['id'])
                            do_action('woocommerce_settings_' . sanitize_title($value['id']));
                        break;
                    case 'sectionend':
                        if (isset($value['id']) && $value['id'])
                            do_action('woocommerce_settings_' . sanitize_title($value['id']) . '_end');
                        echo '</table>';
                        if (isset($value['id']) && $value['id'])
                            do_action('woocommerce_settings_' . sanitize_title($value['id']) . '_after');
                        break;
                    case 'text':
                        ?><tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr($value['id']); ?>"><?php echo $value['name']; ?></label>
                            </th>
                            <td class="forminp"><input name="<?php echo esc_attr($value['id']); ?>" id="<?php echo esc_attr($value['id']); ?>" type="<?php echo esc_attr($value['type']); ?>" style="<?php echo esc_attr($value['css']); ?>" value="<?php
                                if (get_option($value['id']) !== false && get_option($value['id']) !== null) {
                                    echo esc_attr(stripslashes(get_option($value['id'])));
                                } else {
                                    echo esc_attr($value['std']);
                                }
                                ?>" /> <?php echo $description; ?></td>
                        </tr><?php
                        break;
                    case 'color' :
                        ?><tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr($value['id']); ?>"><?php echo $value['name']; ?></label>
                            </th>
                            <td class="forminp"><input name="<?php echo esc_attr($value['id']); ?>" id="<?php echo esc_attr($value['id']); ?>" type="text" style="<?php echo esc_attr($value['css']); ?>" value="<?php
                                if (get_option($value['id']) !== false && get_option($value['id']) !== null) {
                                    echo esc_attr(stripslashes(get_option($value['id'])));
                                } else {
                                    echo esc_attr($value['std']);
                                }
                                ?>" class="colorpick" /> <?php echo $description; ?> <div id="colorPickerDiv_<?php echo esc_attr($value['id']); ?>" class="colorpickdiv" style="z-index: 100;background:#eee;border:1px solid #ccc;position:absolute;display:none;"></div></td>
                        </tr><?php
                        break;
                    case 'image_width' :
                        ?><tr valign="top">
                            <th scope="row" class="titledesc"><?php echo $value['name'] ?></th>
                            <td class="forminp">

                                <?php _e('Width', 'woocommerce'); ?> <input name="<?php echo esc_attr($value['id']); ?>_width" id="<?php echo esc_attr($value['id']); ?>_width" type="text" size="3" value="<?php
                                if ($size = get_option($value['id'] . '_width'))
                                    echo stripslashes($size);
                                else
                                    echo $value['std'];
                                ?>" />

                                <?php _e('Height', 'woocommerce'); ?> <input name="<?php echo esc_attr($value['id']); ?>_height" id="<?php echo esc_attr($value['id']); ?>_height" type="text" size="3" value="<?php
                                if ($size = get_option($value['id'] . '_height'))
                                    echo stripslashes($size);
                                else
                                    echo $value['std'];
                                ?>" />

                                <label><?php _e('Hard Crop', 'woocommerce'); ?> <input name="<?php echo esc_attr($value['id']); ?>_crop" id="<?php echo esc_attr($value['id']); ?>_crop" type="checkbox" <?php
                                    if (get_option($value['id'] . '_crop') != '')
                                        checked(get_option($value['id'] . '_crop'), 1);
                                    else
                                        checked(1);
                                    ?> /></label>

                                <?php echo $description; ?></td>
                        </tr><?php
                        break;
                    case 'select':
                        ?><tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr($value['id']); ?>"><?php echo $value['name']; ?></label>
                            </th>
                            <td class="forminp"><select name="<?php echo esc_attr($value['id']); ?>" id="<?php echo esc_attr($value['id']); ?>" style="<?php echo esc_attr($value['css']); ?>" class="<?php if (isset($value['class'])) echo $value['class']; ?>">
                                    <?php
                                    foreach ($value['options'] as $key => $val) {
                                        $_current = get_option($value['id']);
                                        if (!$_current) {
                                            $_current = $value['std'];
                                        }
                                        ?>
                                        <option value="<?php echo esc_attr($key); ?>" <?php selected($_current, $key); ?>><?php echo $val ?></option>
                                        <?php
                                    }
                                    ?>
                                </select> <?php echo $description; ?>
                            </td>
                        </tr><?php
                        break;
                    case 'checkbox' :

                        if (!isset($value['hide_if_checked']))
                            $value['hide_if_checked'] = false;
                        if (!isset($value['show_if_checked']))
                            $value['show_if_checked'] = false;

                        if (!isset($value['checkboxgroup']) || (isset($value['checkboxgroup']) && $value['checkboxgroup'] == 'start')) :
                            ?>
                            <tr valign="top" class="<?php
                            if ($value['hide_if_checked'] == 'yes' || $value['show_if_checked'] == 'yes')
                                echo 'hidden_option';
                            if ($value['hide_if_checked'] == 'option')
                                echo 'hide_options_if_checked';
                            if ($value['show_if_checked'] == 'option')
                                echo 'show_options_if_checked';
                            ?>">
                                <th scope="row" class="titledesc"><?php echo $value['name'] ?></th>
                                <td class="forminp">
                                    <fieldset>
                                        <?php
                                    else :
                                        ?>
                                        <fieldset class="<?php
                                        if ($value['hide_if_checked'] == 'yes' || $value['show_if_checked'] == 'yes')
                                            echo 'hidden_option';
                                        if ($value['hide_if_checked'] == 'option')
                                            echo 'hide_options_if_checked';
                                        if ($value['show_if_checked'] == 'option')
                                            echo 'show_options_if_checked';
                                        ?>">
                                                  <?php
                                                  endif;
                                                  ?>
                                        <legend class="screen-reader-text"><span><?php echo $value['name'] ?></span></legend>
                                        <label for="<?php echo $value['id'] ?>">
                                            <input name="<?php echo esc_attr($value['id']); ?>" id="<?php echo esc_attr($value['id']); ?>" type="checkbox" value="1" <?php checked(get_option($value['id']), 'yes'); ?> />
                                            <?php echo $value['desc'] ?></label> <?php if ($value['desc_tip']) echo $description; ?><br />
                                        <?php
                                        if (!isset($value['checkboxgroup']) || (isset($value['checkboxgroup']) && $value['checkboxgroup'] == 'end')) :
                                            ?>
                                        </fieldset>
                                </td>
                            </tr>
                            <?php
                        else :
                            ?>
                            </fieldset>
                        <?php
                        endif;

                        break;
                    case 'textarea':
                        ?><tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr($value['id']); ?>"><?php echo $value['name']; ?></label>
                            </th>
                            <td class="forminp">
                                <textarea <?php if (isset($value['args'])) echo $value['args'] . ' '; ?>name="<?php echo esc_attr($value['id']); ?>" id="<?php echo esc_attr($value['id']); ?>" style="<?php echo esc_attr($value['css']); ?>"><?php
                                    if (false !== get_option($value['id']))
                                        echo esc_textarea(stripslashes(get_option($value['id'])));
                                    else
                                        echo esc_textarea($value['std']);
                                    ?></textarea> <?php echo $description; ?>
                            </td>
                        </tr><?php
                        break;
                    case 'single_select_page' :
                        $page_setting = (int) get_option($value['id']);

                        $args = array('name' => $value['id'],
                            'id' => $value['id'],
                            'sort_column' => 'menu_order',
                            'sort_order' => 'ASC',
                            'show_option_none' => ' ',
                            'class' => $value['class'],
                            'echo' => false,
                            'selected' => $page_setting);

                        if (isset($value['args']))
                            $args = wp_parse_args($value['args'], $args);
                        ?><tr valign="top" class="single_select_page">
                            <th scope="row" class="titledesc"><?php echo $value['name'] ?></th>
                            <td class="forminp">
                                <?php echo str_replace(' id=', " data-placeholder='" . __('Select a page&hellip;', 'woocommerce') . "' style='" . $value['css'] . "' class='" . $value['class'] . "' id=", wp_dropdown_pages($args)); ?> <?php echo $description; ?>
                            </td>
                        </tr><?php
                        break;
                    case 'single_select_country' :
                        $countries = $woocommerce->countries->countries;
                        $country_setting = (string) get_option($value['id']);
                        if (strstr($country_setting, ':')) :
                            $country = current(explode(':', $country_setting));
                            $state = end(explode(':', $country_setting));
                        else :
                            $country = $country_setting;
                            $state = '*';
                        endif;
                        ?><tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr($value['id']); ?>"><?php echo $value['name']; ?></label>
                            </th>
                            <td class="forminp"><select name="<?php echo esc_attr($value['id']); ?>" style="<?php echo esc_attr($value['css']); ?>" data-placeholder="<?php _e('Choose a country&hellip;', 'woocommerce'); ?>" title="Country" class="chosen_select">
                                    <?php echo $woocommerce->countries->country_dropdown_options($country, $state); ?>
                                </select> <?php echo $description; ?>
                            </td>
                        </tr><?php
                        break;
                    case 'multi_select_countries' :
                        $countries = $woocommerce->countries->countries;
                        asort($countries);
                        $selections = (array) get_option($value['id']);
                        ?><tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr($value['id']); ?>"><?php echo $value['name']; ?></label>
                            </th>
                            <td class="forminp">
                                <select multiple="multiple" name="<?php echo esc_attr($value['id']); ?>[]" style="width:450px;" data-placeholder="<?php _e('Choose countries&hellip;', 'woocommerce'); ?>" title="Country" class="chosen_select">
                                    <?php
                                    if ($countries)
                                        foreach ($countries as $key => $val) :
                                            echo '<option value="' . $key . '" ' . selected(in_array($key, $selections), true, false) . '>' . $val . '</option>';
                                        endforeach;
                                    ?>
                                </select>
                            </td>
                        </tr><?php
                        break;
                    default:
                        do_action('woocommerce_admin_field_' . $value['type'], $value);
                        break;
                }
            }
        }

    }
    if (!function_exists('woocommerce_update_options')) {

        function woocommerce_update_options($options) {

            if (empty($_POST))
                return false;

            foreach ($options as $value) {
                if (isset($value['id']) && $value['id'] == 'woocommerce_tax_rates') {

                    // Tax rates saving
                    $tax_rates = array();
                    $tax_classes = (isset($_POST['tax_class'])) ? $_POST['tax_class'] : array();
                    $tax_countries = (isset($_POST['tax_country'])) ? $_POST['tax_country'] : array();
                    $tax_rate = (isset($_POST['tax_rate'])) ? $_POST['tax_rate'] : array();
                    $tax_shipping = (isset($_POST['tax_shipping'])) ? $_POST['tax_shipping'] : array();
                    $tax_postcode = (isset($_POST['tax_postcode'])) ? $_POST['tax_postcode'] : array();
                    $tax_compound = (isset($_POST['tax_compound'])) ? $_POST['tax_compound'] : array();
                    $tax_label = (isset($_POST['tax_label'])) ? $_POST['tax_label'] : array();
                    $tax_classes_count = sizeof($tax_classes);
                    for ($i = 0; $i < $tax_classes_count; $i++) :

                        if (isset($tax_classes[$i]) && isset($tax_countries[$i]) && isset($tax_rate[$i]) && is_numeric($tax_rate[$i])) :

                            $rate = esc_attr(trim($tax_rate[$i]));
                            $rate = number_format($rate, 4, '.', '');

                            $class = woocommerce_clean($tax_classes[$i]);

                            if (isset($tax_shipping[$i]) && $tax_shipping[$i])
                                $shipping = 'yes';
                            else
                                $shipping = 'no';
                            if (isset($tax_compound[$i]) && $tax_compound[$i])
                                $compound = 'yes';
                            else
                                $compound = 'no';

                            // Handle countries
                            $counties_array = array();
                            $countries = $tax_countries[$i];
                            if ($countries)
                                foreach ($countries as $country) :

                                    $country = woocommerce_clean($country);
                                    $state = '*';

                                    if (strstr($country, ':')) :
                                        $cr = explode(':', $country);
                                        $country = current($cr);
                                        $state = end($cr);
                                    endif;

                                    $counties_array[trim($country)][] = trim($state);

                                endforeach;

                            $tax_rates[] = array(
                                'countries' => $counties_array,
                                'rate' => $rate,
                                'shipping' => $shipping,
                                'compound' => $compound,
                                'class' => $class,
                                'label' => esc_attr($tax_label[$i])
                            );

                        endif;

                    endfor;

                    update_option('woocommerce_tax_rates', $tax_rates);

                    // Local tax rates saving
                    $local_tax_rates = array();
                    $tax_classes = (isset($_POST['local_tax_class'])) ? $_POST['local_tax_class'] : array();
                    $tax_countries = (isset($_POST['local_tax_country'])) ? $_POST['local_tax_country'] : array();
                    $tax_postcode = (isset($_POST['local_tax_postcode'])) ? $_POST['local_tax_postcode'] : array();
                    $tax_rate = (isset($_POST['local_tax_rate'])) ? $_POST['local_tax_rate'] : array();
                    $tax_shipping = (isset($_POST['local_tax_shipping'])) ? $_POST['local_tax_shipping'] : array();
                    $tax_postcode = (isset($_POST['local_tax_postcode'])) ? $_POST['local_tax_postcode'] : array();
                    $tax_compound = (isset($_POST['local_tax_compound'])) ? $_POST['local_tax_compound'] : array();
                    $tax_label = (isset($_POST['local_tax_label'])) ? $_POST['local_tax_label'] : array();
                    $tax_classes_count = sizeof($tax_classes);
                    for ($i = 0; $i < $tax_classes_count; $i++) :

                        if (isset($tax_classes[$i]) && isset($tax_countries[$i]) && isset($tax_rate[$i]) && is_numeric($tax_rate[$i])) :

                            $rate = esc_attr(trim($tax_rate[$i]));
                            $rate = number_format($rate, 4, '.', '');

                            $class = woocommerce_clean($tax_classes[$i]);

                            if (isset($tax_shipping[$i]) && $tax_shipping[$i])
                                $shipping = 'yes';
                            else
                                $shipping = 'no';
                            if (isset($tax_compound[$i]) && $tax_compound[$i])
                                $compound = 'yes';
                            else
                                $compound = 'no';

                            // Handle country
                            $country = woocommerce_clean($tax_countries[$i]);
                            $state = '*';

                            if (strstr($country, ':')) :
                                $cr = explode(':', $country);
                                $country = current($cr);
                                $state = end($cr);
                            endif;

                            // Handle postcodes
                            $postcodes = explode(';', $tax_postcode[$i]);
                            $postcodes = array_filter(array_map('trim', $postcodes));

                            $local_tax_rates[] = array(
                                'country' => $country,
                                'state' => $state,
                                'postcode' => $postcodes,
                                'rate' => $rate,
                                'shipping' => $shipping,
                                'compound' => $compound,
                                'class' => $class,
                                'label' => esc_attr($tax_label[$i])
                            );

                        endif;

                    endfor;

                    update_option('woocommerce_local_tax_rates', $local_tax_rates);
                } elseif (isset($value['type']) && $value['type'] == 'multi_select_countries') {

                    // Get countries array
                    if (isset($_POST[$value['id']]))
                        $selected_countries = $_POST[$value['id']];
                    else
                        $selected_countries = array();
                    update_option($value['id'], $selected_countries);
                } elseif (isset($value['id']) && ( $value['id'] == 'woocommerce_price_thousand_sep' || $value['id'] == 'woocommerce_price_decimal_sep' )) {

                    // price separators get a special treatment as they should allow a spaces (don't trim)
                    if (isset($_POST[$value['id']])) {
                        update_option($value['id'], $_POST[$value['id']]);
                    } else {
                        delete_option($value['id']);
                    }
                } elseif (isset($value['type']) && $value['type'] == 'checkbox') {

                    if (isset($value['id']) && isset($_POST[$value['id']])) {
                        update_option($value['id'], 'yes');
                    } else {
                        update_option($value['id'], 'no');
                    }
                } elseif (isset($value['type']) && $value['type'] == 'image_width') {

                    if (isset($value['id']) && isset($_POST[$value['id'] . '_width'])) {
                        update_option($value['id'] . '_width', woocommerce_clean($_POST[$value['id'] . '_width']));
                        update_option($value['id'] . '_height', woocommerce_clean($_POST[$value['id'] . '_height']));
                        if (isset($_POST[$value['id'] . '_crop'])) :
                            update_option($value['id'] . '_crop', 1);
                        else :
                            update_option($value['id'] . '_crop', 0);
                        endif;
                    } else {
                        update_option($value['id'] . '_width', $value['std']);
                        update_option($value['id'] . '_height', $value['std']);
                        update_option($value['id'] . '_crop', 1);
                    }
                } else {

                    if (isset($value['id']) && isset($_POST[$value['id']])) {
                        update_option($value['id'], woocommerce_clean($_POST[$value['id']]));
                    } elseif (isset($value['id'])) {
                        delete_option($value['id']);
                    }
                }
            }
            return true;
        }

    }

    if (!function_exists('woocommerce_clean')) {

        function woocommerce_clean($var) {
            return trim(strip_tags(stripslashes($var)));
        }

    }
}

if (isset($_GET['page'])) {
    if ($_GET['page'] == 'fprac_slug') {
        add_action('admin_head', 'rac_settings_backward_compatibility');
    }
}
?>