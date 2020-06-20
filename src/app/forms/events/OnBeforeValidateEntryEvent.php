<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\events;

use barrelstrength\sproutbase\app\forms\elements\Entry;
use barrelstrength\sproutbase\app\forms\elements\Form;
use yii\base\Event;

class OnBeforeValidateEntryEvent extends Event
{
    /**
     * @var Form
     */
    public $form;

    /**
     * @var Entry
     */
    public $entry;
}
