<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\base;

interface SproutDependencyInterface
{
    const SPROUT_BASE = 'sprout-base';
    const SPROUT_BASE_EMAIL = 'sprout-base-email';
    const SPROUT_BASE_FIELDS = 'sprout-base-fields';
    const SPROUT_BASE_IMPORT = 'sprout-base-import';
    const SPROUT_BASE_REDIRECTS = 'sprout-base-redirects';
    const SPROUT_BASE_REPORTS = 'sprout-base-reports';
    const SPROUT_BASE_SENT_EMAIL = 'sprout-base-sent-email';
    const SPROUT_BASE_SITEMAPS = 'sprout-base-sitemaps';
    const SPROUT_BASE_URIS = 'sprout-base-uris';

    /**
     * Return a list of dependencies to facilitate actions on install and uninstall
     *
     * @return array
     */
    public function getSproutDependencies(): array;
}