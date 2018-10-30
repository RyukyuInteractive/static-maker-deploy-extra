<?php

namespace Static_Maker\Deploy_Extra;

class Cron
{
    private $db;
    private $rsync;

    public function __construct(DB $db, Rsync $rsync)
    {
        $this->db = $db;
        $this->rsync = $rsync;
    }

    public function cron_schedule_handler($timestamp)
    {
        $deploy = $this->db->fetch_waiting_deploy_by_timestamp($timestamp);

        if (!$deploy) {
            return;
        }

        // update deploy status
        if (!$this->db->update_status($deploy, 'processing')) {
            return;
        }

        // process deploy
        $this->rsync->sync_remote($timestamp);

//        ob_flush();
        //        ob_start();
        //        var_dump($deploy);
        //        file_put_contents(ABSPATH . '/hoge', ob_get_flush());

        $this->db->update_status($deploy, 'completed');
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
