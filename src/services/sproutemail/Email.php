<?php

namespace barrelstrength\sproutbase\services\sproutemail;

use barrelstrength\sproutbase\elements\sproutemail\NotificationEmail;
use barrelstrength\sproutbase\integrations\emailtemplates\BasicTemplates;
use Craft;

use barrelstrength\sproutbase\SproutBase;
use craft\base\Component;


class Email extends Component
{
    /**
     * @param NotificationEmail|null $notificationEmail
     *
     * @return string
     * @throws \yii\base\Exception
     */
    public function getEmailTemplate(NotificationEmail $notificationEmail = null)
    {
        // Set our default
        $defaultEmailTemplates = new BasicTemplates();
        $templatePath = $defaultEmailTemplates->getPath();

        $sproutEmail = Craft::$app->plugins->getPlugin('sprout-email');

        // Allow our settings to override our default
        if ($sproutEmail) {
            $settings = $sproutEmail->getSettings();

            if ($settings->templateFolderOverride) {
                $emailTemplate = SproutBase::$app->template->getTemplateById($settings->templateFolderOverride);
                if ($emailTemplate) {
                    // custom path by template API
                    $templatePath = $emailTemplate->getPath();
                } else {
                    // custom folder on site path
                    $templatePath = $this->getSitePath($settings->templateFolderOverride);
                }
            }
        }

        // Allow our email Element to override our settings
        if ($notificationEmail->emailTemplateId) {
            $emailTemplate = SproutBase::$app->template->getTemplateById($notificationEmail->emailTemplateId);

            if ($emailTemplate) {
                // custom path by template API
                $templatePath = $emailTemplate->getPath();
            } else {
                // custom folder on site path
                $templatePath = $this->getSitePath($notificationEmail->emailTemplateId);
            }
        }

        return $templatePath;
    }

    /**
     * @param $path
     *
     * @return string
     * @throws \yii\base\Exception
     */
    private function getSitePath($path)
    {
        return Craft::$app->path->getSiteTemplatesPath().DIRECTORY_SEPARATOR.$path;
    }
}