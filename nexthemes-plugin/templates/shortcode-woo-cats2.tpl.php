<?php
/**
 * Package: TheShopier.
 * User: kinhdon
 * Date: 1/7/2016
 * Vertion: 1.0
 */

$cats_group = (array) vc_param_group_parse_atts( $cats_group );

?>

<?php if( strlen( $title ) > 0 ):?>

    <?php $h_style = strlen($h_style) >0? ' '.$h_style: '';?>
    <h3 class="heading-title<?php echo esc_attr($h_style)?>"><?php echo esc_html($title);?></h3>

<?php endif;?>

<div class="woo-categories-wrapper">
    <div class="woo-categories-list">
        <?php
        $li_str = ''; $cat_info = ''; $i=0;
        foreach($cats_group as $cat) {
            if( !empty($cat['slug']) ) {
                if($i == 0) {
                    $class1 = 'active';
                    $class2 = 'text-center';
                } else {
                    $class1 = '';
                    $class2 = 'text-center hidden';
                }
                $term = get_term_by('slug', $cat['slug'], 'product_cat');
                if($term) {
                    $term_link = get_term_link($term, 'product_cat');
                    $image_src = wp_get_attachment_image_src($cat['cat_image'], 'full');
                    $li_str .= '<li class="woo-cat-item '.esc_attr($term->slug).' '.esc_attr($class1).'" data-id="#'.esc_attr($term->slug).'"><span title="'.esc_attr($term->name).'" class="text">'.esc_html($term->name).'</span><span class="line"></span></li>';
                    /*$style = 'background: url('.esc_url($image_src[0]).') no-repeat center center;';
                    $style .= '-webkit-background-size: cover;';
                    $style .= '-moz-background-size: cover;';
                    $style .= '-o-background-size: cover;';
                    $style .= 'background-size: cover;';*/

                    $cat_info .= '<div class="cat-info-item '.esc_attr($class2).'" id="'.esc_attr($term->slug).'">';
                    $cat_info .= wp_get_attachment_image($cat['cat_image'], 'full');
                    $cat_info .= '<a class="button animated zoomIn" href="'.esc_url($term_link).'">'.esc_html($button_txt).'</a>';
                    $cat_info .= '</div>';
                }

                $i++;
            }
        }

        if(strlen($li_str) > 0) {
            echo '<ul class="list-unstyled">'.$li_str.'</ul>';
            if(!empty($shop_all_text))
                printf('<a href="%1$s" title="%2$s" class="shop-all">%3$s</a>', esc_url(get_permalink( wc_get_page_id( 'shop' ) )), esc_attr($shop_all_text), esc_html($shop_all_text));
        }
        ?>
    </div>
    <div class="woo-categories-info">
        <?php echo $cat_info;?>
    </div>
</div>