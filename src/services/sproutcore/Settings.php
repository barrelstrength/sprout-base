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

class Settings extends Component
{
	public function saveSettings($plugin, $settings)
	{
		// The existing settings
		$pluginSettings = $plugin->getSettings();

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
