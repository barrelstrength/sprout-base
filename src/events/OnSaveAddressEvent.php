<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\events;

use yii\base\Event;

class OnSaveAddressEvent extends Event
{
    // Properties
    // =========================================================================

    public $model = null;
    public $source = null;
}
