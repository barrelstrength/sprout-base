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
class DoesNotContainCondition extends Condition
{
    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return 'does not contain';
    }

    public function validateCondition()
    {
        if (strpos($this->inputValue, $this->ruleValue) === false) {
            return;
        }

        $this->addError('inputValue', Craft::t('sprout', 'Condition does not validate'));
    }
}