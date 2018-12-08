<?php

namespace barrelstrength\sproutbase\app\email\web\assets\email;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Class EmailAsset
 *
 * @package barrelstrength\sproutemail\web\assets\email
 */
class EmailAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbase/app/email/web/assets/email/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/sproutmodal.js',
            'js/SproutEmail.SentEmailElementEditor.js'
        ];

        $this->css = [
            'css/sproutemail.css',
            'css/modal.css',
            'css/charts-explorer.css',
        ];

        parent::init();
    }
}