<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\web\assetbundles\forms;

use barrelstrength\sproutbase\web\assetbundles\selectotherfield\SelectOtherFieldAsset;
use barrelstrength\sproutbase\web\assetbundles\fontawesome\FontAwesomeAsset;
use barrelstrength\sproutbase\web\assetbundles\sproutcp\SproutCpAsset;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use yii\web\JqueryAsset;

class FormsAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbaseassetbundles/forms/dist';

        $this->depends = [
            CpAsset::class,
            JqueryAsset::class,
            SproutCpAsset::class,
            FontAwesomeAsset::class,
            SelectOtherFieldAsset::class
        ];

        $this->css = [
            'css/forms.css',
            'css/forms-ui.css'
        ];

        $this->js = [
            'js/Forms.js'
        ];

        parent::init();
    }
}