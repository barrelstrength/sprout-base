<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\web\assets\sproutfields\address;

use craft\web\AssetBundle;
use barrelstrength\sproutbase\web\assets\sproutbase\cp\CpAsset;

class AddressFieldAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbase/web/assets/sproutfields/address/dist';

        $this->depends = [
            CpAsset::class
        ];

        $this->js = [
            'js/AddressBox.js',
            'js/AddressForm.js',
            'js/EditAddressModal.js'
        ];

        parent::init();
    }
}