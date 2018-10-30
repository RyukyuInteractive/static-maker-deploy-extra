<?php

namespace Static_Maker\Deploy_Extra;

class Path
{

	function get_revision_path($timestamp = null)
	{
		if (!$timestamp) {
			return false;
		}

		$rawpath = wp_upload_dir()['basedir'] . '/static-maker-deploy-extra/' . $timestamp . '/';
		$realpath = realpath($rawpath);

		if ($realpath) {
			return $realpath . '/';
		}

		return $rawpath;
	}

	function get_local_production_path($realpath = true)
	{
		if ($realpath) {
			return realpath(wp_upload_dir()['basedir'] . '/static-maker-deploy-extra/production/');
		} else {
			return wp_upload_dir()['basedir'] . '/static-maker-deploy-extra/production/';
		}
	}

	function create_revision_dir($timestamp = null)
	{
		$revision_path = $this->get_revision_path($timestamp);

		if (!$timestamp || !$revision_path) {
			return false;
		}

		if (!is_dir($revision_path)) {
			if (!mkdir($revision_path, 0755, true)) {
				return false;
			}
		}
		return true;
	}
}
