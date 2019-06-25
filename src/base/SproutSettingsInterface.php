<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\base;

interface SproutSettingsInterface
{
    /**
     * Define plugin settings pages within the plugin tab in the Control Panel
     *
     * @return array
     * @example
     *   'settingsHeading' => [
     *   'heading' => Craft::t('sprout-forms', 'Settings'),
     *   ],
     *   'general' => [
     *   'label' => Craft::t('sprout-forms', 'General'),
     *   'url' => 'sprout-forms/settings/general',
     *   'selected' => 'general',
     *   'template' => 'sprout-forms/settings/general'
     *   ],
     *
     */
    public function getSettingsNavItems(): array;
}