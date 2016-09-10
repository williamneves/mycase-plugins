<?php
/**
 * Package: TheShopier.
 * User: kinhdon
 * Date: 11/13/2015
 * Vertion: 1.0
 */
global $post;

?>

<div class="nth-portfolio-item <?php echo esc_attr(implode(' ', $class));?> <?php echo esc_attr( implode(' ', $cats) );?>" data-filter="<?php echo esc_attr( implode(',', $cats) );?>">

    <div class="nth-portfolio-thumb">
        <div class="thumnail">
            <a href="<?php echo get_permalink();?>" title="<?php echo get_the_title();?>"><?php if(has_post_thumbnail()) the_post_thumbnail('portfolio_thumb');?></a>
        </div>
        <div class="summary">
            <h3><a href="<?php echo get_permalink();?>" title="<?php echo get_the_title();?>"><?php the_title();?></a></h3>
            <div class="nth-meta">
                <?php
                $gallery_meta = '';
                $_rexes = '';
                $cats = get_the_term_list( $post->ID, $tax_cat, '<span class="meta-cats">', ', ', '</span><!--.meta-cats-->' );

                if( !empty($galleries) ) {
                    $images_str = '';
                    $videos_str = '';
                    $_rex = '';
                    if( !empty($galleries['att_img']) ) {
                        $count = count(explode(',', $galleries['att_img']));
                        $images_str = sprintf( _n('%d image', '%d images', $count, 'nexthemes-plugin'), $count);
                    }
                    if( !empty($galleries['v_link']) ) {
                        $count = count($galleries['v_link']);
                        $videos_str = sprintf( _n('%d video', '%d videos', $count, 'nexthemes-plugin'), $count);
                    }

                    if( strlen($images_str) > 0 && strlen($videos_str) > 0 ) {
                        $_rex = '&nbsp;&&nbsp;';
                    }

                    $gallery_meta = '<span class="meta-galleries">'.$images_str . $_rex . $videos_str . '</span>';
                }

                if( strlen($cats) > 0 && strlen($gallery_meta) > 0 ) $_rexes = '&nbsp;/&nbsp;';

                echo $cats . $_rexes . $gallery_meta;
                ?>
            </div>
        </div>
    </div>

</div>