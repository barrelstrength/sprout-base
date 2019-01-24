<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\services;

use Craft;
use craft\base\Plugin;
use craft\helpers\Json;
use yii\base\Component;


class Settings extends Component
{
    /**
     * @param $plugin Plugin
     * @param $settings
     *
     * @return \craft\base\Model
     * @throws \yii\db\Exception
     */
    public function saveSettings($plugin, $settings)
    {
        // The existing settings
        $pluginSettings = $plugin->getSettings();

        // Have namespace?
        $settings = $settings['settings'] ?? $settings;
        // Set sprout scenario validation on the settings model
        $scenario = $settings['validationScenario'] ?? null;

        foreach ($pluginSettings->getAttributes() as $settingHandle => $value) {
            if (isset($settings[$settingHandle])) {
                $pluginSettings->{$settingHandle} = $settings[$settingHandle] ?? $value;
            }
        }

        if ($scenario) {
            $pluginSettings->setScenario($scenario);
        }

        if (!$pluginSettings->validate()) {
            return $pluginSettings;
        }

        return $pluginSettings;
    }

}
