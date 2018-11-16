<?php

namespace barrelstrength\sproutbase\app\import\web\twig\variables;

use barrelstrength\sproutbase\SproutBase;
use Craft;

class SproutImportVariable
{
    /**
     * Confirm if a specific plugin is installed
     *
     * @param string
     *
     * @return bool
     */
    public function isPluginInstalled($plugin)
    {
        if (Craft::$app->getPlugins()->getPlugin($plugin)) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getSproutImportBundles()
    {
        return SproutBase::$app->bundles->getSproutImportBundles();
    }

    /**
     * @return array
     */
    public function getSproutImportImporters()
    {
        return SproutBase::$app->importers->getSproutImportImporters();
    }

    public function getBundleByClass($class)
    {
        return SproutBase::$app->bundles->getBundleByClass($class);
    }

    /**
     * @return mixed
     */
    public function getSproutImportFieldImporters()
    {
        return SproutBase::$app->importers->getSproutImportFieldImporters();
    }

    /**
     * Confirm if any seeds exist
     *
     * @return int
     */
    public function hasSeeds()
    {
        $seeds = SproutBase::$app->seed->getAllSeeds();

        return count($seeds);
    }
}