<?php

namespace Static_Maker\Deploy_Extra;

class File
{
    public function recurse_rm($dir)
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

    public function recurse_copy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recurse_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
//                    copy($src . '/' . $file, $dst . '/' . $file);
                    shell_exec("cp -rp $src/$file $dst/$file");
                }
            }
        }
        closedir($dir);
    }

    public function copy_partial_files($src_base, $dst_base, $files)
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
//            copy($src, $dst);
            shell_exec("cp -rp $src $dst");
        }
        return true;
    }

    public function remove_partial_files($tgt_base, $files)
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

    public function create_dir($export_path)
    {
        if (!is_dir($export_path)) {
            if (!mkdir($export_path, 0755, true)) {
                return false;
            }
        }
        return true;
    }

    /**
     * replace all strings of files in the $directory, and export them to $export_to
     *
     * TODO: remove if unnecessary
     */
    public function replace_all_files_in_path($search, $replace, $directory, $export_to)
    {
        try {
            if (!realpath($directory)) {
                $this->create_dir($directory);
                $directory = realpath($directory);
            }
            if (!realpath($export_to)) {
                $this->create_dir($export_to);
                $export_to = realpath($export_to);
            }

            $it = new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {
                var_dump($file->getRealPath());
                $export_path = $export_to . DIRECTORY_SEPARATOR . str_replace($directory, '', $file->getRealPath());
                if ($file->isDir()) {
                    @mkdir($export_path);
                    continue;
                }

                $file_contents = file_get_contents($file->getRealPath());
                // TODO: get replace values from options
                $file_contents = str_replace('localhost', 'localhost:5050', $file_contents);
                file_put_contents($export_path, $file_contents);
            }
            // rmdir($directory);
        } catch (\UnexpectedValueException $e) {
            // No such file or directory, return true
            return true;
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
