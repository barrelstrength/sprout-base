<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\web\assetbundles\reports;

use barrelstrength\sproutbase\web\assetbundles\apexcharts\ApexChartsAsset;
use barrelstrength\sproutbase\web\assetbundles\datatables\DataTablesAsset;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class SproutReportsAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbaseassetbundles/reports/dist';

        $this->depends = [
            CpAsset::class,
            ApexChartsAsset::class,
            DataTablesAsset::class
        ];

        $this->css = [
            'css/reports.css',
            'css/visualizations.css'
        ];

        $this->js = [
            'js/Reports.js'
        ];

        parent::init();
    }
}