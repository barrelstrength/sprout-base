<?php

namespace barrelstrength\sproutbase\app\email\web\assets\email;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class EmailAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbase/app/email/web/assets/email/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/notification.js',
            'js/sprout-modal.js'
        ];

        $this->css = [
            'css/sproutemail.css',
            'css/modal.css',
            'css/charts-explorer.css',
        ];

        parent::init();
    }
}