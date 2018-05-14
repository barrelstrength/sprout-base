<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\reports\web\assets\base;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class SproutReportsAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbase/app/reports/web/assets/base/dist';

        $this->depends = [
            CpAsset::class
        ];

        $this->css = [
            'css/sproutreports.css'
        ];

        parent::init();
    }
}