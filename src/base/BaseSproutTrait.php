<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\base;

use Craft;

trait BaseSproutTrait
{
    public function getPlugin()
    {
        $pluginClass = get_class($this);

        $pluginHandle = Craft::$app->getPlugins()->getPluginHandleByClass($pluginClass);

        return Craft::$app->getPlugins()->getPlugin($pluginHandle);
    }

    /**
     * Logs an error message using the pluginHandle as the category
     *
     * @param string|array $message
     */
    public static function error($message)
    {
        if (is_array($message)) {
            /** @noinspection ForgottenDebugOutputInspection */
            $message = print_r($message, true);
        }

        Craft::error($message, static::$pluginHandle);
    }

    /**
     * Logs an warning message using the pluginHandle as the category
     *
     * @param string|array $message
     */
    public static function warning($message)
    {
        if (is_array($message)) {
            /** @noinspection ForgottenDebugOutputInspection */
            $message = print_r($message, true);
        }

        Craft::warning($message, static::$pluginHandle);
    }

    /**
     * Logs an info message using the pluginHandle as the category
     *
     * @param string|array $message
     */
    public static function info($message)
    {
        if (is_array($message)) {
            /** @noinspection ForgottenDebugOutputInspection */
            $message = print_r($message, true);
        }

        Craft::info($message, static::$pluginHandle);
    }
}