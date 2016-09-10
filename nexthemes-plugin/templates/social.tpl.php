<?php
/**
 * Package: TheShopier.
 * User: kinhdon
 * Date: 12/11/2015
 * Vertion: 1.0
 */

if( function_exists('vc_param_group_parse_atts') )
    $items = (array) vc_param_group_parse_atts( $item );
else return;

$ul_classes = array('toolbar_item nth-social-network');
$ul_classes[] = 'list-inline';
$ul_classes[] = 'nth-social-' . mt_rand();
if( !empty($class) ) $ul_classes[] = $class;
?>
<?php if( strlen( trim( $title ) ) > 0 ):?>

    <h3 class="heading-title ud-line"><?php echo esc_html( $title );?></h3>

<?php endif;?>

<?php
$li_html = ''; $ct_css = '';
foreach($items as $item) {
    $li_class = 'li-'.rand();
    $link = !empty( $item['link'] )? $item['link']: '#';
    $title = !empty( $item['title'] )? $item['title']: 'Facebook';
    $icon = !empty( $item['icon'] )? $item['icon']: 'fa fa-facebook-square';
    $color = !empty( $item['color'] )? $item['color']: '#6475c2';
    if( absint($color_hover) ) $ct_css .= '.'.$li_class.' a:hover {color: '.$color.'}';
    else $ct_css .= '.'.$li_class.' a {color: '.$color.'}';
    $icon .= ' ' . $ic_size;

    $li_html .= '<li class="'.esc_attr($li_class).'"><a href="'.esc_url($link).'" title="'.esc_attr($title).'">';
    $li_html .= '<span data-toggle="tooltip" data-placement="top" title="'.esc_attr($title).'" class="'.esc_attr($icon).'"></span>';
    $li_html .= '</a></li>';
}
?>

<style type="text/css" scoped><?php echo $ct_css;?></style>

<ul class="<?php echo esc_attr(implode(' ', $ul_classes));?>">
    <?php echo $li_html;?>
</ul>
