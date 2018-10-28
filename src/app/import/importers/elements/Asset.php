<?php

namespace barrelstrength\sproutbase\app\import\importers\elements;

use Craft;
use barrelstrength\sproutbase\app\import\base\ElementImporter;
use craft\elements\Asset as AssetElement;

class Asset extends ElementImporter
{
    public function getName()
    {
        return Craft::t('sprout-base', 'Assets');
    }

    /**
     * @return mixed
     */
    public function getModelName()
    {
        return AssetElement::class;
    }

    public function getFieldLayoutId($model)
    {

    }
}