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

    /**
     * @var Address
     * @deprecated Sprout Forms v3.6.6, Sprout Fields v3.4.4 - Use $address property instead.
     *             Will be removed in Sprout Forms v4.x and Sprout Fields 4.x
     */
    public $model;

    /**
     * @var string
     * @deprecated Sprout Forms v3.6.6, Sprout Fields v3.4.4 - Not in use
     *             Will be removed in Sprout Forms v4.x and Sprout Fields 4.x
     */
    public $source;
}
