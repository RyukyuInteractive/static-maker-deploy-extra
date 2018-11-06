<?php

namespace Static_Maker\Deploy_Extra;

class Ajax
{
    private $diff;
    private $rsync;
    private $cron;
    private $db;
    private $revision;

    public function __construct(
        Diff $diff,
        Rsync $rsync,
        Cron $cron,
        DB $db,
        Revision $revision
    ) {
        $this->diff = $diff;
        $this->rsync = $rsync;
        $this->cron = $cron;
        $this->db = $db;
        $this->revision = $revision;
    }

    public function ajax_partial_schedule_deploy()
    {
        check_ajax_referer('partial_schedule_deploy');

        $date = $_POST['date'] ?? null;
        $time = $_POST['time'] ?? null;
        $files = $_POST['files'] ?? null;

        if (!$date || !$time || !$files) {
            wp_die(__('please set required parameters', STATIC_MAKER_DEPLOY_EXTRA), '', 422);
        }

        $timestamp = strtotime($date . ' ' . $time);

        if (!$this->revision->make_revision_from_production($timestamp, $files)) {
            wp_die(__('failed to create a revision', STATIC_MAKER_DEPLOY_EXTRA), '', 500);
        }

        wp_schedule_single_event($timestamp, 'smde_schedule_handler', [intval($timestamp)]);

        wp_die();
    }

    public function ajax_schedule_deploy()
    {
        check_ajax_referer('schedule_deploy');

        $date = $_POST['date'] ?? null;
        $time = $_POST['time'] ?? null;
        $deploy = $_POST['deploy'] ?? null;

        if (!$date || !$time) {
            wp_die(__('please set required parameters', STATIC_MAKER_DEPLOY_EXTRA), '', 422);
        }

        // accept one schedule at a time
        if (count($this->cron->get_cron_schedules()) > 0) {
            wp_die(__('the schedule is already reserved', STATIC_MAKER_DEPLOY_EXTRA), '', 422);
        }

        $timestamp = strtotime($date . ' ' . $time);

        // create revision for `timestamp`
        if ($deploy) {
            $revision_timestamp = $this->db->fetch_timestamp_by_id($deploy);

            if (!$revision_timestamp) {return false;}

            if (!$this->revision->make_revision_from_existing($revision_timestamp)) {
                wp_die(__('failed to create a revision', STATIC_MAKER_DEPLOY_EXTRA), '', 500);
            }
        } else {
            if (!$this->revision->make_revision($timestamp)) {
                wp_die(__('failed to create a revision', STATIC_MAKER_DEPLOY_EXTRA), '', 500);
            }
        }

        wp_schedule_single_event($timestamp, 'smde_schedule_handler', [intval($timestamp)]);

        wp_die();
    }

    public function ajax_unschedule_deploy()
    {
        check_ajax_referer('unschedule_deploy');

        $timestamp = $_POST['timestamp'] ?? null;

        if (!$timestamp) {
            wp_die(__('please set timestamp to unschedule', STATIC_MAKER_DEPLOY_EXTRA), '', 422);
        }

        if (!$this->revision->remove_revision($timestamp)) {
            wp_die(__('failed to remove revision', STATIC_MAKER_DEPLOY_EXTRA), '', 500);
        }

        wp_unschedule_event($timestamp, 'smde_schedule_handler', [intval($timestamp)]);

        wp_die();
    }

    public function ajax_download_production_data()
    {
        check_ajax_referer('download_production_data');

        if (!$this->rsync->download_production_data()) {
            wp_die(__('failed to download', STATIC_MAKER_DEPLOY_EXTRA), '', 500);
        }

        wp_die();
    }

    public function ajax_get_current_diffs()
    {
        check_ajax_referer('get_current_diffs');

        if (!$this->rsync->download_production_data()) {
            wp_die(__('failed to download', STATIC_MAKER_DEPLOY_EXTRA), '', 500);
        }

        wp_send_json($this->diff->get_diff_list());

        wp_die();
    }
}
