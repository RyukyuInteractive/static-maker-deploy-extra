<?php

namespace Static_Maker\Deploy_Extra;

class Option
{
    public function get_option()
    {
        return get_option(STATIC_MAKER_DEPLOY_EXTRA);
    }

    public function get_notification_emails()
    {
        $emails_string = $this->get_option()['deploy_notify_email'] ?? '';

        if (!$emails_string) {
            return [];
        }

        return explode(',', $emails_string);
    }

    public function get_subject($subject = null)
    {
        $options = $this->get_option();

        if (!isset($options['deploy_notify_email_subject']) || !$options['deploy_notify_email_subject']) {
            return $subject;
        }

        return str_replace('%subject%', $subject, $options['deploy_notify_email_subject']);
    }

    public function is_configured()
    {
        $opts = $this->get_option();

        if (!empty($opts['deploy_type'])) {
            switch ($opts['deploy_type']) {
                case 'rsync':
                    return ($this->is_set($opts, 'remote_ssh_key') || $this->is_set($opts, 'remote_ssh_key_path')) &&
                        $this->is_set($opts, 'remote_user') &&
                        $this->is_set($opts, 'remote_dir') &&
                        $this->is_set($opts, 'remote_host');
                case 's3':
                    return $this->is_set($opts, 's3_bucket') &&
                        $this->is_set($opts, 's3_bucket_source') &&
                        getenv('AWS_ACCESS_KEY_ID', false) &&
                        getenv('AWS_SECRET_ACCESS_KEY', false);
                default:
                    break;
            }
        }


        return false;
    }

    private function is_set($opts, $key)
    {
        return isset($opts[$key]) && !empty($opts[$key]);
    }
}
