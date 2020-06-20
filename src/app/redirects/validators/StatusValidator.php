<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\redirects\validators;

use barrelstrength\sproutbase\app\redirects\enums\RedirectStatuses;
use Craft;
use yii\validators\Validator;

class StatusValidator extends Validator
{
    /**
     * @inheritDoc
     */
    public function validateAttribute($object, $attribute)
    {
        if (!in_array($object->$attribute, [RedirectStatuses::ON, RedirectStatuses::OFF], true)) {
            $this->addError($object, $attribute, Craft::t('sprout', 'The status must be either "ON" or "OFF".'));
        }
    }
}
