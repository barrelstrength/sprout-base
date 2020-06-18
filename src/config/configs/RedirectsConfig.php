<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\configs;

use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\config\models\settings\RedirectsSettings;
use barrelstrength\sproutbase\migrations\redirects\Install;
use barrelstrength\sproutbase\SproutBase;
use Craft;

/**
 *
 * @property array $cpNavItem
 * @property array|string[] $cpUrlRules
 * @property SeoConfig $configGroup
 * @property string $description
 * @property array[]|array $userPermissions
 * @property array|string[] $controllerMapKeys
 * @property string $key
 */
class RedirectsConfig extends Config
{
    public function getKey(): string
    {
        return 'redirects';
    }

    public static function displayName(): string
    {
        return Craft::t('sprout', 'Redirects');
    }

    public function getDescription(): string
    {
        return Craft::t('sprout', 'Manage redirects and track 404s');
    }

    public function getConfigGroup()
    {
        return new SeoConfig();
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

    public function setEdition()
    {
        $sproutRedirectsIsPro = SproutBase::$app->config->isPluginEdition('sprout-redirects', Config::EDITION_PRO);
        $sproutSeoIsPro = SproutBase::$app->config->isPluginEdition('sprout-seo', Config::EDITION_PRO);

        if ($sproutSeoIsPro || $sproutRedirectsIsPro) {
            $this->_edition = Config::EDITION_PRO;
        }
    }

    public function getControllerMapKeys(): array
    {
        return [
            'redirects'
        ];
    }
}

