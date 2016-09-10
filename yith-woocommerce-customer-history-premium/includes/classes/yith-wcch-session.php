<?php

defined( 'ABSPATH' ) or exit;

/*
 *  YITH WooCommerce Customer History Session
 */

if ( ! class_exists( 'YITH_WCCH_Session' ) ) {

    class YITH_WCCH_Session {

        public $id              = 0;
        public $user_id         = '';
        public $url             = '';
        public $reg_date        = '0000-00-00 00:00:00';
        public $del             = 0;

        /*
         *  Constructor
         */

        public function __construct( $id = 0 ) {

            global $wpdb;

            if ( $id > 0 ) {

                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}yith_wcch_sessions WHERE id='$id'" );

                if ( isset( $row ) && $row->id == $id ) {

                    $this->id               = $row->id;
                    $this->user_id          = $row->user_id;
                    $this->url              = $row->url;
                    $this->reg_date         = $row->reg_date;
                    $this->del              = $row->del;

                }

            }
            
        }

        public static function insert( $user_id, $url ) {

            if ( $user_id >= 0 && $url != '' ) {

                var_dump('expression-'. $user_id);

                global $wpdb;
                $wpdb->hide_errors();

                $sql = "INSERT INTO {$wpdb->prefix}yith_wcch_sessions (id,user_id,url,reg_date,del) VALUES ('',$user_id,'$url',CURRENT_TIMESTAMP,'0')";
                $wpdb->query( $sql );

            }

        }

        public static function create_tables() {

            /*
             *  Check if dbDelta() exists
             */

            if ( ! function_exists( 'dbDelta' ) ) { require_once( ABSPATH . 'wp-admin/includes/upgrade.php' ); }

            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();

            $create = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}yith_wcch_sessions (
                        id              BIGINT(20) NOT NULL AUTO_INCREMENT,
                        user_id         BIGINT(20),
                        url             VARCHAR(250),
                        reg_date        TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
                        del             TINYINT(1) NOT NULL DEFAULT '0',
                        PRIMARY KEY     (id)
                    ) $charset_collate;";
            $result = dbDelta( $create );

        }

    }

}