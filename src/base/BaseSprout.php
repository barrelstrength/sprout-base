<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutcore\base;

use Craft;

trait BaseSprout
{
	/**
	 * Logs an error message using the pluginId as the category
	 *
	 * @param string|array $message
	 */
	public static function error($message)
	{
		if (is_array($message))
		{
			$message = print_r($message, true);
		}

		Craft::error($message, static::$pluginId);
	}

	/**
	 * Logs an warning message using the pluginId as the category
	 *
	 * @param string|array $message
	 */
	public static function warning($message)
	{
		if (is_array($message))
		{
			$message = print_r($message, true);
		}

		Craft::warning($message, static::$pluginId);
	}

	/**
	 * Logs an info message using the pluginId as the category
	 *
	 * @param string|array $message
	 */
	public static function info($message)
	{
		if (is_array($message))
		{
			$message = print_r($message, true);
		}

		Craft::info($message, static::$pluginId);
	}

	/**
	 * Translates a message to the specified language using the pluginId as the category
	 *
	 * @param string $message
	 * @param array  $params
	 *
	 * @return string
	 */
	public static function t($message, array $params = [])
	{
		return Craft::t(static::$pluginId, $message, $params);
	}
}