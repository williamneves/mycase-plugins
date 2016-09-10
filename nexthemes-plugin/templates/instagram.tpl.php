<?php
/**
 * Package: TheShopier.
 * User: kinhdon
 * Date: 1/6/2016
 * Vertion: 1.0
 */

$options = array(
    "items"				=> $columns,
);
$options = NexThemes_Plg::get_owlResponsive($options);

?>

<?php if( strlen( $title ) > 0 ):?>

    <?php $h_style = strlen($h_style) >0? ' '.$h_style: '';?>
    <h3 class="heading-title<?php echo esc_attr($h_style)?>"><?php echo $title;?></h3>

<?php endif;?>


<div class="instagram-wrapper instagram-media-ajax nth-loading <?php echo $columns?>" data-thumbnail="<?php echo esc_attr($image_size);?>" data-limit="<?php echo absint($limit);?>" data-options="<?php echo esc_attr(json_encode($options));?>" data-base="1">

</div>
