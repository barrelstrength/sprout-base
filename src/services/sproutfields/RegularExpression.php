<?php
namespace barrelstrength\sproutcore\services\sproutfields;

use craft\base\Field;
use yii\base\Component;

use barrelstrength\sproutcore\Module;

/**
 * Class RegularExpression
 *
 */
class RegularExpression extends Component
{
	/**
	 *
	 * @param $value
	 * @param Field $field
	 *
	 * @return bool
	 */
	public function validate($value, Field $field): bool
	{
		$customPattern = $field->customPattern;

		if (!empty($customPattern))
		{
			// Use backticks as delimiters
			$customPattern = "`" . $customPattern . "`";

			if (!preg_match($customPattern, $value))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Return error message
	 *
	 * @param  mixed  $field
	 *
	 * @return string
	 */
	public function getErrorMessage($field): string
	{
		if (!empty($field->customPattern) && isset($field->customPatternErrorMessage))
		{
			return Module::t($field->customPatternErrorMessage);
		}

		return Module::t($field->name . ' must be a valid pattern.');
	}

}
