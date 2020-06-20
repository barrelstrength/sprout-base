<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\rules\conditions;

use barrelstrength\sproutbase\app\forms\base\Condition;
use Craft;

class IsGreaterThanOrEqualToCondition extends Condition
{
    public function getLabel(): string
    {
        return 'is greater than or equal to';
    }

    public function validateCondition()
    {
        if ($this->inputValue >= $this->ruleValue) {
            return;
        }

        $this->addError('inputValue', Craft::t('sprout', 'Condition does not validate'));
    }
}