<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\sproutbase\events;

use yii\base\Event;

class BeforeSaveSettingsEvent extends Event
{
    public $plugin;
    public $settings;
}
