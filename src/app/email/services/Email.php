<?php

namespace barrelstrength\sproutbase\app\email\services;

use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\app\email\elements\NotificationEmail;
use barrelstrength\sproutbase\app\email\base\EmailTemplates;
use barrelstrength\sproutbase\app\email\emailtemplates\BasicTemplates;
use Craft;
use craft\events\RegisterComponentTypesEvent;
use craft\base\Component;

class Email extends Component
{
    const EVENT_REGISTER_EMAIL_TEMPLATES = 'registerEmailTemplatesEvent';

    public function getAllEmailTemplateTypes()
    {
        $event = new RegisterComponentTypesEvent([
            'types' => []
        ]);

        $this->trigger(self::EVENT_REGISTER_EMAIL_TEMPLATES, $event);

        return $event->types;
    }

    /**
     * Returns all available Global Form Templates
     *
     * @return string[]
     */
    public function getAllEmailTemplates()
    {
        $templateTypes = $this->getAllEmailTemplateTypes();
        $templates = [];

        foreach ($templateTypes as $templateType) {
            $templates[$templateType] = new $templateType();
        }

        uasort($templates, function($a, $b) {
            /**
             * @var EmailTemplates $a
             * @var EmailTemplates $b
             */
            return $a->getName() <=> $b->getName();
        });

        return $templates;
    }

    /**
     * @param NotificationEmail|null $notificationEmail
     *
     * @return string
     * @throws \ReflectionException
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

            if ($settings->emailTemplateId) {
                $emailTemplate = new BasicTemplates();
                if ($emailTemplate) {
                    // custom path by template API
                    $templatePath = $emailTemplate->getPath();
                } else {
                    // custom folder on site path
                    $templatePath = $this->getSitePath($settings->emailTemplateId);
                }
            }
        }

        // Allow our email Element to override our settings
        if ($notificationEmail->emailTemplateId) {
            $emailTemplate = new $notificationEmail->emailTemplateId();

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