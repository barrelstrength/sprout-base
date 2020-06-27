<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\base;

interface ConfigNotificationEventInterface
{
    /**
     * Returns an array of supported Notification Event types for the given module
     *
     * @return array
     */
    public function getSupportedNotificationEventTypes(): array;
}