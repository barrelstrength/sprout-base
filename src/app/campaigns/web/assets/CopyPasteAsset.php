<?php

namespace barrelstrength\sproutbase\app\campaigns\web\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Class CopyPasteAsset
 *
 * @package barrelstrength\sproutemail\web\assets\email
 */
class CopyPasteAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbase/app/email/web/assets/email/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'copypaste/js/copypaste.js'
        ];

        $this->css = [
            'copypaste/css/copypaste.css'
        ];

        parent::init();
    }
}