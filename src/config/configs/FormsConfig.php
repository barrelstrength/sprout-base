<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\configs;

use barrelstrength\sproutbase\app\forms\controllers\FormEntriesController;
use barrelstrength\sproutbase\app\forms\controllers\FormEntryStatusesController;
use barrelstrength\sproutbase\app\forms\controllers\FormFieldsController;
use barrelstrength\sproutbase\app\forms\controllers\FormGroupsController;
use barrelstrength\sproutbase\app\forms\controllers\FormIntegrationsController;
use barrelstrength\sproutbase\app\forms\controllers\FormRulesController;
use barrelstrength\sproutbase\app\forms\controllers\FormsController;
use barrelstrength\sproutbase\app\forms\integrations\sproutemail\events\notificationevents\SaveEntryEvent;
use barrelstrength\sproutbase\app\forms\integrations\sproutreports\datasources\EntriesDataSource;
use barrelstrength\sproutbase\app\forms\integrations\sproutreports\datasources\IntegrationLogDataSource;
use barrelstrength\sproutbase\app\forms\integrations\sproutreports\datasources\SpamLogDataSource;
use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\config\controllers\SettingsController;
use barrelstrength\sproutbase\config\models\settings\FormsSettings;
use barrelstrength\sproutbase\migrations\forms\Install;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\web\twig\variables\FormsVariable;
use Craft;

class FormsConfig extends Config
{
    public static function getControllerMap(): array
    {
        return [
            'forms' => FormsController::class,
            'form-entries' => FormEntriesController::class,
            'form-entry-statuses' => FormEntryStatusesController::class,
            'form-fields' => FormFieldsController::class,
            'form-groups' => FormGroupsController::class,
            'form-integrations' => FormIntegrationsController::class,
            'form-rules' => FormRulesController::class,
        ];
    }

    public static function getVariableMap(): array
    {
        return [
            'forms' => FormsVariable::class,
        ];
    }

    public static function getSproutConfigs(): array
    {
        return [
            FieldsConfig::class
        ];
    }

    public static function getKey(): string
    {
        return 'forms';
    }

    public static function displayName(): string
    {
        return Craft::t('sprout', 'Forms');
    }

    public function getDescription(): string
    {
        return Craft::t('sprout', 'Form builder and entry management');
    }

    public function getUpgradeMessage(): string
    {
        return Craft::t('sprout', 'Upgrade to Sprout Forms PRO to manage Unlimited Forms');
    }

    public function createSettingsModel()
    {
        return new FormsSettings();
    }

    public function createInstallMigration()
    {
        return new Install();
    }

    public function getCpNavItem(): array
    {
        return [
            'label' => Craft::t('sprout', 'Forms'),
            'url' => 'sprout/forms',
            'subnav' => [
                'forms' => [
                    'label' => Craft::t('sprout', 'Forms'),
                    'url' => 'sprout/forms',
                ],
                'entries' => [
                    'label' => Craft::t('sprout', 'Entries'),
                    'url' => 'sprout/forms/entries',
                ],
            ],
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            'sprout:forms:editForms' => [
                'label' => Craft::t('sprout', 'Edit Forms'),
            ],
            'sprout:forms:viewEntries' => [
                'label' => Craft::t('sprout', 'View Form Entries'),
                'nested' => [
                    'sprout:forms:editEntries' => [
                        'label' => Craft::t('sprout', 'Edit Form Entries'),
                    ],
                ],
            ],
        ];
    }

//    public function defineRules(): array
//    {
//        $rules = parent::defineRules();
//
//        $rules[] = [['formTemplateId'], 'required', 'on' => 'general'];
//
//        return $rules;
//    }

    /**
     * @return array
     */
    public function getCpUrlRules(): array
    {
        return [
//            'sprout/forms' =>
//                'sprout/forms/forms-default-section',
            'sprout/forms' =>
                'sprout/forms/forms-index-template',
            'sprout/forms/new' =>
                'sprout/forms/edit-form-template',
            'sprout/forms/edit/<formId:\d+>' =>
                'sprout/forms/edit-form-template',
            'sprout/forms/edit/<formId:\d+>/settings/<subNavKey:[^\/]+>' =>
                'sprout/forms/edit-settings-template',
            'sprout/forms/entries' =>
                'sprout/form-entries/entries-index-template',
            'sprout/forms/entries/edit/<entryId:\d+>' =>
                'sprout/form-entries/edit-entry-template',
            'sprout/forms/<groupId:\d+>' =>
                'sprout-forms/forms',

            // DB Settings
            'sprout/settings/<configKey:forms>/<subNavKey:entry-statuses>/new' => [
                'route' => 'sprout/form-entry-statuses/edit',
                'params' => [
                    'settingsTarget' => SettingsController::SETTINGS_TARGET_CUSTOM,
                ],
            ],
            'sprout/settings/<configKey:forms>/<subNavKey:entry-statuses>/<entryStatusId:\d+>' => [
                'route' => 'sprout/form-entry-statuses/edit',
                'params' => [
                    'settingsTarget' => SettingsController::SETTINGS_TARGET_CUSTOM,
                ],
            ],
        ];
    }

    public function setEdition()
    {
        $sproutFormsIsPro = SproutBase::$app->config->isPluginEdition('sprout-forms', Config::EDITION_PRO);

        if ($sproutFormsIsPro) {
            $this->_edition = Config::EDITION_PRO;
        }
    }

    public function getSupportedDataSourceTypes(): array
    {
        return [
            EntriesDataSource::class,
            IntegrationLogDataSource::class,
            SpamLogDataSource::class,
        ];
    }

    public function getSupportedNotificationEventTypes(): array
    {
        return [
            SaveEntryEvent::class,
        ];
    }
}

