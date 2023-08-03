<?php

function mccf_install() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'mccf_submissions';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        name tinytext NOT NULL,
        email varchar(100) NOT NULL,
        message text NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

function mccf_uninstall() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'mccf_submissions';

    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}
