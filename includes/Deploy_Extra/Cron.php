<?php

namespace Static_Maker\Deploy_Extra;

use Monolog\Logger;

class Cron
{
    private $db;
    private $rsync;
    private $log;

    public function __construct(DB $db, Rsync $rsync, Logger $log)
    {
        $this->db = $db;
        $this->rsync = $rsync;
        $this->log = $log;
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
        $ret = $this->rsync->sync_remote($timestamp);

        $output_data = array_merge($deploy, [
            'rsync_code' => $ret['code'],
            'rsync_output' => $ret['output'],
        ]);

        if ($ret['code'] !== 0) {
            $this->db->update_status($deploy, 'failed');
            $this->log->error(__('deployed', STATIC_MAKER_DEPLOY_EXTRA), $output_data);
        } else {
            $this->db->update_status($deploy, 'completed');
            $this->log->notice(__('deployed', STATIC_MAKER_DEPLOY_EXTRA), $output_data);
        }
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
