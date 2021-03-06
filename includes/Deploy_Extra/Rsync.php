<?php

namespace Static_Maker\Deploy_Extra;

class Rsync
{
    private $file;
    private $path;
    private $option;
    private $static_maker;

    public function __construct(File $file, Path $path, Option $option, Static_Maker $static_maker)
    {
        $this->file = $file;
        $this->path = $path;
        $this->option = $option;
        $this->static_maker = $static_maker;
    }

    public function sync_remote($timestamp, $dry_run = false)
    {
        $crypto_util = $this->static_maker->crypto_util;
        $option = $this->option->get_option();
        $host = $option['remote_host'];
        $user = $option['remote_user'];
        $dst = $option['remote_dir'];
        $credential = $crypto_util::decrypt($option['remote_ssh_key'], true);
        $remote_ssh_key_path = $crypto_util::decrypt($option['remote_ssh_key_path'], true);

        $revision_path = $this->path->get_revision_path($timestamp);

        if (!empty($credential)) {
            $temp = tmpfile();
            $remote_ssh_key_path = stream_get_meta_data($temp)['uri'];
            fwrite($temp, $credential);
        }

        $options = "-Parcvv --delete -e 'ssh -i $remote_ssh_key_path -o StrictHostKeyChecking=no'";
        $options = apply_filters( 'static_maker_deploy_extra_sync_options', $options, '', '' );

        if ($dry_run) {
            $options .= ' -n';
        }

        $rsync_command = "rsync $options $revision_path $user@$host:$dst 2>&1";

        exec($rsync_command, $out, $code);

        if(!empty($credential)) {
            fclose($temp);
        }

        return [
            'output' => $out,
            'code' => $code,
        ];
    }

    public function download_production_data($dry_run = false)
    {
        $crypto_util = $this->static_maker->crypto_util;
        $option = $this->option->get_option();
        $host = $option['remote_host'];
        $user = $option['remote_user'];
        $dst = $option['remote_dir'];
        $credential = $crypto_util::decrypt($option['remote_ssh_key'], true);
        $remote_ssh_key_path = $crypto_util::decrypt($option['remote_ssh_key_path'], true);

        $local_path = $this->path->get_local_production_path();

        if (!$local_path) {
            if (!$this->file->create_dir($this->path->get_local_production_path(false))) {
                return false;
            }
            $local_path = $this->path->get_local_production_path();
        }

        if (!empty($credential)) {
            $temp = tmpfile();
            $remote_ssh_key_path = stream_get_meta_data($temp)['uri'];
            fwrite($temp, $credential);
        }

        $options = "-Parcvv --delete -e 'ssh -i $remote_ssh_key_path -o StrictHostKeyChecking=no'";
        $options = apply_filters( 'static_maker_deploy_extra_sync_options', $options, '', '' );

        if ($dry_run) {
            $options .= ' -n';
        }

        if (substr($dst, -1) !== '/') {
            $dst .= '/';
        }

        $rsync_command = "rsync $options $user@$host:$dst $local_path 2>&1";

        exec($rsync_command, $out, $code);

        if(!empty($credential)){
            fclose($temp);
        }

        return [
            'output' => $out,
            'code' => $code,
        ];
    }
}
