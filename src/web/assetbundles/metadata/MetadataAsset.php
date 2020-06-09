<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\web\assetbundles\metadata;

use barrelstrength\sproutbase\web\assetbundles\selectotherfield\SelectOtherFieldAsset;
use barrelstrength\sproutbase\web\assetbundles\tageditor\TagEditorAsset;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\assets\datepickeri18n\DatepickerI18nAsset;
use craft\web\assets\timepicker\TimepickerAsset;
use yii\web\JqueryAsset;

class MetadataAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbaseassetbundles/metadata/dist';

        $this->depends = [
            CpAsset::class,
            JqueryAsset::class,
            DatepickerI18nAsset::class,
            TimepickerAsset::class,
            TagEditorAsset::class,
            SelectOtherFieldAsset::class
        ];

        $this->js = [
            'js/Metadata.js'
        ];

        $this->css = [
            'css/metadata.css'
        ];

        parent::init();
    }
}