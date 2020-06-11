<?php

namespace barrelstrength\sproutbase\web\assetbundles\copypastemailer;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class CopyPasteMailerAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbaseassetbundles/copypastemailer/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/CopyPaste.js'
        ];

        $this->css = [
            'css/copy-paste.css'
        ];

        parent::init();
    }
}