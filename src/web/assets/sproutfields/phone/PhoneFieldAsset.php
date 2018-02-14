<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\web\assets\sproutfields\phone;

use barrelstrength\sproutbase\web\assets\sproutbase\fontello\FontelloAsset;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class PhoneFieldAsset extends AssetBundle
{
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = '@sproutbase/web/assets/sproutfields';

        // define the dependencies
        $this->depends = [
            CpAsset::class,
            InputMaskAsset::class,
            FontelloAsset::class
        ];

        $this->js = [
            'phone/dist/js/PhoneInputMask.js'
        ];

        $this->css = [
            'base/css/sproutfields.css',
        ];

        parent::init();
    }
}