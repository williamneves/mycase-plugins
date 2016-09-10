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
        <table class="nth_status_table widefat" cellspacing="0">
            <thead>
            <tr>
                <th colspan="3" data-export-label="WordPress Environment"><?php _e( 'WordPress Environment', 'nexthemes-plugin' ); ?></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td data-export-label="Home URL"><?php _e( 'Home URL', 'nexthemes-plugin' ); ?>:</td>
                <td class="help"><?php echo nth_help_tip(__( 'The URL of your site\'s homepage.', 'nexthemes-plugin' ));?></td>
                <td><?php echo home_url(); ?></td>
            </tr>
            <tr>
                <td data-export-label="Site URL"><?php _e( 'Site URL', 'nexthemes-plugin' ); ?>:</td>
                <td class="help"><?php echo nth_help_tip(__( 'The root URL of your site.', 'nexthemes-plugin' ));?></td>
                <td><?php echo site_url(); ?></td>
            </tr>
            <tr>
                <td data-export-label="WP Version"><?php _e( 'WP Version', 'nexthemes-plugin' ); ?>:</td>
                <td class="help"><?php echo nth_help_tip(__( 'The version of WordPress installed on your site.', 'nexthemes-plugin' ));?></td>
                <td><?php bloginfo('version'); ?></td>
            </tr>
            <tr>
                <td data-export-label="WP Multisite"><?php _e( 'WP Multisite', 'nexthemes-plugin' ); ?>:</td>
                <td class="help"><?php echo nth_help_tip(__( 'Whether or not you have WordPress Multisite enabled.', 'nexthemes-plugin' ));?></td>
                <td><?php if ( is_multisite() ) echo '&#10004;'; else echo '&ndash;'; ?></td>
            </tr>
            <tr>
                <td data-export-label="WP Memory Limit"><?php _e( 'WP Memory Limit', 'nexthemes-plugin' ); ?>:</td>
                <td class="help"><?php echo nth_help_tip(__( 'The maximum amount of memory (RAM) that your site can use at one time.', 'nexthemes-plugin' ));?></td>
                <td>
                    <?php
                    $memory = nth_let_to_num( WP_MEMORY_LIMIT );
                    if ( function_exists( 'memory_get_usage' ) ) {
                        $system_memory = nth_let_to_num( @ini_get( 'memory_limit' ) );
                        $memory        = max( $memory, $system_memory );
                    }
                    if ( $memory < 67108864 ) {
                        echo '<mark class="error">' . sprintf( __( '%s - We recommend setting memory to at least 64MB. See: %s', 'nexthemes-plugin' ), size_format( $memory ), '<a href="http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP" target="_blank">' . __( 'Increasing memory allocated to PHP', 'nexthemes-plugin' ) . '</a>' ) . '</mark>';
                    } else {
                        echo '<mark class="yes">' . size_format( $memory ) . '</mark>';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td data-export-label="WP Debug Mode"><?php _e( 'WP Debug Mode', 'nexthemes-plugin' ); ?>:</td>
                <td class="help"><?php echo nth_help_tip(__( 'Displays whether or not WordPress is in Debug Mode.', 'nexthemes-plugin' ));?></td>
                <td><?php if ( defined('WP_DEBUG') && WP_DEBUG ) echo '<mark class="yes">&#10004;</mark>'; else echo '<mark class="no">&ndash;</mark>'; ?></td>
            </tr>
            <tr>
                <td data-export-label="Language"><?php _e( 'Language', 'nexthemes-plugin' ); ?>:</td>
                <td class="help"><?php echo nth_help_tip(__( 'The current language used by WordPress. Default = English', 'nexthemes-plugin' ));?></td>
                <td><?php echo get_locale() ?></td>
            </tr>
            </tbody>
        </table>

        <table class="nth_status_table widefat" cellspacing="0">
            <thead>
            <tr>
                <th colspan="3" data-export-label="Server Environment"><?php _e( 'Server Environment', 'nexthemes-plugin' ); ?></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td data-export-label="Server Info"><?php _e( 'Server Info', 'nexthemes-plugin' ); ?>:</td>
                <td class="help"><?php echo nth_help_tip(__( 'Information about the web server that is currently hosting your site.', 'nexthemes-plugin' ));?></td>
                <td><?php echo esc_html( $_SERVER['SERVER_SOFTWARE'] ); ?></td>
            </tr>
            <tr>
                <td data-export-label="PHP Version"><?php _e( 'PHP Version', 'nexthemes-plugin' ); ?>:</td>
                <td class="help"><?php echo nth_help_tip(__( 'The version of PHP installed on your hosting server.', 'nexthemes-plugin' ));?></td>
                <td><?php
                    if( function_exists('phpversion') ) {
                        $php_version = phpversion();
                        echo '<mark class="yes">' . esc_html( $php_version ) . '</mark>';
                    } else {
                        _e( "Couldn't determine PHP version because phpversion() doesn't exist.", 'nexthemes-plugin' );
                    }
                    ?></td>
            </tr>
            <?php if ( function_exists( 'ini_get' ) ) : ?>
                <tr>
                    <td data-export-label="PHP Post Max Size"><?php _e( 'PHP Post Max Size', 'nexthemes-plugin' ); ?>:</td>
                    <td class="help"><?php echo nth_help_tip(__( 'The largest filesize that can be contained in one post.', 'nexthemes-plugin' ));?></td>
                    <td><?php echo size_format( nth_let_to_num( ini_get( 'post_max_size' ) ) ); ?></td>
                </tr>
                <tr>
                    <td data-export-label="PHP Time Limit"><?php _e( 'PHP Time Limit', 'nexthemes-plugin' ); ?>:</td>
                    <td class="help"><?php echo nth_help_tip(__( 'The amount of time (in seconds) that your site will spend on a single operation before timing out (to avoid server lockups)', 'nexthemes-plugin' ));?></td>
                    <td><?php
                        $time_limit = ini_get( 'max_execution_time' );
                        if ( $time_limit < 300 && $time_limit != 0 ) {
                            echo '<mark class="error">' . sprintf( __( '%s - We recommend setting max execution time to at least 300. See: <a href="%s" target="_blank">Increasing max execution to PHP</a>', 'nexthemes-plugin' ), $time_limit, 'http://codex.wordpress.org/Common_WordPress_Errors#Maximum_execution_time_exceeded' ) . '</mark>';
                        } else {
                            echo '<mark class="yes">' . $time_limit . '</mark>';
                        }
                        ?></td>
                </tr>
                <tr>
                    <td data-export-label="PHP Max Input Vars"><?php _e( 'PHP Max Input Vars', 'nexthemes-plugin' ); ?>:</td>
                    <td class="help"><?php echo nth_help_tip(__( 'The maximum number of variables your server can use for a single function to avoid overloads.', 'nexthemes-plugin' ));?></td>
                    <td><?php
                        $max_input_vars = ini_get( 'max_input_vars' );
                        if ( absint($max_input_vars) < 2000 ) {
                            echo '<mark class="error">' . sprintf( __( '%s - We recommend setting max input vars to at least 2000. See: <a href="%s" target="_blank">Increasing max input vars limit.</a>', 'Avada' ), $max_input_vars, 'https://www.google.com/#q=max_input_vars' ) . '</mark>';
                        } else {
                            echo '<mark class="yes">' . $max_input_vars . '</mark>';
                        }
                        ?></td>
                </tr>
                <tr>
                    <td data-export-label="SUHOSIN Installed"><?php _e( 'SUHOSIN Installed', 'nexthemes-plugin' ); ?>:</td>
                    <td class="help"><?php echo nth_help_tip(__( 'Suhosin is an advanced protection system for PHP installations. It was designed to protect your servers on the one hand against a number of well known problems in PHP applications and on the other hand against potential unknown vulnerabilities within these applications or the PHP core itself. If enabled on your server, Suhosin may need to be configured to increase its data submission limits.', 'nexthemes-plugin' ));?></td>
                    <td><?php echo extension_loaded( 'suhosin' ) ? '&#10004;' : '&ndash;'; ?></td>
                </tr>
            <?php endif;?>

            <tr>
                <td data-export-label="ZipArchive"><?php _e( 'ZipArchive', 'nexthemes-plugin' ); ?>:</td>
                <td class="help"><?php echo nth_help_tip(__( 'ZipArchive is required for importing demos. They are used to import and export zip files specifically for sliders.', 'nexthemes-plugin' ));?></td>
                <td><?php
                    if( class_exists( 'ZipArchive' ) ) {
                        echo '<mark class="yes">&#10004;</mark>';
                    } else {
                        echo '<mark class="error">'.__('ZipArchive is not installed on your server, but is required if you need to import demo content', 'nexthemes-plugin').'</mark>';
                    }
                    ?></td>
            </tr>
            <tr>
                <td data-export-label="MySQL Version"><?php _e( 'MySQL Version', 'nexthemes-plugin' ); ?>:</td>
                <td class="help"><?php echo nth_help_tip(__( 'The version of MySQL installed on your hosting server.', 'nexthemes-plugin' ));?></td>
                <td><?php
                    global $wpdb;
                    echo $wpdb->db_version();
                    ?></td>
            </tr>
            <tr>
                <td data-export-label="Max Upload Size"><?php _e( 'Max Upload Size', 'nexthemes-plugin' ); ?>:</td>
                <td class="help"><?php echo nth_help_tip(__( 'The largest filesize that can be uploaded to your WordPress installation.', 'nexthemes-plugin' ));?></td>
                <td><?php echo size_format( wp_max_upload_size() ); ?></td>
            </tr>
            <tr>
                <td data-export-label="DOMDocument"><?php _e( 'DOMDocument', 'nexthemes-plugin' ); ?>:</td>
                <td class="help"><?php echo nth_help_tip(__( 'HTML/Multipart emails use DOMDocument to generate inline CSS in templates.', 'nexthemes-plugin' ));?></td>
                <td><?php
                    if ( class_exists( 'DOMDocument' ) ) {
                        echo '<mark class="yes">&#10004;</mark>';
                    } else {
                        echo '<mark class="error">'.__('Your server does not have the DOMDocument class enabled - HTML/Multipart emails, and also some extensions, will not work without DOMDocument.', 'nexthemes-plugin').'</mark>';
                    }
                    ?></td>
            </tr>
            </tbody>
        </table>


        <?php
        $active_plugins = (array) get_option( 'active_plugins', array() );
        if( is_multisite() ) {
            $active_sitewide_plugins = (array) get_site_option( 'active_sitewide_plugins', array() );
            $active_plugins = array_merge( $active_plugins, $active_sitewide_plugins );
        }
        ?>
        <table class="nth_status_table widefat" cellspacing="0">
            <thead>
            <tr>
                <th colspan="3" data-export-label="Active Plugins"><?php printf( __( 'Active Plugins (%d)', 'nexthemes-plugin' ), count($active_plugins) ); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $stt = 1;
            foreach ( $active_plugins as $plugin ):
                $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin ); ?>

                <?php if( !empty( $plugin_data['Title'] ) ):
                ?>
                <tr>
                    <td data-export-label="<?php echo esc_attr($plugin_data['name'])?>"><?php echo $plugin_data['Title'];?></td>
                    <td class="help">&nbsp;</td>
                    <td><?php echo sprintf( __( 'by %s &ndash; %s', 'nexthemes-plugin' ), $plugin_data['Author'], esc_html( $plugin_data['Version'] ));?></td>
                </tr>
                <?php endif;?>

            <?php endforeach;?>
            </tbody>
        </table>

        <?php
        $import_arrs = Nexthemes_Importer::getThemeHomepages();

        $home_k = isset($import_arrs['homepages'])? $import_arrs['homepages']: array();

        $homes_installed = (array) @unserialize(get_option('nth_theme_imported', array()));
        $home_current = get_option('nth_theme_current', false);
        $__preview_url = 'http://demo.nexthemes.com/wordpress/theshopier/';

        if(count($home_k) > 0):
        ?>
        <table class="nth_status_table widefat" cellspacing="0">
            <thead>
            <tr>
                <th colspan="3" data-export-label="Demo homepage"><?php printf( __( 'Demo homepage (%s)', 'nexthemes-plugin' ), count($home_k) ); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach( $home_k as $k => $home ):
                $_home_name = sprintf('<a href="%1$s/" target="_blank" title="%2$s">%3$s</a>', esc_url( $__preview_url.'home'.$k ), __('Preview this homepage', 'nexthemes-plugin' ), esc_html($home['name']));
                ?>
                <tr>
                    <td data-export-label="<?php echo esc_attr($home['name'])?>"><?php echo $_home_name;?></td>
                    <td class="help">&nbsp;</td>
                    <td><?php
                        if( in_array($k, $homes_installed) ) {
                            echo '<mark class="yes">&#10004;</mark>';
                            if( $home_current && absint($home_current) === absint($k) ) echo ' <mark class="yes">( ' . __('Currently applying', 'nexthemes-plugin').' )</mark>';
                        } else {
                            echo '<mark class="no">&ndash;</mark>';
                        }
                        ?></td>
                </tr>
            <?php endforeach;?>
            </tbody>
        </table>

        <?php endif;?>
    </div>

</div>
