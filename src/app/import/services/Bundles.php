<?php

namespace barrelstrength\sproutbase\app\import\services;

use barrelstrength\sproutbase\app\import\bundles\SimpleBundle;
use barrelstrength\sproutbase\app\import\base\Bundle;
use craft\base\Component;
use craft\events\RegisterComponentTypesEvent;

class Bundles extends Component
{
    const EVENT_REGISTER_BUNDLE_TYPES = 'registerBundlesTypes';

    /**
     * @var array
     */
    protected $bundles = [];

    public function getSproutImportBundles(): array
    {
        $bundleTypes = [
            SimpleBundle::class
        ];

        $event = new RegisterComponentTypesEvent([
            'types' => $bundleTypes
        ]);

        $this->trigger(self::EVENT_REGISTER_BUNDLE_TYPES, $event);

        $bundles = $event->types;

        if ($bundles !== null) {
            foreach ($bundles as $bundleClass) {

                // Create an instance of our Bundle object
                $bundle = new $bundleClass();

                $this->bundles[$bundleClass] = $bundle;
            }
        }

        uasort($this->bundles, function($a, $b) {
            /**
             * @var $a Bundle
             * @var $b Bundle
             */
            return $a->getName() <=> $b->getName();
        });

        return $this->bundles;
    }
}