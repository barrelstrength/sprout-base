<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\config\web\assets\cp;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset as CraftCpAsset;

class CpAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbase/config/web/assets/cp/dist';

        $this->depends = [
            CraftCpAsset::class
        ];

        $this->css = [
            'css/sproutcp.css',
        ];

        parent::init();
    }
}