<?php

function get_list_table_name()
{
    global $wpdb;
    return $wpdb->prefix . 'staticmaker_de_lists';
}

function get_diff_table_name()
{
    global $wpdb;
    return $wpdb->prefix . 'staticmaker_de_diffs';
}

function activate_hook_function($network_wide)
{
    global $wpdb;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    if (is_multisite() && $network_wide) {
        foreach ($wpdb->get_col("SELECT blog_id FROM $wpdb->blogs") as $blog_id) {
            switch_to_blog($blog_id);

            _create_list_table();
            _create_diff_table();

            restore_current_blog();
        }
    } else {
        _create_list_table();
        _create_diff_table();
    }
}

function deactivate_hook_function($network_wide)
{
}

function _create_list_table()
{
    global $wpdb;

    $table_name = get_list_table_name();
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
			  id int(20) NOT NULL AUTO_INCREMENT,
			  deploy_user bigint(20) DEFAULT 0,
			  date datetime,
			  timestamp VARCHAR(30),
			  type VARCHAR(30),
			  status VARCHAR(30),
              deleted tinyint(1) DEFAULT 0,
              created_at TIMESTAMP NOT NULL DEFAULT NOW(),
              updated_at TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE now(),
			  PRIMARY KEY (id)
			) $charset_collate";

    dbDelta($sql);
}

function _create_diff_table()
{
    global $wpdb;

    $table_name = get_diff_table_name();
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
			  id int(20) NOT NULL AUTO_INCREMENT,
			  foreign_id int(20),
			  file_path VARCHAR(512),
			  action VARCHAR(30),
              created_at TIMESTAMP NOT NULL DEFAULT NOW(),
              updated_at TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE now(),
			  PRIMARY KEY (id)
			) $charset_collate";

    dbDelta($sql);
}
