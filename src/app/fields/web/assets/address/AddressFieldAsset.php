<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\fields\web\assets\address;

use barrelstrength\sproutbase\config\web\assets\cp\CpAsset;
use craft\web\AssetBundle;

class AddressFieldAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbase/app/fields/web/assets/address/dist';

        $this->depends = [
            CpAsset::class
        ];

        $this->css = [
            'css/addressfield.css'
        ];

        $this->js = [
            'js/addressfield.js'
        ];

        parent::init();
    }
}