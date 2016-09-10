<?php
// This file is based on wp-includes/js/tinymce/langs/wp-langs.php

if ( ! defined( 'ABSPATH' ) )
    exit;

if ( ! class_exists( '_WP_Editors' ) )
    require( ABSPATH . WPINC . '/class-wp-editor.php' );

function ywrac_tinymce_plugin_translation() {
    $strings = array(
        'firstname'   => __( 'User First Name', 'yrac' ),
        'lastname'    => __( 'User Last Name', 'yrac' ),
        'username'    => __( 'User Name', 'yrac' ),
        'cartcontent' => __( 'Cart Content', 'yrac' ),
    );

    $locale = _WP_Editors::$mce_locale;
    $translated = 'tinyMCE.addI18n("' . $locale . '.tc_button", ' . json_encode( $strings ) . ");\n";

    return $translated;
}

$strings = ywrac_tinymce_plugin_translation();