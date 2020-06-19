<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\services;

use barrelstrength\sproutbase\app\forms\base\FormTemplates as BaseFormTemplates;
use barrelstrength\sproutbase\app\forms\elements\Form;
use barrelstrength\sproutbase\app\forms\elements\Form as FormElement;
use barrelstrength\sproutbase\app\forms\errors\FormTemplatesDirectoryNotFoundException;
use barrelstrength\sproutbase\app\forms\formtemplates\AccessibleTemplates;
use barrelstrength\sproutbase\app\forms\formtemplates\CustomTemplates;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\events\RegisterComponentTypesEvent;
use yii\base\Component;
use yii\base\Exception;

class FormTemplates extends Component
{
    const EVENT_REGISTER_FORM_TEMPLATES = 'registerFormTemplatesEvent';

    /**
     * Returns all available Form Templates Class Names
     *
     * @return array
     */
    public function getAllFormTemplateTypes(): array
    {
        $event = new RegisterComponentTypesEvent([
            'types' => []
        ]);

        $this->trigger(self::EVENT_REGISTER_FORM_TEMPLATES, $event);

        return $event->types;
    }

    /**
     * Returns all available Form Templates
     *
     * @return BaseFormTemplates[]
     */
    public function getAllFormTemplates(): array
    {
        $templateTypes = $this->getAllFormTemplateTypes();
        $templates = [];

        foreach ($templateTypes as $templateType) {
            $templates[$templateType] = new $templateType();
        }

        uasort($templates, static function($a, $b) {
            /**
             * @var $a BaseFormTemplates
             * @var $b BaseFormTemplates
             */
            return $a->getName() <=> $b->getName();
        });

        return $templates;
    }

    /**
     * @param FormElement $form
     *
     * @return array
     * @throws Exception
     */
    public function getFormTemplatePaths(FormElement $form): array
    {
        $settings = SproutBase::$app->settings->getSettingsByKey('forms');

        $templates = [];
        $templateFolder = '';
        $fallbackFormTemplates = new AccessibleTemplates();
        $defaultTemplate = $fallbackFormTemplates->getFullPath();

        if ($settings->formTemplateId) {
            $defaultFormTemplates = $this->getFormTemplateById($settings->formTemplateId);
            if ($defaultFormTemplates) {
                // custom path by template API
                $templateFolder = $defaultFormTemplates->getFullPath();
            } else {
                // custom folder on site path
                $templateFolder = $this->getSitePath($settings->formTemplateId);
            }
        }

        if ($form->formTemplateId) {
            $formTemplates = $this->getFormTemplateById($form->formTemplateId);
            if ($formTemplates) {
                // custom path by template API
                $templateFolder = $formTemplates->getFullPath();
            } else {
                // custom folder on site path
                $templateFolder = $this->getSitePath($form->formTemplateId);
            }
        }

        // Set our defaults
        $templates['form'] = $defaultTemplate;
        $templates['tab'] = $defaultTemplate;
        $templates['field'] = $defaultTemplate;
        $templates['fields'] = $defaultTemplate;
        $templates['email'] = $defaultTemplate;

        // See if we should override our defaults
        if ($templateFolder) {

            $formTemplate = $templateFolder.DIRECTORY_SEPARATOR.'form';
            $tabTemplate = $templateFolder.DIRECTORY_SEPARATOR.'tab';
            $fieldTemplate = $templateFolder.DIRECTORY_SEPARATOR.'field';
            $fieldsFolder = $templateFolder.DIRECTORY_SEPARATOR.'fields';
            $emailTemplate = $templateFolder.DIRECTORY_SEPARATOR.'email';
            $basePath = $templateFolder.DIRECTORY_SEPARATOR;

            foreach (Craft::$app->getConfig()->getGeneral()->defaultTemplateExtensions as $extension) {

                if (file_exists($formTemplate.'.'.$extension)) {
                    $templates['form'] = $basePath;
                }

                if (file_exists($tabTemplate.'.'.$extension)) {
                    $templates['tab'] = $basePath;
                }

                if (file_exists($fieldTemplate.'.'.$extension)) {
                    $templates['field'] = $basePath;
                }

                if (file_exists($fieldsFolder)) {
                    $templates['fields'] = $basePath.'fields';
                }

                if (file_exists($emailTemplate.'.'.$extension)) {
                    $templates['email'] = $basePath;
                }
            }

            if (file_exists($fieldsFolder)) {
                $templates['fields'] = $basePath.'fields';
            }
        }

        return $templates;
    }

    /**
     * @param $templateId
     *
     * @return BaseFormTemplates
     * @throws FormTemplatesDirectoryNotFoundException
     */
    public function getFormTemplateById($templateId): BaseFormTemplates
    {
        $formTemplates = null;

        if (class_exists($templateId)) {
            /** @var FormTemplates $templateId */
            $formTemplates = new $templateId();
        }

        if ($formTemplates instanceof BaseFormTemplates === false) {
            $formTemplates = new CustomTemplates();
            $formTemplates->setPath($templateId);
        }

        if (!is_dir($formTemplates->getFullPath())) {
            throw new FormTemplatesDirectoryNotFoundException('Unable to find Form Templates directory: '.$formTemplates->getFullPath());
        }

        return $formTemplates;
    }

    /**
     * @param Form|null $form
     * @param bool $generalSettings
     *
     * @return array
     */
    public function getFormTemplateOptions(Form $form = null, $generalSettings = false): array
    {
        $defaultFormTemplates = new AccessibleTemplates();

        if ($generalSettings) {
            $options[] = [
                'optgroup' => Craft::t('sprout', 'Global Templates')
            ];

            $options[] = [
                'label' => Craft::t('sprout', 'Default Form Templates'),
                'value' => null
            ];
        }

        $templates = $this->getAllFormTemplates();
        $templateIds = [];

        if ($generalSettings) {
            $options[] = [
                'optgroup' => Craft::t('sprout', 'Form-Specific Templates')
            ];
        }

        foreach ($templates as $template) {
            $options[] = [
                'label' => $template->getName(),
                'value' => get_class($template)
            ];
            $templateIds[] = get_class($template);
        }

        $templateFolder = null;
        $settings = SproutBase::$app->settings->getSettingsByKey('forms');

        $templateFolder = $form->formTemplateId ?? $settings->formTemplateId ?? AccessibleTemplates::class;

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

    /**
     * @param $path
     *
     * @return string
     * @throws Exception
     */
    private function getSitePath($path): string
    {
        return Craft::$app->path->getSiteTemplatesPath().DIRECTORY_SEPARATOR.$path;
    }
}
