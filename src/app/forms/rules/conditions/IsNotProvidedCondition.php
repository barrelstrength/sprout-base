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
class IsNotProvidedCondition extends Condition
{
    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return 'is not provided';
    }

    public function validateCondition()
    {
        if (empty($this->inputValue) === true) {
            return;
        }

        $this->addError('inputValue', Craft::t('sprout', 'Condition does not validate'));
    }
}