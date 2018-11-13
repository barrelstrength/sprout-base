<?php

namespace barrelstrength\sproutbase\app\email\web\twig\variables;

use barrelstrength\sproutbase\app\email\base\Mailer;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\app\email\base\EmailTemplates;
use barrelstrength\sproutbase\app\email\emailtemplates\BasicTemplates;
use barrelstrength\sproutemail\SproutEmail;
use Craft;
use craft\helpers\UrlHelper;

class SproutEmailVariable
{
    /**
     * @return Mailer[]
     */
    public function getCampaignMailers()
    {
        return SproutBase::$app->mailers->getMailers();
    }

    /**
     * @return array
     */
    public function getCampaignTypes()
    {
        return SproutEmail::$app->campaignTypes->getCampaignTypes();
    }

    /**
     * @param $mailer
     *
     * @return Mailer
     * @throws \yii\base\Exception
     */
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
        $config = Craft::$app->getConfig()->getConfigSettings('general');

        if (!is_array($config)) {
            return false;
        }

        $dateScheduled = isset($config->displayDateScheduled) ? $config->displayDateScheduled : false;

        return $dateScheduled;
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
     * @param null $notificationEmail
     *
     * @return array
     */
    public function getEmailTemplateOptions($notificationEmail = null)
    {
        $defaultEmailTemplates = new BasicTemplates();

        $templates = SproutBase::$app->sproutEmail->getAllEmailTemplates();

        $templateIds = [];
        $options = [
            [
                'label' => Craft::t('sprout-base', 'Select...'),
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

    /**
     * Trigger a cleanUpSentEmails Job
     */
    public function cleanUpSentEmails()
    {
        SproutEmail::$app->sentEmails->cleanUpSentEmails();
    }
}