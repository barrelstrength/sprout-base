<?php

namespace barrelstrength\sproutbase\services\sproutemail;

use barrelstrength\sproutbase\elements\sproutemail\NotificationEmail;
use barrelstrength\sproutbase\integrations\emailtemplates\BasicTemplates;
use Craft;
use barrelstrength\sproutbase\contracts\sproutemail\BaseEmailTemplates;
use barrelstrength\sproutbase\SproutBase;
use craft\base\Component;
use craft\events\RegisterComponentTypesEvent;
use craft\web\View;

class Email extends Component
{
    /**
     * @return mixed|string
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
        if ($notificationEmail->template) {
            $emailTemplate = SproutBase::$app->template->getTemplateById($notificationEmail->template);

            if ($emailTemplate) {
                // custom path by template API
                $templatePath = $emailTemplate->getPath();
            } else {
                // custom folder on site path
                $templatePath = $this->getSitePath($notificationEmail->template);
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