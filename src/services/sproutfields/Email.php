<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\services\sproutfields;

use Craft;
use craft\base\Field;
use yii\base\Component;
use craft\db\Query;

use barrelstrength\sproutbase\SproutBase;

/**
 * Class EmailService
 *
 */
class Email extends Component
{
	/**
	 * Handles validation of an email address as user edits email in the UI
	 *
	 * @param string $value
	 * @param int    $elementId
	 * @param Field  $field
	 *
	 * @return bool
	 */
	public function validate($value, Field $field, $elementId): bool
	{
		$customPattern = $field->customPattern;
		$checkPattern = $field->customPatternToggle;

		if (!$this->validateEmailAddress($value, $customPattern, $checkPattern))
		{
			return false;
		}

		if ($elementId)
		{
			$element = Craft::$app->elements->getElementById($elementId);

			if ($field->uniqueEmail && !$this->validateUniqueEmailAddress($value, $element, $field))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Validates an email address or email custom pattern
	 *
	 * @param $value         string current email to validate
	 * @param $customPattern string regular expression
	 * @param $checkPattern  bool
	 *
	 * @return bool
	 */
	public function validateEmailAddress($value, $customPattern, $checkPattern = false): bool
	{
		if ($checkPattern)
		{
			// Use backticks as delimiters as they are invalid characters for emails
			$customPattern = "`".$customPattern."`";

			if (preg_match($customPattern, $value))
			{
				return true;
			}
		} else
		{
			if (!filter_var($value, FILTER_VALIDATE_EMAIL) === false)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Validates that an email address is unique to a particular field type
	 *
	 * @param $value
	 * @param $element
	 * @param $field
	 *
	 * @return bool
	 */
	public function validateUniqueEmailAddress($value, $element, $field)
	{
		$fieldHandle = $element->fieldColumnPrefix.$field->handle;
		$contentTable = $element->contentTable;

		$query = (new Query())
			->select($fieldHandle)
			->from($contentTable)
			->where([$fieldHandle => $value]);

		if (is_numeric($element->id))
		{
			// Exclude current elementId from our results
			$query->andWhere(['not in', 'elementId', $element->id]);
		}

		$emailExists = $query->scalar();

		if ($emailExists)
		{
			return false;
		}

		return true;
	}

	/**
	 * @param $fieldName
	 * @param $field
	 *
	 * @return string
	 */
	public function getErrorMessage($fieldName, $field)
	{
		if (!empty($field->customPattern) && $field->customPatternErrorMessage)
		{
			return SproutBase::t($field->customPatternErrorMessage);
		}

		return SproutBase::t($fieldName.' must be a valid email.');
	}

}
