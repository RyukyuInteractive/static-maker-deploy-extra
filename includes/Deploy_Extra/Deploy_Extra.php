<?php

namespace Static_Maker\Deploy_Extra;

use Monolog\Logger;

class Deploy_Extra
{
    public $db;
    public $diff;
    public $path;
    public $rsync;
    public $file;
    public $cron;
    public $revision;
    public $ajax;
    public $log;
    public $static_maker;
    public $option;

    public function __construct(
        DB $db,
        Diff $diff,
        Path $path,
        Rsync $rsync,
        File $file,
        Cron $cron,
        Revision $revision,
        Ajax $ajax,
        Logger $log,
        Option $option,
        Static_Maker $static_maker
    ) {
        $this->db = $db;
        $this->diff = $diff;
        $this->path = $path;
        $this->rsync = $rsync;
        $this->file = $file;
        $this->cron = $cron;
        $this->revision = $revision;
        $this->ajax = $ajax;
        $this->log = $log;
        $this->option = $option;
        $this->static_maker = $static_maker;
    }

    public function load()
    {
        add_action('init', [$this, 'init']);
    }

    public function init()
    {
        add_action('static_maker_menu_configure', [$this, 'menu']);
    }

    public function menu($slug)
    {
        $cap = 'manage_options';

        add_submenu_page(
            $slug,
            __('Deploy', STATIC_MAKER_DEPLOY_EXTRA),
            __('Deploy', STATIC_MAKER_DEPLOY_EXTRA),
            $cap,
            $slug . '_deploy_extra_main',
            [$this, 'display_admin_menu_main']
        );

        add_submenu_page(
            $slug,
            __('Deploy List', STATIC_MAKER_DEPLOY_EXTRA),
            __('Deploy List', STATIC_MAKER_DEPLOY_EXTRA),
            $cap,
            $slug . '_deploy_extra_list',
            [$this, 'display_admin_menu_list']
        );

        add_submenu_page(
            $slug,
            __('Deploy Settings', STATIC_MAKER_DEPLOY_EXTRA),
            __('Deploy Settings', STATIC_MAKER_DEPLOY_EXTRA),
            $cap,
            $slug . '_deploy_extra_configuration',
            [$this, 'display_admin_menu_configure']
        );
    }

