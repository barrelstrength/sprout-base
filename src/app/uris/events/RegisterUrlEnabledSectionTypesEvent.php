<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\uris\events;

use yii\base\Event;

class RegisterUrlEnabledSectionTypesEvent extends Event
{
    public $urlEnabledSectionTypes = [];
}
