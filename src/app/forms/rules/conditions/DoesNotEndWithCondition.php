<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\rules\conditions;

use barrelstrength\sproutbase\app\forms\base\Condition;
use Craft;

/**
 *
 * @property string $label
 */
class DoesNotEndWithCondition extends Condition
{
    public function getLabel(): string
    {
        return 'does not end with';
    }

    public function validateCondition()
    {
        if (substr_compare($this->inputValue, $this->ruleValue, -strlen($this->ruleValue)) !== 0) {
            return;
        }

        $this->addError('inputValue', Craft::t('sprout', 'Condition does not validate'));
    }
}