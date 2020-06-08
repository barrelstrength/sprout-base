<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\configs;

use barrelstrength\sproutbase\app\redirects\migrations\Install;
use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\config\models\settings\RedirectsSettings;
use Craft;

class RedirectsConfig extends Config
{
    public static function displayName(): string
    {
        return Craft::t('sprout', 'Redirects');
    }

    public function createSettingsModel()
    {
        return new RedirectsSettings();
    }

    public function createInstallMigration()
    {
        return new Install();
    }

    public function getCpNavItem(): array
    {
        return [
            'label' => Craft::t('sprout', 'Redirects'),
            'url' => 'sprout/redirects',
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            'sprout:redirects:editRedirects' => [
                'label' => Craft::t('sprout', 'Edit Redirects')
            ]
        ];
    }

    public function getCpUrlRules(): array
    {
        return [
            // Redirects
            'sprout/redirects/edit/<redirectId:\d+>/<siteHandle:[^\/]+>' =>
                'sprout/redirects/edit-redirect-template',
            'sprout/redirects/edit/<redirectId:\d+>' =>
                'sprout/redirects/edit-redirect-template',
            'sprout/redirects/new/<siteHandle:[^\/]+>' =>
                'sprout/redirects/edit-redirect-template',
            'sprout/redirects/new' =>
                'sprout/redirects/edit-redirect-template',
            'sprout/redirects/<siteHandle:[^\/]+>' =>
                'sprout/redirects/redirects-index-template',
            'sprout/redirects' =>
                'sprout/redirects/redirects-index-template'
        ];
    }
}

