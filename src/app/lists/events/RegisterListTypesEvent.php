<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\lists\events;

use yii\base\Event;

class RegisterListTypesEvent extends Event
{
    /**
     * @var array
     */
    public $listTypes;
}