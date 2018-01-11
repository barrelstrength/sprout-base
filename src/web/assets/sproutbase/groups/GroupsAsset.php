<?php

namespace barrelstrength\sproutbase\web\assets\sproutbase\groups;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class GroupsAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbase/web/assets/sproutbase/groups/dist';

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