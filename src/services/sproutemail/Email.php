<?php

namespace barrelstrength\sproutbase\services\sproutemail;

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
        $plugin = Craft::$app->plugins->getPlugin('sprout-email');
        $settings = null;
        if ($plugin) {
            $settings = $plugin->getSettings();
        }

        // Return empty if sprout email is disabled or not installed
       if ($settings == null) return "";

        $templateFolderOverride = $settings->templateFolderOverride;

        if (!empty($templateFolderOverride)) {
            $template = $templateFolderOverride;

            Craft::$app->getView()->setTemplateMode(View::TEMPLATE_MODE_SITE);
        }

        $templateObj = SproutBase::$app->sproutEmail->getTemplateById($templateFolderOverride);

        if ($templateObj) {
            $template = $templateObj->getPath();

            Craft::$app->getView()->setTemplateMode(View::TEMPLATE_MODE_CP);
        }

        return $template;
    }
}