    /**
     * load scripts for the specific page
     *
     * @param $hook
     */
    public function enqueue_scripts($hook)
    {
        wp_register_script('smde_hyperapp', plugins_url('', STATIC_MAKER_DEPLOY_EXTRA_ENTRY_FILE) . '/res/js/hyperapp.js');
        wp_enqueue_script('smde_hyperapp');
        wp_register_script('smde_components', plugins_url('', STATIC_MAKER_DEPLOY_EXTRA_ENTRY_FILE) . '/res/js/components.js');
        wp_enqueue_script('smde_components');
        wp_register_script('smde_actions', plugins_url('', STATIC_MAKER_DEPLOY_EXTRA_ENTRY_FILE) . '/res/js/actions.js');
        wp_localize_script('smde_actions', 'DeployExtraMessages', [
            'Schedule Deploy' => __('Schedule Deploy', STATIC_MAKER_DEPLOY_EXTRA),
            'Date' => __('Date', STATIC_MAKER_DEPLOY_EXTRA),
            'Time' => __('Time', STATIC_MAKER_DEPLOY_EXTRA),
            'Deploy' => __('Deploy', STATIC_MAKER_DEPLOY_EXTRA),
            'Deploy All Files' => __('Deploy All Files', STATIC_MAKER_DEPLOY_EXTRA),
            'Specify Files to Deploy' => __('Specify Files to Deploy', STATIC_MAKER_DEPLOY_EXTRA),
            'Compare Diff' => __('Compare Diff', STATIC_MAKER_DEPLOY_EXTRA),
            'Next' => __('Next', STATIC_MAKER_DEPLOY_EXTRA),
            'Back' => __('Back', STATIC_MAKER_DEPLOY_EXTRA),
            'succeeded' => __('succeeded', STATIC_MAKER_DEPLOY_EXTRA),
            'failed' => __('failed', STATIC_MAKER_DEPLOY_EXTRA),
            'no timestamp' => __('no timestamp', STATIC_MAKER_DEPLOY_EXTRA),
            'Status' => __('Status', STATIC_MAKER_DEPLOY_EXTRA),
            'modified' => __('modified', STATIC_MAKER_DEPLOY_EXTRA),
            'added' => __('added', STATIC_MAKER_DEPLOY_EXTRA),
            'deleted' => __('deleted', STATIC_MAKER_DEPLOY_EXTRA),
            'Reserve' => __('Reserve', STATIC_MAKER_DEPLOY_EXTRA),
            'Reserve at current time' => __('Reserve at current time', STATIC_MAKER_DEPLOY_EXTRA),
            'Missing Required Arguments' => __('Missing Required Arguments', STATIC_MAKER_DEPLOY_EXTRA),
            'The new deployment has been reserved' => __('The new deployment has been reserved', STATIC_MAKER_DEPLOY_EXTRA),
            'The production is latest' => __('The production is latest', STATIC_MAKER_DEPLOY_EXTRA),
        ]);
        wp_enqueue_script('smde_actions');

        wp_enqueue_style('smde-common-style', plugins_url('', STATIC_MAKER_DEPLOY_EXTRA_ENTRY_FILE) . '/res/css/common.css');

        wp_localize_script('smde_actions', 'scheduleDeployData', [
            'action' => 'static-maker-deploy-extra-schedule_deploy',
            'url' => wp_nonce_url(admin_url('admin-ajax.php'), 'schedule_deploy'),
        ]);
        wp_localize_script('smde_actions', 'partialScheduleDeployData', [
            'action' => 'static-maker-deploy-extra-partial_schedule_deploy',
            'url' => wp_nonce_url(admin_url('admin-ajax.php'), 'partial_schedule_deploy'),
        ]);

        switch ($hook) {
            case 'static-maker_page_static-maker_deploy_extra_main':

                wp_register_script('smde_deploy', plugins_url('', STATIC_MAKER_DEPLOY_EXTRA_ENTRY_FILE) . '/res/js/deploy.js');
                wp_enqueue_script('smde_deploy');

                wp_localize_script('smde_deploy', 'downloadProductionData', [
                    'action' => 'static-maker-deploy-extra-ajax_download_production_data',
                    'url' => wp_nonce_url(admin_url('admin-ajax.php'), 'download_production_data'),
                ]);
                wp_localize_script('smde_deploy', 'getCurrentDiffsData', [
                    'action' => 'static-maker-deploy-extra-ajax_get_current_diffs',
                    'url' => wp_nonce_url(admin_url('admin-ajax.php'), 'get_current_diffs'),
                ]);

                break;
            case 'static-maker_page_static-maker_deploy_extra_list':

                wp_register_script('smde_deploy_detail', plugins_url('', STATIC_MAKER_DEPLOY_EXTRA_ENTRY_FILE) . '/res/js/detail.js');
                wp_enqueue_script('smde_deploy_detail');

                if (isset($_GET['deploy'])) {
                    $deploy = $this->db->fetch_deploy($_GET['deploy']);
                    $exists = $this->path->exists_revision($deploy['timestamp']) ? 1 : 0;
                    $files = $this->db->fetch_partial_deploy_file_list($deploy['id']);

                    wp_localize_script('smde_deploy_detail', 'currentDeployData', array_merge($deploy, ['exists' => $exists]));
                    wp_localize_script('smde_deploy_detail', 'currentDeployDiffData', $files);
                }

                wp_localize_script('smde_deploy_detail', 'unscheduleDeployData', [
                    'action' => 'static-maker-deploy-extra-unschedule_deploy',
                    'url' => wp_nonce_url(admin_url('admin-ajax.php'), 'unschedule_deploy'),
                ]);

                wp_enqueue_style('smde-list-style', plugins_url('', STATIC_MAKER_DEPLOY_EXTRA_ENTRY_FILE) . '/res/css/list.css');
                break;
        }
    }

    public function display_admin_menu_main()
    {
        include_once STATIC_MAKER_DEPLOY_EXTRA_ABSPATH . '/views/admin-display-main.php';
    }

    public function display_admin_menu_list()
    {
        if (isset($_GET['deploy'])) {
            include_once STATIC_MAKER_DEPLOY_EXTRA_ABSPATH . '/views/admin-display-detail.php';
        } else {
            include_once STATIC_MAKER_DEPLOY_EXTRA_ABSPATH . '/views/admin-display-list.php';
        }
    }

    public function display_admin_menu_configure()
    {
        include_once STATIC_MAKER_DEPLOY_EXTRA_ABSPATH . '/views/admin-display-configure.php';
    }

    public function options_update()
    {
        register_setting(STATIC_MAKER_DEPLOY_EXTRA, STATIC_MAKER_DEPLOY_EXTRA, [$this, 'validate']);
    }

    public function validate($input)
    {
        if (isset($input['remote_ssh_key']) && !empty($input['remote_ssh_key'])) {
            $crypto_util = $this->static_maker->crypto_util;
            $input['remote_ssh_key'] = $crypto_util::encrypt($input['remote_ssh_key'], true);
        }

        return $input;
    }
}
