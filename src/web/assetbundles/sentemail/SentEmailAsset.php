<?php

namespace barrelstrength\sproutbase\web\assetbundles\sentemail;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @todo - is this class in use?
 */
class SentEmailAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbaseassetbundles/sentemail/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->css = [
            'css/sent-email.css',
        ];

        parent::init();
    }
}