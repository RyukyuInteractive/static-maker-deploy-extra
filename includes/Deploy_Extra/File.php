<?php

namespace Static_Maker\Deploy_Extra;

class File
{
	function recurse_rm($dir)
	{
		try {
			$it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
			$files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);

			foreach ($files as $file) {
				if ($file->isDir()) {
					rmdir($file->getRealPath());
				} else {
					unlink($file->getRealPath());
				}
			}
			rmdir($dir);
		} catch (\UnexpectedValueException $e) {
			// No such file or directory, return true
			return true;
		} catch (\Exception $e) {
			return false;
		}

		return true;
	}

	function recurse_copy($src, $dst)
	{
		$dir = opendir($src);
		@mkdir($dst);
		while (false !== ($file = readdir($dir))) {
			if (($file != '.') && ($file != '..')) {
				if (is_dir($src . '/' . $file)) {
					$this->recurse_copy($src . '/' . $file, $dst . '/' . $file);
				} else {
//					copy($src . '/' . $file, $dst . '/' . $file);
					shell_exec("cp -rp $src/$file $dst/$file");
				}
			}
		}
		closedir($dir);
	}

	function copy_partial_files($src_base, $dst_base, $files)
	{
		foreach ($files as $file => $data) {
			if ($data['status'] === 'deleted') {
				continue;
			}
			$src = realpath("$src_base/$file");

			$dst_pathinfo = pathinfo("$dst_base/$file");
			var_dump($dst_pathinfo);
			$dst_dir = realpath($dst_pathinfo['dirname']);

			if (!$dst_dir) {
				mkdir($dst_pathinfo['dirname'], 0777, true);
			}
			$dst = "$dst_base/$file";

			if (!$src || !$dst) {
				return false;
			}
//			copy($src, $dst);
			shell_exec("cp -rp $src $dst");
		}
		return true;
	}

	function remove_partial_files($tgt_base, $files)
	{
		foreach ($files as $file => $data) {
			if ($data['status'] !== 'deleted') {
				continue;
			}

			$path = realpath("$tgt_base/$file");

			if (!$path) {
				continue;
			}

			unlink($path);
		}
		return true;
	}

	function create_dir($export_path)
	{
		if (!is_dir($export_path)) {
			if (!mkdir($export_path, 0755, true)) {
				return false;
			}
		}
		return true;
	}
}
