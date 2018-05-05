<?php

namespace barrelstrength\sproutbase\sproutbase\services;

use barrelstrength\sproutbase\sproutemail\contracts\BaseEmailTemplates;
use craft\base\Component;

use craft\events\RegisterComponentTypesEvent;

class Template extends Component
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

    /**
     * Returns all available Global Form Templates
     *
     * @return string[]
     */
    public function getAllGlobalTemplates()
    {
        $templateTypes = $this->getAllGlobalTemplateTypes();
        $templates = [];

        foreach ($templateTypes as $templateType) {
            $templates[$templateType] = new $templateType();
        }

        uasort($templates, function($a, $b) {
            return $a->getName() <=> $b->getName();
        });

        return $templates;
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
}