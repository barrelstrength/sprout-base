<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutcore\services\sproutcore;

use Craft;
use craft\helpers\Json;
use yii\base\Component;
use barrelstrength\sproutcore\events\BeforeSaveSettingsEvent;

class Settings extends Component
{
	const EVENT_BEFORE_SAVE_SETTINGS = 'beforeSaveSettings';

	public function saveSettings($plugin, $settings)
	{
		// The existing settings
		$pluginSettings = $plugin->getSettings();

		$event = new BeforeSaveSettingsEvent([
			'plugin'   => $plugin,
			'settings' => $settings
		]);

		$this->trigger(self::EVENT_BEFORE_SAVE_SETTINGS, $event);

		foreach ($pluginSettings->getAttributes() as $settingHandle => $value)
		{
			if (isset($settings[$settingHandle]))
			{
				$pluginSettings->{$settingHandle} = $settings[$settingHandle] ?? $value;
			}
		}

		$settings = Json::encode($pluginSettings);

		$affectedRows = Craft::$app->db->createCommand()->update('{{%plugins}}', [
			'settings' => $settings
		], [
			'handle' => strtolower($plugin->handle)
		])->execute();

		return (bool)$affectedRows;
	}

}
