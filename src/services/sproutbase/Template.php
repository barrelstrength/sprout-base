<?php

namespace barrelstrength\sproutbase\services\sproutbase;

use craft\base\Component;
use Craft;

class Template extends Component
{
    /**
     * Returns all available Global Form Templates
     *
     * @return string[]
     */
    public function getAllGlobalTemplates($templateTypes)
    {
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
     * @return array
     */
    public function getTemplateOptions($templates, $pluginName = '')
    {
        $templateIds = [];
        $options = [
            [
                'label' => \Craft::t('sprout-base', 'Select...'),
                'value' => ''
            ]
        ];

        foreach ($templates as $template) {
            $options[] = [
                'label' => $template->getName(),
                'value' => $template->getTemplateId()
            ];
            $templateIds[] = $template->getTemplateId();
        }

        $templateFolder = '';
        if ($pluginName != '') {
            $plugin = Craft::$app->getPlugins()->getPlugin($pluginName);
            $settings = $plugin->getSettings();
            $templateFolder = $settings->templateFolderOverride;
        }

        $options[] = [
            'optgroup' => Craft::t('sprout-base', 'Custom Template Folder')
        ];

        if (!in_array($templateFolder, $templateIds) && $templateFolder != '') {
            $options[] = [
                'label' => $templateFolder,
                'value' => $templateFolder
            ];
        }

        $options[] = [
            'label' => Craft::t('sprout-base', 'Add Custom'),
            'value' => 'custom'
        ];

        return $options;
    }
}