<?php

namespace barrelstrength\sproutbase\services\sproutemail;

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
    public function getTemplateOverride()
    {
        $defaultEmailTemplates = new BasicTemplates();
        $templatePath = $defaultEmailTemplates->getPath();

        $sproutEmail = Craft::$app->plugins->getPlugin('sprout-email');

        if ($sproutEmail && $sproutEmail->getSettings()->templateFolderOverride) {
            $emailTemplate = $this->getTemplateById($settings->templateFolderOverride);
            if ($emailTemplate) {
                // custom path by template API
                $templatePath = $emailTemplate->getPath();

                Craft::$app->getView()->setTemplateMode(View::TEMPLATE_MODE_CP);
            } else {
                // custom folder on site path
                $templatePath = $this->getSitePath($settings->templateFolderOverride);

                Craft::$app->getView()->setTemplateMode(View::TEMPLATE_MODE_SITE);
            }
        }

//        if ($form->templateOverridesFolder) {
//            $formTemplatePath = $this->getTemplateById($form->templateOverridesFolder);
//            if ($formTemplatePath) {
//                // custom path by template API
//                $templateFolderOverride = $formTemplatePath->getPath();
//            } else {
//                // custom folder on site path
//                $templateFolderOverride = $this->getSitePath($form->templateOverridesFolder);
//            }
//        }

//        if (!empty($templateFolderOverride)) {
//            $templatePath = $templateFolderOverride;
//
//        }
//
//        $emailTemplates = SproutBase::$app->sproutEmail->getTemplateById($templateFolderOverride);
//
//        if ($emailTemplates) {
//
//            $templatePath = $emailTemplates->getPath();
//
//
//        }

        return $templatePath;
    }
}