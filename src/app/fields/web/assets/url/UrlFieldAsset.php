<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\fields\web\assets\url;

use barrelstrength\sproutbase\config\web\assets\cp\CpAsset;
use craft\web\AssetBundle;

class UrlFieldAsset extends AssetBundle
{
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = '@sproutbase/app/fields/web/assets/url/dist';

        // define the dependencies
        $this->depends = [
            CpAsset::class
        ];

        $this->css = [
            'css/urlfield.css',
        ];

        $this->js = [
            'js/urlfield.js',
        ];

        parent::init();
    }
}