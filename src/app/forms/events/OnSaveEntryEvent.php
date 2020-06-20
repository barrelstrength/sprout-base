<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\events;

use barrelstrength\sproutbase\app\forms\elements\Entry;
use yii\base\Event;

class OnSaveEntryEvent extends Event
{
    /**
     * @var Entry
     */
    public $entry;

    /**
     * @var bool
     */
    public $isNewEntry = true;
}
