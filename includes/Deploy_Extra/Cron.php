<?php

namespace Static_Maker\Deploy_Extra;

use Monolog\Logger;

class Cron
{
    private $db;
    private $rsync;
    private $aws;
    private $log;
    private $revision;
    private $option;

    public function __construct(DB $db, Rsync $rsync, Logger $log, Revision $revision, AWS $aws, Option $option)
    {
        $this->db = $db;
        $this->rsync = $rsync;
        $this->aws = $aws;
        $this->log = $log;
        $this->revision = $revision;
        $this->option = $option->get_option();
    }

    public function cron_schedule_handler($timestamp)
    {
        $this->log->info(__('cron_schedule_handler'), ['timestamp' => $timestamp]);

        $deploy = $this->db->fetch_waiting_deploy_by_timestamp($timestamp);

        if (!$deploy) {
            $this->log->info(__('no deploy found'), ['timestamp' => $timestamp]);
            return;
        }

        // update deploy status
        if (!$this->db->update_status($deploy, 'processing')) {
            $this->log->warning(__('can not update deploy status'), $deploy);
            return;
        }

        // process deploy
        if(!empty($this->option['deploy_type'])){
            switch ($this->option['deploy_type']){
                case 's3':
                    $ret = $this->aws->s3_sync_remote($timestamp);
                    break;
                case 'rsync':
                default:
                    $ret = $this->rsync->sync_remote($timestamp);
                    break;
            }

            $output_data = array_merge($deploy, [
                $this->option['deploy_type'].'_code' => $ret['code'],
                $this->option['deploy_type'].'_output' => $ret['output'],
            ]);

            if ($ret['code'] !== 0) {
                $this->db->update_status($deploy, 'failed');
                $this->log->error(__('deployed', STATIC_MAKER_DEPLOY_EXTRA), $output_data);
            } else {
                $this->db->update_status($deploy, 'completed');
                $this->log->notice(__('deployed', STATIC_MAKER_DEPLOY_EXTRA), $output_data);
            }
        } else {
            $this->log->warning(__('not set deploy type'), $deploy);

        }
    }

    public function cron_remove_old_deploy_file_handler()
    {
        $ret =  $this->revision->remove_old_deploy_file();
        $this->log->notice(__('cron_remove_old_deploy_file_handler'), $ret);
    }

    public function get_cron_schedules()
    {
        return array_filter(get_option('cron'), array($this, 'filter_to_smde_crons'));
    }

    public function filter_to_smde_crons($cron)
    {
        if (isset($cron['smde_schedule_handler'])) {
            return true;
        }
        return false;
    }
}
