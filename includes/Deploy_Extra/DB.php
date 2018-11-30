<?php

namespace Static_Maker\Deploy_Extra;

class DB
{
    public static function get_list_table_name()
    {
        return STATIC_MAKER_DEPLOY_EXTRA_DEPLOY_LIST_TABLE_NAME;
    }

    public static function get_diff_table_name()
    {
        return STATIC_MAKER_DEPLOY_EXTRA_DEPLOY_DIFF_TABLE_NAME;
    }

    public function insert_whole_deploy($data)
    {
        global $wpdb;
        $table = $this->get_list_table_name();
        return $wpdb->insert($table, $data);
    }

    public function insert_partial_deploy($deploy, $files)
    {
        global $wpdb;
        $list_table = $this->get_list_table_name();

        // save deploy data
        $wpdb->insert($list_table, $deploy);

        // save files data
        $deploy_id = $wpdb->insert_id;
        $diff_table = $this->get_diff_table_name();

        $query = 'INSERT INTO ' . $diff_table . ' (foreign_id, file_path, action) VALUES ';
        $place_holders = [];
        $values = [];
        foreach ($files as $file) {
            array_push($values, intval($deploy_id), $file['file_path'], $file['action']);
            array_push($place_holders, " (%d, '%s', '%s')");
        }
        $query .= implode(', ', $place_holders);
        return $wpdb->query($wpdb->prepare($query, $values));
    }

    public function update_status($deploy, $status)
    {
        global $wpdb;

        $id = $deploy['id'];
        $table = $this->get_list_table_name();
        return $wpdb->update($table, ['status' => $status], ['id' => $id]) === 1;
    }

    public function update_status_by_timestamp($timestamp, $status)
    {
        global $wpdb;

        $deploy = $this->fetch_waiting_deploy_by_timestamp($timestamp);

        if (!$deploy) {
            return false;
        }

        $id = $deploy['id'];
        $table = $this->get_list_table_name();
        return $wpdb->update($table, ['status' => $status], ['id' => $id]) === 1;
    }

    public function fetch_latest_deploy_of_timestamp($timestamp)
    {
        global $wpdb;
        $sql = 'SELECT * FROM ' . $this->get_list_table_name() . 'WHERE timestamp = %s ORDER BY id DESC LIMIT 1';
        return $wpdb->get_row($wpdb->prepare($sql, $timestamp), ARRAY_A);
    }

    public function fetch_waiting_deploy_by_timestamp($timestamp)
    {
        global $wpdb;
        $sql = 'SELECT * FROM ' . $this->get_list_table_name() . ' WHERE status = "waiting" AND timestamp = %s ORDER BY id DESC LIMIT 1';
        return $wpdb->get_row($wpdb->prepare($sql, $timestamp), ARRAY_A);
    }

    public function fetch_deploy($id)
    {
        global $wpdb;
        $sql = 'SELECT * FROM ' . $this->get_list_table_name() . ' WHERE id = %d';
        return $wpdb->get_row($wpdb->prepare($sql, $id), ARRAY_A);
    }

    public function fetch_timestamp_by_id($id)
    {
        global $wpdb;
        $sql = 'SELECT timestamp FROM ' . $this->get_list_table_name() . ' WHERE id = %d';
        return $wpdb->get_var($wpdb->prepare($sql, [$id]));
    }

    public function fetch_deploy_files($opts)
    {
        global $wpdb;
        $id = $opts['id'];
        $current_page = $opts['current_page'] ?? 0;
        $total_items = $opts['total_items'] ?? 0;
        $per_page = $opts['per_page'] ?? 15;
        $columns = $opts['columns'] ?? [];
        $orderby = $opts['orderby'] ?? null;
        $order = $opts['order'] ?? null;

        $prepare_values = [$id];

        $search_queries = [];
        if (isset($_POST['s']) && $_POST['s'] !== '') {
            $search_string = '%' . $_POST['s'] . '%';
            $search_query = ' WHERE ';
            foreach ($columns as $key => $val) {
                array_push($search_queries, "$key LIKE \"%s\"");
                array_push($prepare_values, $search_string);
            }
        }

        $query = 'SELECT * FROM ' . $this->get_diff_table_name() . ' WHERE foreign_id = %d';

        if ($search_queries) {
            $query .= ' AND ' . implode(' OR ', $search_queries);
        }

        $offset = ($current_page - 1) * $per_page;
        $query .= " LIMIT %d  OFFSET %d";
        array_push($prepare_values, $per_page, $offset);

        // if the orderby is available, validate orderby is in the columns name
        if ($orderby && $order && isset($columns[$orderby]) && $this->is_valid_for_order($order)) {
            $query .= " ORDER BY $orderby $order ";
        }

        return $wpdb->get_results($wpdb->prepare($query, $prepare_values), ARRAY_A);
    }

    public function fetch_deploy_list($opts)
    {
        global $wpdb;
        $current_page = $opts['current_page'] ?? 0;
        $total_items = $opts['total_items'] ?? 0;
        $per_page = $opts['per_page'] ?? 15;
        $columns = $opts['columns'] ?? [];
        $orderby = $opts['orderby'] ?? null;
        $order = $opts['order'] ?? null;

        $prepare_values = [];

        $search_queries = [];
        $search_values = [];
        if (isset($_POST['s']) && $_POST['s'] !== '') {
            $search_string = '%' . $_POST['s'] . '%';
            $search_query = ' WHERE ';
            foreach ($columns as $key => $val) {
                array_push($search_queries, "$key LIKE \"%s\"");
                array_push($prepare_values, $search_string);
            }
        }

        $query = 'SELECT * FROM ' . $this->get_list_table_name() . ' WHERE deleted = 0 ';
        if ($search_queries) {
            $query .= ' AND (' . implode(' OR ', $search_queries) . ')';
        }

        // if the orderby is available, validate orderby is in the columns name
        if ($orderby && $order && isset($columns[$orderby]) && $this->is_valid_for_order($order)) {
            $query .= " ORDER BY $orderby $order ";
        }

        $query .= " LIMIT %d  OFFSET %d";
        $offset = ($current_page - 1) * $per_page;
        array_push($prepare_values, $per_page, $offset);

        $query = $wpdb->prepare($query, $prepare_values);
        return $wpdb->get_results($query, ARRAY_A);
    }

    public function fetch_deploy_list_total_items()
    {
        global $wpdb;
        return $wpdb->get_var('SELECT COUNT(*) FROM ' . $this->get_list_table_name() . ' WHERE deleted = 0');
    }

    public function fetch_partial_deploy_file_list($deploy_id)
    {
        global $wpdb;
        $query = 'SELECT file_path, action FROM ' . $this->get_diff_table_name() . ' WHERE foreign_id = %d';
        $query = $wpdb->prepare($query, $deploy_id);
        return $wpdb->get_results($query, ARRAY_A);
    }

    public function is_valid_for_order($name)
    {
        return strtolower($name) === 'asc' || strtolower($name) === 'desc';
    }

    public function soft_delete_deploy_by_ids($ids)
    {
        global $wpdb;
        $table = $this->get_list_table_name();

        $place_holders = [];
        foreach ($ids as $id) {
            $place_holders[] = '%d';
        }

        $sql = "UPDATE $table SET deleted = 1 WHERE id in (" . implode(', ', $place_holders) . ')';
        return $wpdb->query($wpdb->prepare($sql, $ids));
    }
}
