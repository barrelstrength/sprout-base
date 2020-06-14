<?php

namespace barrelstrength\sproutbase\app\email\services;

use barrelstrength\sproutbase\app\email\base\EmailTemplates as BaseEmailTemplates;
use craft\base\Component;
use craft\events\RegisterComponentTypesEvent;

/**
 *
 * @property array $emailTemplatesTypes
 * @property string[] $allEmailTemplates
 */
class EmailTemplates extends Component
{
    const EVENT_REGISTER_EMAIL_TEMPLATES = 'registerEmailTemplatesEvent';

    public function getEmailTemplatesTypes(): array
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
    public function getAllEmailTemplates(): array
    {
        $templateTypes = $this->getEmailTemplatesTypes();
        $templates = [];

        foreach ($templateTypes as $templateType) {
            $templates[$templateType] = new $templateType();
        }

        uasort($templates, static function($a, $b) {
            /**
             * @var BaseEmailTemplates $a
             * @var BaseEmailTemplates $b
             */
            return $a->getName() <=> $b->getName();
        });

        return $templates;
    }
}