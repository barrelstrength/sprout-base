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
    const EVENT_REGISTER_EMAIL_TEMPLATES = 'registerEmailTemplatesEvent';

    public function getAllGlobalTemplateTypes()
    {
        $event = new RegisterComponentTypesEvent([
            'types' => []
        ]);

        $this->trigger(self::EVENT_REGISTER_EMAIL_TEMPLATES, $event);

        return $event->types;
    }

    public function getAllGlobalTemplates()
    {
        $templateTypes = $this->getAllGlobalTemplateTypes();

        return SproutBase::$app->template->getAllGlobalTemplates($templateTypes);
    }

    public function getTemplateOptions()
    {
        $templates = $this->getAllGlobalTemplates();

        return SproutBase::$app->template->getTemplateOptions($templates, 'sprout-email');
    }
    /**
     * @param $templateId
     *
     * @return null|BaseEmailTemplates
     */
    public function getTemplateById($templateId)
    {
        $templates = $this->getAllGlobalTemplates();

        foreach ($templates as $template) {
            if ($template->getTemplateId() == $templateId) {
                return $template;
            }
        }

        return null;
    }

    /**
     * @return mixed|string
     * @throws \yii\base\Exception
     */
    public function getEmailTemplates(NotificationEmail $notificationEmail = null)
    {
        // Set our default
        $defaultEmailTemplates = new BasicTemplates();
        $templatePath = $defaultEmailTemplates->getPath();

        $settings = Craft::$app->plugins->getPlugin('sprout-email')->getSettings();

        // Allow our settings to override our default
        if ($settings->templateFolderOverride) {
            $emailTemplate = $this->getTemplateById($settings->templateFolderOverride);
            if ($emailTemplate) {
                // custom path by template API
                $templatePath = $emailTemplate->getPath();
            } else {
                // custom folder on site path
                $templatePath = $this->getSitePath($settings->templateFolderOverride);
            }
        }

        // Allow our email Element to override our settings
        if ($notificationEmail->template) {
            $emailTemplate = $this->getTemplateById($sproutEmail->getSettings()->template);

            if ($emailTemplate) {
                // custom path by template API
                $templatePath = $emailTemplate->getPath();

//                Craft::$app->getView()->setTemplateMode(View::TEMPLATE_MODE_CP);
            } else {
                // custom folder on site path
                $templatePath = $this->getSitePath($settings->template);

//                Craft::$app->getView()->setTemplateMode(View::TEMPLATE_MODE_SITE);
            }
        }

        return $templatePath;
    }
}