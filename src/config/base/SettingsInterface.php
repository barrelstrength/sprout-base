<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\base;

/**
 * Interface SettingsInterface
 *
 * @package barrelstrength\sproutbase\config\base
 *
 * @mixin Settings::getIsEnabled()
 */
interface SettingsInterface
{
    /**
     * The short name we use to refer to this module
     * in settings, etc.
     * The URL segment used to identify this settings section
     * Returns a snake-case version of the settings model class name
     * class name to use as a key or slug
     * (removes 'Settings' characters from end of class name)
     *
     * i.e.
     * sprout-forms => 'forms'
     * sprout-sent-email => 'sent-email'
     *
     * @return string
     */
    public function getKey(): string;

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