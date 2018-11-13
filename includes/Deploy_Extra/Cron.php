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
        $this->rsync->sync_remote($timestamp);

        $this->db->update_status($deploy, 'completed');

        $this->log->notice(__('deployed'), $deploy);
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
