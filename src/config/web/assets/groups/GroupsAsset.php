<?php

namespace barrelstrength\sproutbase\config\web\assets\groups;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class GroupsAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbase/config/web/assets/groups/dist';

        // define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/groups.js',
        ];

        parent::init();
    }
}