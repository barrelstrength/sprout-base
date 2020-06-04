<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\web\assets\cp;

use barrelstrength\sproutbase\config\web\assets\cp\CpAsset as SproutBaseCpAsset;
use barrelstrength\sproutbase\app\fields\web\assets\selectother\SelectOtherFieldAsset;
use barrelstrength\sproutbase\app\forms\web\assets\fontawesome\FontAwesomeAsset;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset as CraftCpAsset;

class CpAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbase/app/forms/web/assets/cp/dist';

        // @todo - refactor sproutfields.js asset within SelectOtherField asset
        $this->depends = [
            CraftCpAsset::class,
            SproutBaseCpAsset::class,
            FontAwesomeAsset::class,
            SelectOtherFieldAsset::class
        ];

        $this->css = [
            'css/sproutforms-charts.css',
            'css/sproutforms-cp.css',
            'css/sproutforms-forms-ui.css'
        ];

        $this->js = [
            'js/sproutforms-cp.js'
        ];

        parent::init();
    }
}