<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\seo\web\assets\livepreview;


use craft\web\AssetBundle;


class LivePreviewAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbase/app/seo/web/assets/';

        $this->css = [
            'livepreview/dist/css/cp.css',
            'livepreview/dist/css/craft.css',
            'base/dist/css/sproutseo.css',
            'livepreview/dist/css/preview.css'
        ];

        $this->js = [
            'livepreview/dist/js/jquery.min.js',
            'livepreview/dist/js/live-preview.js'
        ];

        parent::init();
    }
}