<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\events;

use yii\base\Event;

class RegisterFieldsEvent extends Event
{
    /**
     * @var array The registered Fields.
     */
    public $fields = [];
}
