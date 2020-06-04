<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\models\settings;

use barrelstrength\sproutbase\config\base\Settings;
use barrelstrength\sproutforms\formtemplates\AccessibleTemplates;
use barrelstrength\sproutforms\SproutForms;
use Craft;

class FormsSettings extends Settings
{
    const SPAM_REDIRECT_BEHAVIOR_NORMAL = 'redirectAsNormal';
    const SPAM_REDIRECT_BEHAVIOR_BACK_TO_FORM = 'redirectBackToForm';

    public $pluginNameOverride = '';

    public $defaultSection = 'entries';

    public $formTemplateId = AccessibleTemplates::class;

    public $enableSaveData = true;

    public $saveSpamToDatabase = false;

    public $enableSaveDataDefaultValue = true;

    public $spamRedirectBehavior = self::SPAM_REDIRECT_BEHAVIOR_NORMAL;

    public $spamLimit = 500;

    public $cleanupProbability = 1000;

    public $trackRemoteIp = false;

    public $captchaSettings = [];

    public $enableEditFormEntryViaFrontEnd = false;

    public $showNotificationsTab = true;

    public $showReportsTab = true;

    public function getSettingsNavItem(): array
    {
        return [
            'label' => Craft::t('sprout', 'Forms'),
            'url' => 'sprout/settings/forms',
            'icon' => '@sproutbaseicons/plugins/forms/icon.svg',
            'subnav' => [
                'forms' => [
                    'label' => Craft::t('sprout', 'Forms'),
                    'url' => 'sprout/settings/forms',
                    'template' => 'sprout-forms/settings/general',
                ],
                'spam-protection' => [
                    'label' => Craft::t('sprout', 'Spam Protection'),
                    'url' => 'sprout/settings/forms/spam-protection',
                    'template' => 'sprout-forms/settings/spam-protection',
                    'variables' => [
                        'spamRedirectBehaviorOptions' => $this->getSpamRedirectBehaviorsAsOptions()
                    ]
                ],
                'entry-statuses' => [
                    'label' => Craft::t('sprout', 'Entry Statuses'),
                    'url' => 'sprout-forms/settings/forms/entry-statuses',
                    'template' => 'sprout-forms/settings/entrystatuses',
                    'actionButtonTemplate' => 'sprout-forms/settings/entrystatuses/_actionStatusButton',
                    'variables' => [
                        'entryStatuses' => SproutForms::$app->entryStatuses->getAllEntryStatuses()
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function getSpamRedirectBehaviorsAsOptions(): array
    {
        return [
            [
                'label' => 'Redirect as normal (recommended)',
                'value' => self::SPAM_REDIRECT_BEHAVIOR_NORMAL
            ],
            [
                'label' => 'Redirect back to form',
                'value' => self::SPAM_REDIRECT_BEHAVIOR_BACK_TO_FORM
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
}

