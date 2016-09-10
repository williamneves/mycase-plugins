<?php
/**
 * Package: TheShopier.
 * User: kinhdon
 * Date: 11/6/2015
 * Vertion: 1.0
 */

if( !empty($link) ) {
    $sprintf_str = '<a class="%1$s" href="%2$s" style="%5$s" title="%3$s">%4$s</a>';
} else {
    $sprintf_str = '<button class="%1$s" style="%5$s">%4$s</button>';
}

$in_css = ''; $icon = '';$classes = array( 'button' );
if( !empty($bgcl_style) && strcmp($bgcl_style, 'custom_color') == 0 ) $in_css = 'background-color: '. $bg_color . ';';
if( !empty($color) ) $in_css .= 'color: ' . $color . ';';
if( !empty($border_color) && strcmp($style, 'outline') == 0 ) $in_css .= 'border: ' . $border_color . ' 1px solid;';

if( !empty($use_icon) && absint($use_icon) == 1 ) $icon = '<i class="'.esc_attr($icon_fontawesome).'"></i>';
if( !empty($size) ) $classes[] = $size;
if( !empty($style) ) $classes[] = esc_attr($style);

if( !empty($bgcl_style) ) $classes[] = esc_attr($bgcl_style);
if( !empty($el_class) ) $classes[] = esc_attr($el_class);

printf( $sprintf_str, implode(' ', $classes), esc_url($link), esc_html($text), $icon . esc_html($text), $in_css );
?>
