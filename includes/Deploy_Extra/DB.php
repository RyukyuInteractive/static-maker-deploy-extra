<?php

namespace Static_Maker\Deploy_Extra;

class DB
{
	public $list_table_name = STATIC_MAKER_DEPLOY_EXTRA_DEPLOY_LIST_TABLE_NAME;
	public $diff_table_name = STATIC_MAKER_DEPLOY_EXTRA_DEPLOY_DIFF_TABLE_NAME;

	public function __construct()
	{
	}

	function insert_whole_deploy($data)
	{
		global $wpdb;
		$table = $this->list_table_name;
		return $wpdb->insert($table, $data);
	}

	function insert_partial_deploy($deploy, $files)
	{
		global $wpdb;
		$list_table = $this->list_table_name;

		// save deploy data
		$wpdb->insert($list_table, $deploy);

		// save files data
		$deploy_id = $wpdb->insert_id;
		$diff_table = $this->diff_table_name;

		$query = 'INSERT INTO ' . $diff_table . ' (foreign_id, file_path, action) VALUES ';
		$place_holders = [];
		$values = [];
		foreach ($files as $file) {
			array_push($values, $deploy_id, $file['file'], $file['status']);
			array_push($place_holders, " ('%d', '%s', '%s')");
		}
		$query .= implode(', ', $place_holders);
		return $wpdb->query($wpdb->prepare($query, $values));
	}

	function update_status($deploy, $status)
	{
		global $wpdb;

		$id = $deploy['id'];
		$table = $this->list_table_name;
		return $wpdb->update($table, ['status' => $status], ['id' => $id]) === 1;
	}

	// TODO: 使わなければ消す
	function update_status_by_timestamp($timestamp, $status)
	{
		global $wpdb;

		$deploy = $this->fetch_waiting_deploy_by_timestamp($timestamp);

		if (!$deploy) {
			return false;
		}

		$id = $deploy['id'];
		$table = $this->list_table_name;
		return $wpdb->update($table, ['status' => $status], ['id' => $id]) === 1;
	}

	function fetch_latest_deploy_of_timestamp($timestamp)
	{
		global $wpdb;
		$sql = "SELECT * FROM $this->list_table_name WHERE timestamp = %s ORDER BY id DESC LIMIT 1";
		return $wpdb->get_row($wpdb->prepare($sql, $timestamp), ARRAY_A);
	}

	function fetch_waiting_deploy_by_timestamp($timestamp)
	{
		global $wpdb;
		$sql = "SELECT * FROM $this->list_table_name WHERE status = 'waiting' AND timestamp = %s ORDER BY id DESC LIMIT 1";
		return $wpdb->get_row($wpdb->prepare($sql, $timestamp), ARRAY_A);
	}
}
