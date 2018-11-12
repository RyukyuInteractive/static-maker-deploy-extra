<?php

namespace Static_Maker\Deploy_Extra;

class Path
{

    public function get_revision_path($timestamp = null)
    {
        if (!$timestamp) {
            return false;
        }

        $rawpath = get_home_path() . 'static-maker-deploy-extra/' . $timestamp . '/';
        $realpath = realpath($rawpath);

        if ($realpath) {
            return $realpath . '/';
        }

        return $rawpath;
    }

    public function get_local_production_path($realpath = true)
    {
        if ($realpath) {
            return realpath(get_home_path() . 'static-maker-deploy-extra/production/');
        } else {
            return get_home_path() . 'static-maker-deploy-extra/production/';
        }
    }

    /**
     * TODO: remove if unnecessary
     */
    public function get_local_production_replaced_path($realpath = true)
    {
        if ($realpath) {
            return realpath(get_home_path() . 'static-maker-deploy-extra/production-replaced/');
        } else {
            return get_home_path() . 'static-maker-deploy-extra/production-replaced/';
        }
    }

    public function create_revision_dir($timestamp = null)
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

    public function exists_revision($timestamp)
    {
        return is_dir($this->get_revision_path($timestamp));
    }

}
