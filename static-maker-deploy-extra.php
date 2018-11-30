<?php
/**
 * Plugin Name:     Static Maker Deploy Extra
 * Plugin URI:      https://github.com/ameyamashiro/static-maker-deploy-extra
 * Description:     Static Maker addon for manual deploying
 * Author:          ameyamashiro
 * Author URI:      https://github.com/ameyamashiro
 * Text Domain:     static-maker-deploy-extra
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Static_Maker_Deploy_Extra
 */

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FilterHandler;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\RotatingFileHandler;
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
    // static maker config
    $static_maker_class->features['rsync'] = false;

    // i18n
    load_plugin_textdomain(STATIC_MAKER_DEPLOY_EXTRA, false, dirname(plugin_basename(__FILE__)) . '/languages/');

    $builder = new DI\ContainerBuilder();
    $builder->addDefinitions([
        'Monolog\Logger' => function (Static_Maker\Deploy_Extra\Option $option) {
            $log = new Logger('Static Maker Deploy Extra');

            if (WP_DEBUG) {
                $stream = new RotatingFileHandler(get_home_path() . '/sm-logs/static-maker-deploy-extra.log', 0, Logger::DEBUG);
            } else {
                $stream = new RotatingFileHandler(get_home_path() . '/sm-logs/static-maker-deploy-extra.log', 0, Logger::NOTICE);
            }
            $log->pushHandler($stream);

            if ($option->get_notification_emails()) {
                $noticeEmail = new NativeMailerHandler($option->get_notification_emails(), $option->get_subject(__('Notice', STATIC_MAKER_DEPLOY_EXTRA)), get_option('admin_email'), Logger::NOTICE);
                $errorEmail = new NativeMailerHandler($option->get_notification_emails(), __('Fatal Error Occurred', STATIC_MAKER_DEPLOY_EXTRA), get_option('admin_email'), Logger::ERROR);

                $format_template = "%channel%.%level_name%\n\n";
                $format_template .= __('ID', STATIC_MAKER_DEPLOY_EXTRA) . ": %context.id%\n";
                $format_template .= __('Record Time', STATIC_MAKER_DEPLOY_EXTRA) . ": %datetime%\n";
                $format_template .= __('Reserve Time', STATIC_MAKER_DEPLOY_EXTRA) . ": %context.date%\n";
                $format_template .= __('Timestamp', STATIC_MAKER_DEPLOY_EXTRA) . ": %context.timestamp%\n";
                $format_template .= __('Type', STATIC_MAKER_DEPLOY_EXTRA) . ": %context.type%\n";
                $format_template .= __('Created', STATIC_MAKER_DEPLOY_EXTRA) . ": %context.created_at%\n";
                $format_template .= __('Rsync Code', STATIC_MAKER_DEPLOY_EXTRA) . ": %context.rsync_code%\n";
                $format_template .= __('Rsync Output', STATIC_MAKER_DEPLOY_EXTRA) . ": %context.rsync_output%\n";
                $line_formatter = new LineFormatter($format_template);
                $line_formatter->allowInlineLineBreaks();

                $noticeEmail->setFormatter($line_formatter);
                $errorEmail->setFormatter($line_formatter);

                $log->pushHandler(new FilterHandler($noticeEmail, [Logger::NOTICE]));
                $log->pushHandler($errorEmail);
            }

            return $log;
        },
    ]);

    $container = $builder->build();

    $smde = $container->get('Static_Maker\Deploy_Extra\Deploy_Extra');

    add_action('static_maker_loaded', [$smde, 'load']);

    add_action('admin_init', [$smde, 'options_update']);
    add_action('admin_enqueue_scripts', [$smde, 'enqueue_scripts']);

    // cron actions
    add_action('smde_schedule_handler', [$smde->cron, 'cron_schedule_handler']);
    // add_action('static_maker_dequeue', [$smde->cron, 'cron_schedule_handler']);

    // ajax endpoints
    add_action('wp_ajax_static-maker-deploy-extra-schedule_deploy', [$smde->ajax, 'ajax_schedule_deploy']);
    add_action('wp_ajax_static-maker-deploy-extra-partial_schedule_deploy', [$smde->ajax, 'ajax_partial_schedule_deploy']);
    add_action('wp_ajax_static-maker-deploy-extra-unschedule_deploy', [$smde->ajax, 'ajax_unschedule_deploy']);
    add_action('wp_ajax_static-maker-deploy-extra-ajax_download_production_data', [$smde->ajax, 'ajax_download_production_data']);
    add_action('wp_ajax_static-maker-deploy-extra-ajax_get_current_diffs', [$smde->ajax, 'ajax_get_current_diffs']);

}

// activation and deactivation hooks

function activate_hook($network_wide)
{
    activate_hook_function($network_wide);
}

function deactivate_hook($network_wide)
{
    deactivate_hook_function($network_wide);
}

add_action('static_maker_before_init', 'load_static_maker_deploy_extra_basics');

register_activation_hook(__FILE__, 'activate_hook_function');
register_deactivation_hook(__FILE__, 'deactivate_hook_function');
