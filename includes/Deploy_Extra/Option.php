<?php

namespace Static_Maker\Deploy_Extra;

class Option
{
    public function get_option()
    {
        return get_option(STATIC_MAKER_DEPLOY_EXTRA);
    }

    public function get_notification_email()
    {
        return $this->get_option()['deploy_notify_email'] ?? false;
    }
}
