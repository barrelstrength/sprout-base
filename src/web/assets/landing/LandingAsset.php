<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\web\assets\landing;

use barrelstrength\sproutbase\web\assets\cp\CpAsset as SproutCpAsset;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset as CraftCpAsset;

class LandingAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = '@sproutbase/web/assets/landing/dist';

        $this->depends = [
            CraftCpAsset::class,
            SproutCpAsset::class
        ];

        $this->css = [
            'css/landing.css',
        ];

        parent::init();
    }
}