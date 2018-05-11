<?php

namespace barrelstrength\sproutbase\app\email\web\assets\base;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class NotificationAsset extends AssetBundle
{
    public function init()
    {
        $this->depends = [
            CpAsset::class,
        ];

        $this->sourcePath = '@sproutbase/app/email/web/assets/base/dist';

        $this->js = [
            'js/notification.js',
            'js/sproutmodal.js'
        ];

        $this->css = [
            'css/modal.css'
        ];

        parent::init();
    }
}