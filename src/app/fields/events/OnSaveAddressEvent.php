<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\fields\events;

use barrelstrength\sproutbase\app\fields\models\Address;
use yii\base\Event;

class OnSaveAddressEvent extends Event
{
    /**
     * @var Address
     */
    public $address;
}
