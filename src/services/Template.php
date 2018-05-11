<?php

namespace barrelstrength\sproutbase\services;

use barrelstrength\sproutbase\app\email\contracts\BaseEmailTemplates;
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
            /**
             * @var BaseEmailTemplates $a
             * @var BaseEmailTemplates $b
             */
            return $a->getName() <=> $b->getName();
        });

        return $templates;
    }

    /**
     * @param $templateId
     *
     * @return BaseEmailTemplates|null
     * @throws \ReflectionException
     */
    public function getTemplateById($templateId)
    {
        $templates = $this->getAllGlobalTemplates();

        /**
         * @var BaseEmailTemplates $template
         */
        foreach ($templates as $template) {
            if ($template->getTemplateId() == $templateId) {
                return $template;
            }
        }

        return null;
    }
}