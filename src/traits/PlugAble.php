<?php

namespace barrelstrength\sproutcore\traits;

use Craft;

trait PlugAble
{

	/**
	 * @param string $message
	 * @param array  $params
	 *
	 * @return string
	 */
	public static function t($message, array $params = [])
	{
		return Craft::t(static::$pluginId, $message, $params);
	}

	/**
	 * @param $message
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
	 * @param $message
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
	 * @param $message
	 */
	public static function warning($message)
	{
		if (is_array($message))
		{
			$message = print_r($message, true);
		}

		Craft::warning($message, static::$pluginId);
	}
}