<?php

namespace barrelstrength\sproutbase\web\assets\sproutbase\fontawesome;

use craft\web\AssetBundle;

class FontAwesomeAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbase/web/assets/sproutbase/fontawesome/dist';

        $this->css = [
            'css/font-awesome.min.css'
        ];

        parent::init();
    }
}