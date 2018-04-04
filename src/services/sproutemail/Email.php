<?php

namespace barrelstrength\sproutbase\services\sproutemail;


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
        $templateTypes = $this->getAllGlobalTemplates();

        $options = SproutBase::$app->template->getAllGlobalTemplates($templateTypes);

        return $options;
    }
}