<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\redirects\web\assets\redirects;


use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class RedirectsAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbase/app/redirects/web/assets/redirects/dist';

        // define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

        $this->css = [
            'css/redirects.css'
        ];

        $this->js = [
            'js/redirectindex.js'
        ];

        parent::init();
    }
}