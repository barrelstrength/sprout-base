<?php

namespace barrelstrength\sproutbase\services\sproutemail;

use Craft;
use barrelstrength\sproutbase\contracts\sproutemail\BaseEmailTemplates;
use barrelstrength\sproutbase\SproutBase;
use craft\base\Component;
use craft\events\RegisterComponentTypesEvent;

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

    public function getTemplateOverride()
    {
        $settings = Craft::$app->plugins->getPlugin('sprout-email')->getSettings();

        $templateFolderOverride = $settings->templateFolderOverride;

        $template = SproutBase::$app->sproutEmail->getTemplateById($templateFolderOverride);

        return $template;
    }
}