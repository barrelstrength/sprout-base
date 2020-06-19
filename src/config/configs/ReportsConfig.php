<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\configs;

use barrelstrength\sproutbase\app\reports\datasources\CustomQuery;
use barrelstrength\sproutbase\app\reports\datasources\CustomTwigTemplate;
use barrelstrength\sproutbase\app\reports\datasources\Users;
use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\config\models\settings\ReportsSettings;
use barrelstrength\sproutbase\migrations\reports\Install;
use barrelstrength\sproutbase\SproutBase;
use Craft;

class ReportsConfig extends Config
{
    public function getKey(): string
    {
        return 'reports';
    }

    public static function displayName(): string
    {
        return Craft::t('sprout', 'Reports');
    }

    public function getDescription(): string
    {
        return Craft::t('sprout', 'View and manage reports');
    }

    public function createSettingsModel()
    {
        return new ReportsSettings();
    }

    public function createInstallMigration()
    {
        return new Install();
    }

    public function getCpNavItem(): array
    {
        return [
            'label' => Craft::t('sprout', 'Reports'),
            'url' => 'sprout/reports',
            'subnav' => [
                'reports' => [
                    'label' => Craft::t('sprout', 'Reports'),
                    'url' => 'sprout/reports'
                ],
                'data-sources' => [
                    'label' => Craft::t('sprout', 'Data Sources'),
                    'url' => 'sprout/reports/data-sources'
                ],
            ]
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            // Reports
            'sprout:reports:viewReports' => [
                'label' => Craft::t('sprout', 'View Reports'),
                'nested' => [
                    'sprout:reports:editReports' => [
                        'label' => Craft::t('sprout', 'Edit Reports')
                    ]
                ]
            ],

            // Data Sources
            'sprout:reports:editDataSources' => [
                'label' => Craft::t('sprout', 'Edit Data Sources')
            ]
        ];
    }

    public function getCpUrlRules(): array
    {
        return [
            // Reports
            'sprout/reports/<groupId:\d+>' =>
                'sprout/reports/reports-index-template',
            'sprout/reports/<dataSourceId:\d+>/new' =>
                'sprout/reports/edit-report-template',
            'sprout/reports/<dataSourceId:\d+>/edit/<reportId:\d+>' =>
                'sprout/reports/edit-report-template',
            'sprout/reports/view/<reportId:\d+>' =>
                'sprout/reports/results-index-template',
            'sprout/reports' =>
                'sprout/reports/reports-index-template',

            // Data Sources
            'sprout/reports/data-sources' =>
                'sprout/data-sources/data-sources-index-template'
        ];
    }

    public function setEdition()
    {
        $sproutReportsIsPro = SproutBase::$app->config->isPluginEdition('sprout-reports', Config::EDITION_STANDARD);

        if ($sproutReportsIsPro) {
            $this->_edition = Config::EDITION_PRO;
        }
    }

    public function getControllerMapKeys(): array
    {
        return [
            'reports',
            'data-sources'
        ];
    }

    public function getSupportedDataSourceTypes(): array
    {
        if ($this->getIsPro()) {
            return [
                CustomQuery::class,
                CustomTwigTemplate::class,
                Users::class
            ];
        }

        return [
            CustomQuery::class
        ];
    }
}

