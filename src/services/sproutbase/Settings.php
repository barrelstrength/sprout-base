<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\services\sproutbase;

use Craft;
use craft\base\Plugin;
use craft\helpers\Json;
use yii\base\Component;
use barrelstrength\sproutbase\events\BeforeSaveSettingsEvent;

class Settings extends Component
{
    const EVENT_BEFORE_SAVE_SETTINGS = 'beforeSaveSettings';

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

        $event = new BeforeSaveSettingsEvent([
            'plugin' => $plugin,
            'settings' => $settings
        ]);

        $this->trigger(self::EVENT_BEFORE_SAVE_SETTINGS, $event);

        // Have namespace?
        $settings = $settings['settings'] ?? $settings;
        $scenario = $settings['sprout-scenario'] ?? null;

        foreach ($pluginSettings->getAttributes() as $settingHandle => $value) {
            if (isset($settings[$settingHandle])) {
                $pluginSettings->{$settingHandle} = $settings[$settingHandle] ?? $value;
            }
        }
        // Set sprout scenario
        if ($scenario) {
            $pluginSettings->setScenario($scenario);
        }

        if (!$pluginSettings->validate()) {
            return $pluginSettings;
        }

        $settings = Json::encode($pluginSettings);

        Craft::$app->db->createCommand()->update('{{%plugins}}', [
            'settings' => $settings
        ], [
            'handle' => strtolower($plugin->handle)
        ])->execute();

        return $pluginSettings;
    }

}
