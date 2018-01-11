<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\services\sproutfields;

use craft\base\Field;
use yii\base\Component;
use Craft;
use barrelstrength\sproutbase\SproutBase;

/**
 * Class LinkService
 *
 */
class Link extends Component
{
    /**
     * Validates a phone number against a given mask/pattern
     *
     * @param       $value
     * @param Field $field
     *
     * @return bool
     */
    public function validate($value, Field $field): bool
    {
        $customPattern = $field->customPattern;
        $checkPattern = $field->customPatternToggle;

        if ($customPattern && $checkPattern) {
            // Use backticks as delimiters as they are invalid characters for emails
            $customPattern = "`".$customPattern."`";

            if (preg_match($customPattern, $value)) {
                return true;
            }
        } else {
            if ((!filter_var($value, FILTER_VALIDATE_URL) === false)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return error message
     *
     * @param  mixed $field
     *
     * @return string
     */
    public function getErrorMessage($fieldName, $field): string
    {
        if (!empty($field->customPattern) && isset($field->customPatternErrorMessage)) {
            return SproutBase::t($field->customPatternErrorMessage);
        }

        return SproutBase::t($fieldName.' must be a valid link.');
    }

}
