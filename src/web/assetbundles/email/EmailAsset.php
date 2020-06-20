<?php

namespace barrelstrength\sproutbase\web\assetbundles\email;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class EmailAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbaseassetbundles/email/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/NotificationEvent.js',
            'js/SproutModal.js',
        ];

        $this->css = [
            'css/sproutemail.css',
            'css/modal.css',
            'css/charts-explorer.css',
        ];

        parent::init();
    }
}