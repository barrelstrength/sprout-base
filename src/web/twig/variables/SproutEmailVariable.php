<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\web\twig\variables;

use barrelstrength\sproutbase\app\email\base\EmailTemplates;
use barrelstrength\sproutbase\app\email\base\Mailer;
use barrelstrength\sproutbase\app\email\emailtemplates\BasicTemplates;
use barrelstrength\sproutbase\app\sentemail\elements\SentEmail;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\helpers\UrlHelper;
use yii\base\Exception;

class SproutEmailVariable
{
    /**
     * @param $mailer
     *
     * @return Mailer
     * @throws Exception
     */
    public function getMailer($mailer): Mailer
    {
        return SproutBase::$app->mailers->getMailerByName($mailer);
    }

    public function getSentEmailById($sentEmailId)
    {
        return Craft::$app->getElements()->getElementById($sentEmailId, SentEmail::class);
    }

    /**
     * Get the available Email Template Options
     *
     * @param null $notificationEmail
     *
     * @return array
     */
    public function getEmailTemplateOptions($notificationEmail = null): array
    {
        $defaultEmailTemplates = new BasicTemplates();

        $templates = SproutBase::$app->emailTemplates->getAllEmailTemplates();

        $templateIds = [];
        $options = [
            [
                'label' => Craft::t('sprout', 'Select...'),
                'value' => ''
            ]
        ];

        /**
         * Build our options
         *
         * @var EmailTemplates $template
         */
        foreach ($templates as $template) {
            $type = get_class($template);

            $options[] = [
                'label' => $template->getName(),
                'value' => $type
            ];
            $templateIds[] = $type;
        }

        $templateFolder = null;
        $settings = SproutBase::$app->settings->getSettingsByKey('notifications');

        $templateFolder = $notificationEmail->emailTemplateId ?? $settings->emailTemplateId ?? $defaultEmailTemplates->getPath();

        $options[] = [
            'optgroup' => Craft::t('sprout', 'Custom Template Folder')
        ];

        if (!in_array($templateFolder, $templateIds, false) && $templateFolder != '') {
            $options[] = [
                'label' => $templateFolder,
                'value' => $templateFolder
            ];
        }

        $options[] = [
            'label' => Craft::t('sprout', 'Add Custom'),
            'value' => 'custom'
        ];

        return $options;
    }
}