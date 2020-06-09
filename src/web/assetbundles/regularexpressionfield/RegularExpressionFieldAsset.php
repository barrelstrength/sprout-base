<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\web\assetbundles\regularexpressionfield;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class RegularExpressionFieldAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbaseassetbundles/regularexpressionfield/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/RegularExpressionField.js',
        ];

        parent::init();
    }
}