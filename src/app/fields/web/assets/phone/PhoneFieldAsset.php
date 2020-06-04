<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\fields\web\assets\phone;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class PhoneFieldAsset extends AssetBundle
{
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = '@sproutbase/app/fields/web/assets/phone/dist';

        // define the dependencies
        $this->depends = [
            CpAsset::class
        ];

        $this->css = [
            'css/phonefield.css',
        ];

        $this->js = [
            'js/phonefield.js'
        ];

        parent::init();
    }
}