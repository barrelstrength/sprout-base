<?php

namespace barrelstrength\sproutbase\app\import\web\assets\import;

use barrelstrength\sproutbase\web\assets\cp\CpAsset as SproutBaseCpAsset;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class ImportAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbase/app/import/web/assets/import/dist';

        $this->depends = [
            SproutBaseCpAsset::class,
            CpAsset::class
        ];

        $this->js = [
            'js/sproutimport.js'
        ];

        $this->css = [
            'css/sproutimport.css'
        ];

        parent::init();
    }
}