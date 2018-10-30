<?php

namespace Static_Maker\Deploy_Extra;

class Diff
{
    private $path;
    private $static_maker;

    public function __construct(Path $path, Static_Maker $static_maker)
    {
        $this->path = $path;
        $this->static_maker = $static_maker;
    }

    public function get_diff_list($timestamp = null)
    {
        try {
            // production
            $prd_path = $this->path->get_local_production_path();
            if ($timestamp) {
                $rev_path = $this->path->get_revision_path($timestamp);
            } else {
                $rev_path = realpath($this->static_maker->file_util::get_output_path());
            }
            $rev_list = [];
            $prd_list = [];
            $diffs = [];

            // make production file list

            $it = new \RecursiveDirectoryIterator($prd_path, \RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);

            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $prd_list[str_replace($prd_path, '', $file->getRealPath())] = $file;
                }
            }

            // make revision file list

            $it = new \RecursiveDirectoryIterator($rev_path, \RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);

            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $rev_list[str_replace($rev_path, '', $file->getRealPath())] = $file;
                }
            }

            // diff (modified and added)

            foreach ($rev_list as $rev_key => $rev_file) {
                if ($rev_file->getBasename() === '.DS_Store') {
                    continue;
                }

                if (isset($prd_list[$rev_key])) {
                    $prd_file = $prd_list[$rev_key];

                    $time_diff = $rev_file->getMTime() === $prd_file->getMTime();
                    $size_diff = $rev_file->getSize() === $prd_file->getSize();

                    if (!$time_diff || !$size_diff) {
                        array_push($diffs, [
                            'file' => $rev_key,
                            'status' => 'modified',
                            'time_diff' => !$time_diff,
                            'size_diff' => !$size_diff,
                            'times' => [$rev_file->getMTime(), $prd_file->getMTime()],
                            'sizes' => [$rev_file->getSize(), $prd_file->getSize()],
                            'rev_path' => $rev_path,
                            'prod_path' => $prd_path,
                        ]);
                    }

                } else {
                    array_push($diffs, [
                        'file' => $rev_key,
                        'status' => 'added',
                    ]);
                }
            }

            // diff (deleted)

            foreach ($prd_list as $prd_key => $prd_file) {
                if ($prd_file->getBasename() === '.DS_Store') {
                    continue;
                }

                if (!isset($rev_list[$prd_key])) {
                    array_push($diffs, [
                        'file' => $prd_key,
                        'status' => 'deleted',
                    ]);
                }
            }

            return $diffs;
        } catch (\Exception $e) {
            return false;
        }
    }

}
