<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\base;

interface ConfigDataSourceInterface
{
    /**
     * Returns an array of supported Data Source types for the given module
     *
     * @return array
     */
    public function getSupportedDataSourceTypes(): array;
}