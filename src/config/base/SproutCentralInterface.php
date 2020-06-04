<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\base;

use craft\base\Plugin;

/**
 * Interface SproutCentralInterface
 *
 * @package barrelstrength\sproutbase\config\base
 *
 * @mixin Plugin
 */
interface SproutCentralInterface
{
    /**
     * Implement this interface on all plugins so
     * we can easily sort out all Sprout plugins
     * from the list of all installed plugins and
     * manage dependencies
     *
     * @return ConfigInterface[]
     */
    public static function getSproutConfigs(): array;
}