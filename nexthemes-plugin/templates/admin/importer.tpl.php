<?php
/**
 * Package: TheShopier.
 * User: kinhdon
 * Date: 1/31/2016
 * Vertion: 1.0
 */

?>

<div class="wrap about-wrap nexthemes-wrap">

    <?php do_action('nth_plugin_panel_header');?>

    <div class="nav-tab-conent">

        <div class="theme-importer-wrapper">

            <?php
            $dummy_installed = get_option('nth_dummy_installed', false);
            $class_dumm = array("importer-section main-dummydata import-dummy");
            $class_setting = array('importer-section homepage-import');
            $dummy_disabled = $home_disabled = '';

            if( !absint($dummy_installed) ) :

                $param = array(
                    'action'    => 'nexthemes_import_dummy',
                    'security'   => wp_create_nonce( '__THEME_IMPORT_5362' )
                );
            ?>


            <div class="<?php echo esc_attr( implode(' ', $class_dumm) )?>">
                <h2>Main Dummy data</h2>

                <button class="nth-ajax-call button button-primary" <?php echo $dummy_disabled;?> data-param="<?php echo esc_attr(wp_json_encode($param))?>" data-progress_text="<?php esc_attr_e('Please wait a moment...', 'nexthemes-plugin')?>"><i class="nth-icon nth-icon-download"></i> <?php _e('Import Dummy Data', 'nexthemes-plugin' );?></button>
            </div>

            <?php else: ?>

            <div class="<?php echo esc_attr(implode(' ', $class_setting))?>">
                <h2>Import Homepage</h2>

                <div class="homepages-wrapper">
                <?php

                $import_arrs = Nexthemes_Importer::getThemeHomepages();

                $homepages = !empty($import_arrs['homepages'])? $import_arrs['homepages']: array();
                $req_plugins = !empty($import_arrs['req_plugins'])? $import_arrs['req_plugins']: array();


                $home_current = get_option('nth_theme_current', false);

                $home_install = @unserialize(get_option('nth_theme_imported', true));

                $ref_url = '';
                if( NexThemes_Plg::checkPlugin('woocommerce/woocommerce.php') ) {
                    $woo_admin_notice = get_option('woocommerce_admin_notices', array());
                    if( in_array('install', $woo_admin_notice) )
                        $ref_url = admin_url( 'admin.php?page=wc-setup' );
                }

                    foreach( $homepages as  $slug => $home_arg) :
                        if(class_exists('Nexthemes_Importer')) $home_variable = Nexthemes_Importer::checkImportfiles($slug);
                        else $home_variable = false;

                        if( $home_variable == false ) continue;

                        $home_img_url = esc_url(get_template_directory_uri() . "/framework/backend/images/imports/homepage-iwrap{$slug}.jpg");

                        $param = array(
                            'action'    => 'nexthemes_import_home',
                            'security'  => wp_create_nonce( '__THEME_IMPORT_5362' ),
                            'home'      => $slug
                        );

                    ?>
                    <div class="homepage-item-wrapper">
                        <div class="homepage-item-inner">
                            <?php
                            $image_class = array('image-wrapper');
                            $home_title_rex = '%s';

                            if( $home_install && is_array($home_install) && in_array($slug, $home_install)) {
                                $home_title_rex = '%s <span class="imported">(%s)</span>';
                                if(absint($home_current) > 0 && absint($home_current) == absint($slug)) {
                                    $image_class[] = 'current_item';
                                    $button_resetup = sprintf('<button class="button" disabled><i class="nth-icon-check"></i> %s</button>', __('Applied', 'nexthemes-plugin') );
                                } else {
                                    $image_class[] = 'imported_item';
                                    $button_resetup = sprintf('<button class="nth-ajax-call button" data-json="1" data-ref="%s" data-param="%s"><i class="nth-icon-download"></i> %s</button>', esc_attr($ref_url), esc_attr(wp_json_encode($param)), __('Apply', 'nexthemes-plugin') );
                                }
                            } else {
                                $button_resetup = sprintf('<button class="nth-ajax-call button button-primary" data-json="1" data-ref="%s" data-param="%s"><i class="nth-icon-download"></i> %s</button>', esc_attr($ref_url), esc_attr(wp_json_encode($param)), __('Import', 'nexthemes-plugin'));
                            }

                            NexThemes_Plg::getImage(array(
                                'src'       => $home_img_url,
                                'alt'       => 'home-' . $slug,
                                'before'    => '<div class="'.esc_attr(implode(' ', $image_class)).'">',
                                'after'     => '</div>'
                            ));
                            $req_pls_li = '';
                            foreach($home_arg['pl_request'] as $pl_path){
                                if( $req_plugins[$pl_path]['status'] == true ) {
                                    $rex_str = '<li class="active"><i class="nth-icon-check"></i> %s</li>';
                                } else {
                                    $rex_str = '<li class="request"><i class="nth-icon-check-empty"></i> %s</li>';
                                }

                                $req_pls_li .= sprintf($rex_str, esc_html($req_plugins[$pl_path]['name']));
                            }
                            ?>
                            <div class="meta-wrapper">
                                <div class="heading-wrap">
                                    <h3 class="home-title"><?php printf($home_title_rex, esc_html($home_arg['name']), __('imported', 'nexthemes-plugin') ) ?></h3>
                                    <ul class="pl-request"><?php echo $req_pls_li;?></ul>
                                </div>
                                <div class="action-buttons">
                                    <a class="button" href="<?php echo esc_url("//demo.nexthemes.com/wordpress/theshopier/home{$slug}/")?>" target="_blank" title="<?php echo esc_attr($home_arg['name'])?>"><?php _e('Preview', 'nexthemes-plugin')?></a>
                                    <?php echo $button_resetup;?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach;?>

                </div>
            </div>

            <?php endif; /* endif of if( !absint($dummy_installed) )*/?>

        </div>

    </div>

</div>
