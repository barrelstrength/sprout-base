<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\web\assetbundles\sproutcp;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class SproutCpAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbaseassetbundles/sproutcp/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->css = [
            'css/sprout-cp.css',
        ];

        parent::init();
    }
}