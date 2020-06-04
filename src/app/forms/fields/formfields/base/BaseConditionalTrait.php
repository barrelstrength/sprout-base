<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\fields\formfields\base;

use barrelstrength\sproutbase\app\forms\base\ConditionInterface;

trait BaseConditionalTrait
{
    /**
     * This add support for the field rule condition api return a prover value input html depending of the condition
     *
     * @param ConditionInterface $condition
     * @param                    $fieldName
     * @param                    $fieldValue
     *
     * @return string
     */
    public function getConditionValueInputHtml(ConditionInterface $condition, $fieldName, $fieldValue): string
    {
        return '<input class="text fullwidth" type="text" name="'.$fieldName.'" value="'.$fieldValue.'">';
    }
}
