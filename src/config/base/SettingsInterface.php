<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\base;

/**
 * @mixin Settings::getIsEnabled()
 */
interface SettingsInterface
{
    /**
     * Define plugin settings pages within the plugin tab in the Control Panel
     *
     * @return array
     * @example
     *   'settingsHeading' => [
     *   'heading' => Craft::t('sprout', 'Settings'),
     *   ],
     *   'general' => [
     *   'label' => Craft::t('sprout', 'General'),
     *   'url' => 'sprout-forms/settings/general',
     *   'selected' => 'general',
     *   'template' => 'sprout-forms/settings/general'
     *   ],
     *
     */
    public function getSettingsNavItem(): array;
}