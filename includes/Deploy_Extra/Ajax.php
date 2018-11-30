<?php

namespace Static_Maker\Deploy_Extra;

use Monolog\Logger;

class Ajax
{
    private $diff;
    private $rsync;
    private $cron;
    private $db;
    private $revision;
    private $log;

    public function __construct(
        Diff $diff,
        Rsync $rsync,
        Cron $cron,
        DB $db,
        Revision $revision,
        Logger $log
    ) {
        $this->diff = $diff;
        $this->rsync = $rsync;
        $this->cron = $cron;
        $this->db = $db;
        $this->revision = $revision;
        $this->log = $log;
    }

    public function ajax_partial_schedule_deploy()
    {
        check_ajax_referer('partial_schedule_deploy');

        $date = $_POST['date'] ?? null;
        $time = $_POST['time'] ?? null;
        $files = $_POST['files'] ?? null;
        $now = $_POST['now'] ?? false;

        if ($now) {
            $d = explode(' ', date('Y-m-d H:i'));
            $date = $d[0];
            $time = $d[1];
        }

        if (!$date || !$time || !$files) {
            $this->log->warning(__('ajax_partial_schedule_deploy: please set required parameters', STATIC_MAKER_DEPLOY_EXTRA));
            wp_die(__('please set required parameters', STATIC_MAKER_DEPLOY_EXTRA), '', 422);
        }

        $timestamp = strtotime($date . ' ' . $time);

        if (!$this->revision->make_revision_from_production($timestamp, $files)) {
            $this->log->error(__('ajax_partial_schedule_deploy: failed to create revision from production', STATIC_MAKER_DEPLOY_EXTRA, [
                'timestamp' => $timestamp,
                'files' => $files,
            ]));
            wp_die(__('failed to create a revision', STATIC_MAKER_DEPLOY_EXTRA), '', 500);
        }

        wp_schedule_single_event($timestamp, 'smde_schedule_handler', [intval($timestamp)]);

        $this->log->info(__('ajax_partial_schedule_deploy: succeeded', STATIC_MAKER_DEPLOY_EXTRA));

        wp_die();
    }

    public function ajax_schedule_deploy()
    {
        check_ajax_referer('schedule_deploy');

        $date = $_POST['date'] ?? null;
        $time = $_POST['time'] ?? null;
        $deploy_id = $_POST['deploy'] ?? null;
        $now = $_POST['now'] ?? false;

        if ($now) {
            $d = explode(' ', date('Y-m-d H:i'));
            $date = $d[0];
            $time = $d[1];
        }

        if (!$date || !$time) {
            $this->log->warning(__('ajax_schedule_deploy: please set required parameters', STATIC_MAKER_DEPLOY_EXTRA));
            wp_die(__('please set required parameters', STATIC_MAKER_DEPLOY_EXTRA), '', 422);
        }

        $timestamp = strtotime($date . ' ' . $time);

        // accept one schedule at a time
        if (count($this->cron->get_cron_schedules()) > 0) {
            $this->log->warning(__('ajax_schedule_deploy: Another deployment is running or already reserved', STATIC_MAKER_DEPLOY_EXTRA));
            wp_die(__('Another deployment is running or already reserved', STATIC_MAKER_DEPLOY_EXTRA), '', 422);
        }

        // create a new revision for the specified deploy_id
        if ($deploy_id) {
            $deploy = $this->db->fetch_deploy($deploy_id);
            $revision_timestamp = $deploy['timestamp'];
            // $revision_timestamp = $this->db->fetch_timestamp_by_id($deploy_id);

            if (!$revision_timestamp) {
                $this->log->warning(__('ajax_schedule_deploy: the deploy of the specified timestamp doesn\'t exist', STATIC_MAKER_DEPLOY_EXTRA));
                wp_die(__('the deploy of the specified timestamp doesn\'t exist', STATIC_MAKER_DEPLOY_EXTRA));
            }

            if ($deploy['type'] === 'whole') {
                if (!$this->revision->make_revision_from_existing($deploy['type'], $revision_timestamp, $timestamp)) {
                    $this->log->error(__('ajax_schedule_deploy: failed to create revision from production', STATIC_MAKER_DEPLOY_EXTRA, [
                        'timestamp' => $timestamp,
                        '$deploy_id' => $deploy_id,
                        'deploy' => $deploy,
                    ]));
                    wp_die(__('failed to create a revision', STATIC_MAKER_DEPLOY_EXTRA), '', 500);
                }
            } else {
                $files = $this->db->fetch_partial_deploy_file_list($deploy_id);
                if (!$this->revision->make_revision_from_existing($deploy['type'], $revision_timestamp, $timestamp, $files)) {
                    $this->log->error(__('ajax_schedule_deploy: failed to create a revision', STATIC_MAKER_DEPLOY_EXTRA, [
                        'timestamp' => $timestamp,
                        '$deploy_id' => $deploy_id,
                        'deploy' => $deploy,
                    ]));
                    wp_die(__('failed to create a revision', STATIC_MAKER_DEPLOY_EXTRA), '', 500);
                }
            }
        } else {
            if (!$this->revision->make_revision($timestamp)) {
                $this->log->error(__('ajax_schedule_deploy: failed to create a revision', STATIC_MAKER_DEPLOY_EXTRA, [
                    'timestamp' => $timestamp,
                    '$deploy_id' => $deploy_id,
                    'deploy' => $deploy,
                ]));
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
            $this->log->warning(__('ajax_unschedule_deploy: please set timestamp to unschedule', STATIC_MAKER_DEPLOY_EXTRA));
            wp_die(__('please set timestamp to unschedule', STATIC_MAKER_DEPLOY_EXTRA), '', 422);
        }

        // if (!$this->revision->remove_revision($timestamp)) {
        //     wp_die(__('failed to remove revision', STATIC_MAKER_DEPLOY_EXTRA), '', 500);
        // }

        wp_unschedule_event($timestamp, 'smde_schedule_handler', [intval($timestamp)]);

        $this->db->update_status_by_timestamp($timestamp, 'canceled');

        $this->log->info(__('ajax_unschedule_deploy', STATIC_MAKER_DEPLOY_EXTRA, [
            'timestamp' => $timestamp,
        ]));

        wp_die();
    }

    public function ajax_download_production_data()
    {
        check_ajax_referer('download_production_data');

        $ret = $this->rsync->download_production_data();

        if ($ret['code'] !== 0) {
            $this->log->error(__('Rsync from production failed', STATIC_MAKER_DEPLOY_EXTRA), [
                'rsync_code' => $ret['code'],
                'rsync_output' => $ret['output'],
            ]);
            wp_die(__('failed to download', STATIC_MAKER_DEPLOY_EXTRA), '', 500);
        }

        $this->log->info(__('ajax_download_production_data: succeeded', STATIC_MAKER_DEPLOY_EXTRA));

        wp_die();
    }

    public function ajax_get_current_diffs()
    {
        check_ajax_referer('get_current_diffs');

        $ret = $this->rsync->download_production_data();

        if ($ret['code'] !== 0) {
            $this->log->error(__('Rsync from production failed', STATIC_MAKER_DEPLOY_EXTRA), [
                'rsync_code' => $ret['code'],
                'rsync_output' => $ret['output'],
            ]);
            wp_die(__('failed to download', STATIC_MAKER_DEPLOY_EXTRA), '', 500);
        }

        wp_send_json($this->diff->get_diff_list());

        $this->log->info(__('ajax_get_current_diffs: succeeded', STATIC_MAKER_DEPLOY_EXTRA));

        wp_die();
    }
}
