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
    const SPROUT_BASE = 'sprout-base';
    const SPROUT_BASE_EMAIL = 'sprout-base-email';
    const SPROUT_BASE_FIELDS = 'sprout-base-fields';
    const SPROUT_BASE_IMPORT = 'sprout-base-import';

    //    const SPROUT_SETTINGS = 'settings';
//    const SPROUT_CAMPAIGNS = 'campaigns';
//    const SPROUT_EMAIL = 'email';
//    const SPROUT_FIELDS = 'fields';
//    const SPROUT_IMPORT = 'import';
//    const SPROUT_REDIRECTS = 'redirects';
//    const SPROUT_REPORTS = 'reports';
//    const SPROUT_SENT_EMAIL = 'sent-email';
//    const SPROUT_SITEMAPS = 'sitemaps';
//    const SPROUT_URIS = 'uris';
//
    const SPROUT_BASE_REDIRECTS = 'sprout-base-redirects';
    const SPROUT_BASE_REPORTS = 'sprout-base-reports';
    const SPROUT_BASE_SENT_EMAIL = 'sprout-base-sent-email';
    const SPROUT_BASE_SITEMAPS = 'sprout-base-sitemaps';
    const SPROUT_BASE_URIS = 'sprout-base-uris';

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

    public function getUserPermissions(): array;

    public function getCpUrlRules(): array;

    /**
     * @return Settings|null
     */
    public function createSettingsModel();

    /**
     * @return Migration|null
     */
    public function createInstallMigration();

    /**
     * @return array
     *
     * @todo - update this to be called getSchemaDependencies?
     *         as the goal is primarily to keep from uninstalling
     *         schema when it's still in use
     * Returns a list of plugin dependencies to facilitate
     * actions on install and uninstall
     *
     */
    public function getSproutDependencies(): array;
}