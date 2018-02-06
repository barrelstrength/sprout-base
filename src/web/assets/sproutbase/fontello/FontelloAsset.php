<?php

namespace barrelstrength\sproutbase\web\assets\sproutbase\fontello;

use craft\web\AssetBundle;

class FontelloAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbase/web/assets/sproutbase/fontello/dist';

        $this->css = [
            'css/fontello.css',
        ];

        parent::init();
    }
}