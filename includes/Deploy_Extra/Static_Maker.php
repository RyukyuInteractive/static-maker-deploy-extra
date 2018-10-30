<?php

namespace Static_Maker\Deploy_Extra;

class Static_Maker
{
    public $file_util;
    public $crypto_util;

    public function __construct()
    {
        $this->file_util = new \Static_Maker\FileUtil();
        $this->crypto_util = new \Static_Maker\CryptoUtil();
    }

}
