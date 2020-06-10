<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\configs;

use barrelstrength\sproutbase\migrations\forms\Install;
use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\config\controllers\SettingsController;
use barrelstrength\sproutbase\config\models\settings\FormsSettings;
use Craft;

class FormsConfig extends Config
{
    public static function displayName(): string
    {
        return Craft::t('sprout', 'Forms');
    }

    public function getDescription(): string
    {
        return Craft::t('sprout', 'Form builder and entry management');
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
                    'url' => 'sprout/forms'
                ],
                'entries' => [
                    'label' => Craft::t('sprout', 'Entries'),
                    'url' => 'sprout/forms/entries'
                ],
            ]
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            'sprout:forms:editForms' => [
                'label' => Craft::t('sprout', 'Edit Forms')
            ],
            'sprout:forms:viewEntries' => [
                'label' => Craft::t('sprout', 'View Form Entries'),
                'nested' => [
                    'sprout:forms:editEntries' => [
                        'label' => Craft::t('sprout', 'Edit Form Entries')
                    ]
                ]
            ]
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
            'sprout/forms/edit/<formId:\d+>/settings/<settingsSectionHandle:[^\/]+>' =>
                'sprout/forms/edit-settings-template',
            'sprout/forms/entries' =>
                'sprout/entries/entries-index-template',
            'sprout/forms/entries/edit/<entryId:\d+>' =>
                'sprout/entries/edit-entry-template',
            'sprout/forms/<groupId:\d+>' =>
                'sprout-forms/forms',

            // DB Settings
            'sprout/settings/<settingsSectionHandle:forms>/<settingsSubSectionHandle:entry-statuses>/new' => [
                'route' => 'sprout/entry-statuses/edit',
                'params' => [
                    'settingsTarget' => SettingsController::SETTINGS_TARGET_DB
                ]
            ],
            'sprout/settings/<settingsSectionHandle:forms>/<settingsSubSectionHandle:entry-statuses>/<entryStatusId:\d+>' => [
                'route' => 'sprout/entry-statuses/edit',
                'params' => [
                    'settingsTarget' => SettingsController::SETTINGS_TARGET_DB
                ]
            ]
        ];
    }
}

