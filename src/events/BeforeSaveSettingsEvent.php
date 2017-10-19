<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutcore\events;

use yii\base\Event;

class BeforeSaveSettingsEvent extends Event
{
	// Properties
	// =========================================================================

	public $plugin = null;
	public $settings = null;
}
