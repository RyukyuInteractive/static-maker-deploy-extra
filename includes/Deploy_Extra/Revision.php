<?php

namespace Static_Maker\Deploy_Extra;

class Revision
{
    private $db;
    private $path;
    private $file;
    private $rsync;
    private $static_maker;

    public function __construct(DB $db, Path $path, File $file, Rsync $rsync, Static_Maker $static_Maker)
    {
        $this->db = $db;
        $this->path = $path;
        $this->file = $file;
        $this->rsync = $rsync;
        $this->static_maker = $static_Maker;
    }

    public function remove_revision($timestamp = null)
    {
        if (!$timestamp) {
            return false;
        }

        return $this->file->recurse_rm($this->path->get_revision_path($timestamp));
    }

    public function make_revision($timestamp = null)
    {
        if (!$timestamp) {
            return false;
        }

        if (!$this->path->create_revision_dir($timestamp)) {
            return false;
        }

        $revision_path = $this->path->get_revision_path($timestamp);

        if (!$revision_path) {
            return false;
        }

        // copy the current static files into the revision directyory

        $file_util = $this->static_maker->file_util;
        $static_src_dir = $file_util::get_output_path();

        $this->file->recurse_copy($static_src_dir, $revision_path);

        // save deploy info to the table

        $this->db->insert_whole_deploy([
            'date' => date('Y-m-d H:i:s', $timestamp),
            'timestamp' => $timestamp,
            'type' => 'whole',
            'status' => 'waiting',
        ]);

        return true;
    }

    public function make_revision_from_production($timestamp, $files, $latest = true)
    {
        if (!$timestamp) {
            return false;
        }

        if (!$this->path->create_revision_dir($timestamp)) {
            return false;
        }

        $revision_path = $this->path->get_revision_path($timestamp);

        if (!$revision_path) {
            return false;
        }

        if ($latest && !$this->rsync->download_production_data()) {
            return false;
        }

        $prod_path = $this->path->get_local_production_path();
        $file_util = $this->static_maker->file_util;
        $static_src_path = $file_util::get_output_path();

        // copy current production files into the revision dir
        $this->file->recurse_copy($prod_path, $revision_path);

        // apply partial files to the revision dir
        if (!$this->file->copy_partial_files($static_src_path, $revision_path, $files)) {
            return false;
        }
        if (!$this->file->remove_partial_files($revision_path, $files)) {
            return false;
        }

        $this->db->insert_partial_deploy([
            'date' => date('Y-m-d H:i:s', $timestamp),
            'timestamp' => $timestamp,
            'type' => 'partial',
            'status' => 'waiting',
        ], $files);

        return true;
    }

    public function make_revision_from_existing($timestamp)
    {
        if (!$timestamp) {
            return false;
        }

        if (!$this->path->exists_revision($timestamp)) {
            return false;
        }

        $this->db->insert_partial_deploy([
            'date' => date('Y-m-d H:i:s', $timestamp),
            'timestamp' => $timestamp,
            'type' => 'whole',
            'status' => 'waiting',
        ], $files);

        return true;
    }
}
