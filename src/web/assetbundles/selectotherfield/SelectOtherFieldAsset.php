<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\web\assetbundles\selectotherfield;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class SelectOtherFieldAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbaseassetbundles/selectotherfield/dist';

        $this->depends = [
            CpAsset::class
        ];

        $this->js = [
            'js/SelectOtherField.js'
        ];

        $this->css = [
            'css/select-other-field.css'
        ];

        parent::init();
    }
}