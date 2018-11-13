<?php
/**
 * Plugin Name:     Static Maker Deploy Extra
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          YOUR NAME HERE
 * Author URI:      YOUR SITE HERE
 * Text Domain:     static-maker-deploy-extra
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Static_Maker_Deploy_Extra
 */

use Monolog\Handler\FilterHandler;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/Deploy_Extra/Activation_Deactivation_Hooks.php';

define('STATIC_MAKER_DEPLOY_EXTRA', 'static-maker-deploy-extra');
define('STATIC_MAKER_DEPLOY_EXTRA_ABSPATH', __DIR__);
define('STATIC_MAKER_DEPLOY_EXTRA_ENTRY_FILE', __FILE__);
define('STATIC_MAKER_DEPLOY_EXTRA_DEPLOY_LIST_TABLE_NAME', get_list_table_name());
define('STATIC_MAKER_DEPLOY_EXTRA_DEPLOY_DIFF_TABLE_NAME', get_diff_table_name());

function load_static_maker_deploy_extra_basics($static_maker_class)
{
    $builder = new DI\ContainerBuilder();
    $builder->addDefinitions([
        'Monolog\Logger' => function (Static_Maker\Deploy_Extra\Option $option) {
            $log = new Logger('SMDE');

            $stream = new StreamHandler(get_home_path() . 'static-maker-deploy-extra.log', Logger::NOTICE);
            if (WP_DEBUG) {
                $stream = new StreamHandler(get_home_path() . '/static-maker-deploy-extra.log', Logger::DEBUG);
            }
            $log->pushHandler($stream);

            if ($option->get_notification_email()) {
                $noticeEmail = new NativeMailerHandler($option->get_notification_email(), __('Notice', STATIC_MAKER_DEPLOY_EXTRA), get_option('admin_email'), Logger::NOTICE);
                $errorEmail = new NativeMailerHandler($option->get_notification_email(), __('Fatal Error Occurred', STATIC_MAKER_DEPLOY_EXTRA), get_option('admin_email'), Logger::ERROR);
                $log->pushHandler(new FilterHandler($noticeEmail, [Logger::NOTICE]));
                $log->pushHandler($errorEmail);
            }

            return $log;
        },
    ]);

    $container = $builder->build();

    $static_maker_class->features['rsync'] = false;

    $smde = $container->get('Static_Maker\Deploy_Extra\Deploy_Extra');

    add_action('static_maker_loaded', [$smde, 'load']);

    add_action('admin_init', [$smde, 'options_update']);
    add_action('admin_enqueue_scripts', [$smde, 'enqueue_scripts']);

    // cron actions
    add_action('smde_schedule_handler', [$smde->cron, 'cron_schedule_handler']);

    // ajax endpoints
    add_action('wp_ajax_static-maker-deploy-extra-schedule_deploy', [$smde->ajax, 'ajax_schedule_deploy']);
    add_action('wp_ajax_static-maker-deploy-extra-partial_schedule_deploy', [$smde->ajax, 'ajax_partial_schedule_deploy']);
    add_action('wp_ajax_static-maker-deploy-extra-unschedule_deploy', [$smde->ajax, 'ajax_unschedule_deploy']);
    add_action('wp_ajax_static-maker-deploy-extra-ajax_download_production_data', [$smde->ajax, 'ajax_download_production_data']);
    add_action('wp_ajax_static-maker-deploy-extra-ajax_get_current_diffs', [$smde->ajax, 'ajax_get_current_diffs']);
}

add_action('static_maker_before_init', 'load_static_maker_deploy_extra_basics');

// activation and deactivation hooks

function activate_hook($network_wide)
{
    activate_hook_function($network_wide);
}

function deactivate_hook($network_wide)
{
    deactivate_hook_function($network_wide);
}

register_activation_hook(__FILE__, 'activate_hook_function');
register_deactivation_hook(__FILE__, 'deactivate_hook_function');
