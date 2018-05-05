<?php

namespace barrelstrength\sproutbase\sproutemail\web\twig\variables;

use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\sproutemail\elements\NotificationEmail;
use barrelstrength\sproutbase\sproutemail\integrations\sproutemail\emailtemplates\BasicTemplates;
use barrelstrength\sproutemail\SproutEmail;
use Craft;
use craft\helpers\UrlHelper;

class SproutEmailVariable
{
    public function getCampaignMailers()
    {
        return SproutBase::$app->mailers->getMailers();
    }

    public function getCampaignTypes()
    {
        return SproutEmail::$app->campaignTypes->getCampaignTypes();
    }

    public function getMailer($mailer)
    {
        return SproutBase::$app->mailers->getMailerByName($mailer);
    }

    /**
     * Returns the value of the displayDateScheduled general config setting
     *
     * @return mixed|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getDisplayDateScheduled()
    {
        return SproutEmail::$app->getConfig('displayDateScheduled', false);
    }

    public function getCampaignEmailById($id)
    {
        return SproutEmail::$app->campaignEmails->getCampaignEmailById($id);
    }

    public function getSentEmailById($sentEmailId)
    {
        return Craft::$app->getElements()->getElementById($sentEmailId);
    }

    /**
     * Returns a Campaign Email Share URL and Token
     *
     * @param $emailId
     * @param $campaignTypeId
     *
     * @return array|string
     */
    public function getCampaignEmailShareUrl($emailId, $campaignTypeId)
    {
        return UrlHelper::actionUrl('sprout-email/campaign-email/share-campaign-email', [
            'emailId' => $emailId,
            'campaignTypeId' => $campaignTypeId
        ]);
    }

    public function getNotificationEmailById($id)
    {
        return SproutBase::$app->notifications->getNotificationEmailById($id);
    }

    /**
     * Get the available Email Template Options
     *
     * @param NotificationEmail|null $notificationEmail
     *
     * @return array
     */
    public function getEmailTemplateOptions($notificationEmail = null)
    {
        $defaultEmailTemplates = new BasicTemplates();
        $templates = SproutBase::$app->template->getAllGlobalTemplates();
        $templateIds = [];
        $options = [
            [
                'label' => Craft::t('sprout-base', 'Select...'),
                'value' => ''
            ]
        ];

        // Build our options
        foreach ($templates as $template) {
            $options[] = [
                'label' => $template->getName(),
                'value' => $template->getTemplateId()
            ];
            $templateIds[] = $template->getTemplateId();
        }

        $templateFolder = null;
        $plugin = Craft::$app->getPlugins()->getPlugin('sprout-email');

        if ($plugin) {
            $settings = $plugin->getSettings();
        }

        $templateFolder = $notificationEmail->emailTemplateId ?? $settings->emailTemplateId ?? $defaultEmailTemplates->getPath();

        $options[] = [
            'optgroup' => Craft::t('sprout-base', 'Custom Template Folder')
        ];

        if (!in_array($templateFolder, $templateIds, false) && $templateFolder != '') {
            $options[] = [
                'label' => $templateFolder,
                'value' => $templateFolder
            ];
        }

        $options[] = [
            'label' => Craft::t('sprout-base', 'Add Custom'),
            'value' => 'custom'
        ];

        return $options;
    }
}