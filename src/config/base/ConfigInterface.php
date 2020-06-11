<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\base;

use craft\db\Migration;

interface ConfigInterface
{
    const EDITION_LITE = 'lite';
    const EDITION_STANDARD = 'standard';
    const EDITION_PRO = 'pro';

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
     * Returns true if Config should display settings on the Control Panel settings page
     *
     * @return bool
     */
    public function hasControlPanelSettings(): bool;

    /**
     * Returns the name of the group that will be used to group settings for this config
     *
     * @return string
     */
    public static function groupName(): string;

    /**
     * Returns the Config that will be treated as the parent to this config
     * when grouping items in the sidebar. Items can only have one parent.
     *
     * By default, a Config will be treated as it's own group. Multiple configs
     * can return the same Config Group to have their sub-nav merged with a given group.
     *
     * @return ConfigInterface|null
     */
    public function getConfigGroup();

    /**
     * Returns a short description of the module to display in the settings area
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Returns an instance of this modules Settings model
     *
     * @return Settings|null
     */
    public function createSettingsModel();

    /**
     * Returns an instance of this modules Install migration
     *
     * @return Migration|null
     */
    public function createInstallMigration();

    /**
     * Returns any CP Nav Items this module supports
     *
     * @return array
     */
    public function getCpNavItem(): array;

    /**
     * Returns any CP URL Rules this module supports
     *
     * @return array
     */
    public function getCpUrlRules(): array;

    /**
     * Returns any Site URL Rules this module supports
     *
     * @return array
     */
    public function getSiteUrlRules(): array;

    /**
     * Returns any User Permissions this module supports
     *
     * @return array
     */
    public function getUserPermissions(): array;

    /**
     * Returns a list of plugin dependencies to facilitate
     * actions on install and uninstall
     *
     * @return array
     */
    public function getSproutDependencies(): array;

    /**
     * Returns an array of settings that will be added to the
     * Config model to be used in managing logic within a module
     *
     * @return array
     */
    public function getConfigSettings(): array;
}