<?php

namespace barrelstrength\sproutbase\app\email\services;

use barrelstrength\sproutbase\app\email\base\EmailTemplates as BaseEmailTemplates;
use barrelstrength\sproutbase\app\email\emailtemplates\BasicTemplates;
use barrelstrength\sproutbase\SproutBase;
use Craft;
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

    /**
     * Get the available Email Template Options
     *
     * @param null $emailTemplateId
     *
     * @return array
     */
    public function getEmailTemplateOptions($emailTemplateId = null): array
    {
        $defaultEmailTemplates = new BasicTemplates();

        $templates = $this->getAllEmailTemplates();

        $templateIds = [];

        $options = [
            [
                'label' => Craft::t('sprout', 'Select...'),
                'value' => ''
            ]
        ];

        /**
         * Build our options
         *
         * @var BaseEmailTemplates $template
         */
        foreach ($templates as $template) {
            $type = get_class($template);

            $options[] = [
                'label' => $template->getName(),
                'value' => $type
            ];
            $templateIds[] = $type;
        }

        $templateFolder = null;
        $settings = SproutBase::$app->settings->getSettingsByKey('notifications');

        $templateFolder = $emailTemplateId ?? $settings->emailTemplateId ?? $defaultEmailTemplates->getPath();

        $options[] = [
            'optgroup' => Craft::t('sprout', 'Custom Template Folder')
        ];

        if (!in_array($templateFolder, $templateIds, false) && $templateFolder != '') {
            $options[] = [
                'label' => $templateFolder,
                'value' => $templateFolder
            ];
        }

        $options[] = [
            'label' => Craft::t('sprout', 'Add Custom'),
            'value' => 'custom'
        ];

        return $options;
    }
}