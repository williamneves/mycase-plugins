<?php

$settings = array(

    'email' => array(

            'section_email_settings'     => array(
                'name' => __( 'Email Template', 'ywrac' ),
                'type' => 'title',
                'id'   => 'ywrac_section_general'
            ),

            'email_sender_name' => array(
                'name'    =>  __( 'Email Sender Name', 'ywrac' ),
                'desc'    => '',
                'id'      => 'ywrac_email_sender_name',
                'type'    => 'text',
                'default' => get_bloginfo('name')
            ),

            'email_sender' => array(
                'name'    =>  __( 'Email Sender', 'ywrac' ),
                'desc'    => '',
                'id'      => 'ywrac_email_sender',
                'type'    => 'text',
                'default' => get_bloginfo('admin_email')
            ),

            'email_subject' => array(
                'name'    =>  __( 'Email Subject', 'ywrac' ),
                'desc'    => '',
                'id'      => 'ywrac_email_subject',
                'type'    => 'text',
                'css'      => 'min-width:300px;',
                'default' => ''
            ),

            'email_content' => array(
                'name'    => __( 'Email content', 'ywrac' ),
                'desc'    => '',
                'id'      => 'ywrac_email_content',
                'type'    => 'ywrac_additional_textarea',
                'default' => '',
            ),

            'section_end_form'=> array(
                'type'              => 'sectionend',
                'id'                => 'ywrac_section_general_end_form'
            ),
        )

);

return apply_filters( 'yith_ywrac_panel_settings_options', $settings );