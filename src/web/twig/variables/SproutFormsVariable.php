<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\web\twig\variables;

use barrelstrength\sproutbase\app\forms\base\Condition;
use barrelstrength\sproutbase\app\forms\base\FormField;
use barrelstrength\sproutbase\app\forms\elements\db\EntryQuery;
use barrelstrength\sproutbase\app\forms\elements\Entry;
use barrelstrength\sproutbase\app\forms\elements\Entry as EntryElement;
use barrelstrength\sproutbase\app\forms\elements\Form;
use barrelstrength\sproutbase\app\forms\formtemplates\AccessibleTemplates;
use barrelstrength\sproutbase\app\forms\services\Forms;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\ElementInterface;
use craft\errors\ElementNotFoundException;
use craft\errors\MissingComponentException;
use craft\helpers\Template as TemplateHelper;
use ReflectionException;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Markup;
use yii\base\Exception;
use yii\web\BadRequestHttpException;

/**
 * SproutForms provides an API for accessing information about forms. It is accessible from templates via `craft.sproutForms`.
 *
 */
class SproutFormsVariable
{
    /**
     * Returns a complete form for display in template
     *
     * @param            $formHandle
     * @param array|null $renderingOptions
     *
     * @return Markup
     * @throws \Exception
     * @throws Exception
     */
    public function displayForm($formHandle, array $renderingOptions = null): Markup
    {
        /**
         * @var $form Form
         */
        $form = SproutBase::$app->forms->getFormByHandle($formHandle);

        if (!$form) {
            throw new Exception('Unable to find form with the handle: '.$formHandle);
        }

        $view = Craft::$app->getView();

        $entry = SproutBase::$app->formEntries->getEntry($form);

        $templatePaths = SproutBase::$app->forms->getFormTemplatePaths($form);

        // Check if we need to update our Front-end Form Template Path
        $view->setTemplatesPath($templatePaths['form']);

        // Build our complete form
        $formHtml = $view->renderTemplate('form', [
                'form' => $form,
                'entry' => $entry,
                'renderingOptions' => $renderingOptions
            ]
        );

        $view->setTemplatesPath(Craft::$app->path->getSiteTemplatesPath());

        return TemplateHelper::raw($formHtml);
    }

    /**
     * @param Form $form
     * @param int $tabId
     * @param array|null $renderingOptions
     *
     * @return bool|Markup
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function displayTab(Form $form, int $tabId, array $renderingOptions = null)
    {
        if (!$form) {
            throw new Exception('The displayTab tag requires a Form model.');
        }

        if (!$tabId) {
            throw new Exception('The displayTab tag requires a Tab ID.');
        }

        $view = Craft::$app->getView();

        $entry = SproutBase::$app->formEntries->getEntry($form);

        $templatePaths = SproutBase::$app->forms->getFormTemplatePaths($form);

        // Set Tab template path
        $view->setTemplatesPath($templatePaths['tab']);

        $tabIndex = null;

        $layoutTabs = $form->getFieldLayout()->getTabs();

        foreach ($layoutTabs as $key => $tabInfo) {
            if ($tabId == $tabInfo->id) {
                $tabIndex = $key;
            }
        }

        if ($tabIndex === null) {
            return false;
        }

        $layoutTab = $layoutTabs[$tabIndex] ?? null;

        // Build the HTML for our form tabs and fields
        $tabHtml = $view->renderTemplate('tab', [
            'form' => $form,
            'entry' => $entry,
            'tabs' => [$layoutTab],
            'renderingOptions' => $renderingOptions
        ]);

        $siteTemplatesPath = Craft::$app->path->getSiteTemplatesPath();

        $view->setTemplatesPath($siteTemplatesPath);

        return TemplateHelper::raw($tabHtml);
    }

    /**
     * Returns a complete field for display in template
     *
     * @param Form $form
     * @param FormField $field
     * @param array|null $renderingOptions
     *
     * @return Markup
     * @throws Exception
     * @throws ReflectionException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function displayField(Form $form, $field, array $renderingOptions = null): Markup
    {
        if (!$form) {
            throw new Exception('The displayField tag requires a Form model.');
        }

        if (!$this->validateField($field)) {
            throw new Exception('The displayField tag requires a Field model.');
        }

        if ($renderingOptions !== null) {
            $renderingOptions = [
                'fields' => $renderingOptions['fields'] ?? null
            ];
        }

        $view = Craft::$app->getView();

        $entry = SproutBase::$app->formEntries->getEntry($form);

        $templatePaths = SproutBase::$app->forms->getFormTemplatePaths($form);

        $view->setTemplatesPath($field->getTemplatesPath());

        $inputFilePath = $templatePaths['fields'].DIRECTORY_SEPARATOR.$field->getFieldInputFolder().DIRECTORY_SEPARATOR.'input';

        // Allow input field templates to be overridden
        foreach (Craft::$app->getConfig()->getGeneral()->defaultTemplateExtensions as $extension) {
            if (file_exists($inputFilePath.'.'.$extension)) {

                // Override Field Input template path
                $view->setTemplatesPath($templatePaths['fields']);
                break;
            }
        }

        $globalFieldRenderingOptions = $renderingOptions['fields']['*'] ?? [];
        $fieldSpecificRenderingOptions = $renderingOptions['fields'][$field->handle] ?? [];

        $fieldRenderingOptionsInput = $this->processFieldRenderingOptions($fieldSpecificRenderingOptions, $globalFieldRenderingOptions, 'input');
        $fieldRenderingOptionsWrapper = $this->processFieldRenderingOptions($fieldSpecificRenderingOptions, $globalFieldRenderingOptions, 'container');

        $value = $entry->getFieldValue($field->handle);

        $inputHtml = $field->getFrontEndInputHtml($value, $entry, $fieldRenderingOptionsInput);

        // Set Field template path (we handled the case for overriding the field input templates above)
        $view->setTemplatesPath($templatePaths['field']);

        // Build the HTML for our form field
        $fieldHtml = $view->renderTemplate('field', [
                'form' => $form,
                'entry' => $entry,
                'field' => $field,
                'input' => $inputHtml,
                'renderingOptions' => [
                    'fields' => [
                        $field->handle => $fieldRenderingOptionsWrapper
                    ]
                ]
            ]
        );

        $view->setTemplatesPath(Craft::$app->path->getSiteTemplatesPath());

        return TemplateHelper::raw($fieldHtml);
    }

    /**
     * Gets a specific form. If no form is found, returns null
     *
     * @param int $id
     *
     * @return mixed
     */
    public function getFormById($id)
    {
        return SproutBase::$app->forms->getFormById($id);
    }

    /**
     * Gets a specific form by handle. If no form is found, returns null
     *
     * @param string $formHandle
     *
     * @return mixed
     */
    public function getForm($formHandle)
    {
        return SproutBase::$app->forms->getFormByHandle($formHandle);
    }

    /**
     * Get all forms
     *
     * @return array
     */
    public function getAllForms(): array
    {
        return SproutBase::$app->forms->getAllForms();
    }

    /**
     * Gets entry by ID
     *
     * @param $id
     *
     * @return ElementInterface|null
     */
    public function getEntryById($id)
    {
        return SproutBase::$app->formEntries->getEntryById($id);
    }

    /**
     * Returns an active or new entry model
     *
     * @param Form $form
     *
     * @return mixed
     */
    public function getEntry(Form $form)
    {
        return SproutBase::$app->formEntries->getEntry($form);
    }

    /**
     * Set an active entry for use in your Form Templates
     *
     * See the Entries service setEntry method for more details.
     *
     * @param Form $form
     * @param EntryElement $entry
     */
    public function setEntry(Form $form, Entry $entry)
    {
        SproutBase::$app->formEntries->setEntry($form, $entry);
    }

    /**
     * Gets last entry submitted and cleans up lastEntryId from session
     *
     * @param null $formId
     *
     * @return array|ElementInterface|null
     * @throws MissingComponentException
     * @throws ElementNotFoundException
     */
    public function getLastEntry($formId = null)
    {
        if ($entryId = Craft::$app->getSession()->get('lastEntryId')) {
            $entry = SproutBase::$app->formEntries->getEntryById($entryId);

            if (!$entry) {
                return null;
            }

            if (!$formId || $formId === $entry->getForm()->id) {
                Craft::$app->getSession()->remove('lastEntryId');
            }
        }

        return $entry ?? null;
    }

    /**
     * Gets Form Groups
     *
     * @param int $id Group ID (optional)
     *
     * @return array
     */
    public function getAllFormGroups($id = null): array
    {
        return SproutBase::$app->formGroups->getAllFormGroups($id);
    }

    /**
     * Gets all forms in a specific group by ID
     *
     * @param $id
     *
     * @return Form[]
     */
    public function getFormsByGroupId($id): array
    {
        return SproutBase::$app->formGroups->getFormsByGroupId($id);
    }

    /**
     * @param $settings
     *
     * @throws MissingComponentException
     */
    public function multiStepForm($settings)
    {
        $currentStep = $settings['currentStep'] ?? null;
        $totalSteps = $settings['totalSteps'] ?? null;

        if (!$currentStep or !$totalSteps) {
            return;
        }

        if ($currentStep == 1) {
            // Make sure we are starting from scratch
            Craft::$app->getSession()->remove('multiStepForm');
            Craft::$app->getSession()->remove('multiStepFormEntryId');
            Craft::$app->getSession()->remove('currentStep');
            Craft::$app->getSession()->remove('totalSteps');
        }

        Craft::$app->getSession()->set('multiStepForm', true);
        Craft::$app->getSession()->set('currentStep', $currentStep);
        Craft::$app->getSession()->set('totalSteps', $totalSteps);
    }

    /**
     * @param $type
     *
     * @return FormField|mixed|null
     * @throws Exception
     */
    public function getRegisteredField($type)
    {
        $fields = SproutBase::$app->formFields->getRegisteredFields();

        foreach ($fields as $field) {
            if ($field->getType() == $type) {
                return $field;
            }
        }

        $message = Craft::t('sprout', '{type} field does not support front-end display using Sprout Forms.', [
                'type' => $type
            ]
        );

        Craft::error($message, __METHOD__);

        if (Craft::$app->getConfig()->getGeneral()->devMode) {
            throw new Exception($message);
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getTemplatesPath()
    {
        return Craft::$app->getView()->getTemplatesPath();
    }

    /**
     * @param array $variables
     */
    public function addFieldVariables(array $variables)
    {
        Forms::addFieldVariables($variables);
    }

    /**
     * @param string
     *
     * @return bool
     */
    public function isPluginInstalled($plugin): bool
    {
        $plugins = Craft::$app->plugins->getAllPlugins();

        if (array_key_exists($plugin, $plugins)) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getEntryStatuses(): array
    {
        return SproutBase::$app->formEntryStatuses->getAllEntryStatuses();
    }

    /**
     * @return array|FormField[]
     */
    public function getRegisteredFields(): array
    {
        return SproutBase::$app->formFields->getRegisteredFields();
    }

    /**
     * @return array
     */
    public function getRegisteredFieldsByGroup(): array
    {
        return SproutBase::$app->formFields->getRegisteredFieldsByGroup();
    }

    /**
     * @param $registeredFields
     * @param $sproutFormsFields
     *
     * @return mixed
     */
    public function getCustomFields($registeredFields, $sproutFormsFields)
    {
        foreach ($sproutFormsFields as $group) {
            foreach ($group as $field) {
                unset($registeredFields[$field]);
            }
        }

        return $registeredFields;
    }

    /**
     * @param $field
     *
     * @return string
     */
    public function getFieldClassName($field): string
    {
        return get_class($field);
    }

    /**
     * @return array
     */
    public function getAllCaptchas(): array
    {
        return SproutBase::$app->forms->getAllCaptchas();
    }

    /**
     * @param Form|null $form
     * @param bool $generalSettings
     *
     * @return array
     */
    public function getTemplateOptions(Form $form = null, $generalSettings = false): array
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

        $templates = SproutBase::$app->forms->getAllFormTemplates();
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
     * Returns a new EntryQuery instance.
     *
     * @param mixed $criteria
     *
     * @return EntryQuery
     */
    public function entries($criteria = null): EntryQuery
    {
        $query = EntryElement::find();
        if ($criteria) {
            Craft::configure($query, $criteria);
        }

        return $query;
    }

    /**
     * @return array
     */
    public function getIntegrationOptions(): array
    {
        $integrations = SproutBase::$app->integrations->getAllIntegrations();

        $options[] = [
            'label' => Craft::t('sprout', 'Add Integration...'),
            'value' => ''
        ];

        foreach ($integrations as $integration) {
            $options[] = [
                'label' => $integration::displayName(),
                'value' => get_class($integration)
            ];
        }

        return $options;
    }


    /**
     * @param $field
     *
     * @return mixed
     */
    public function validateField($field)
    {
        return method_exists($field, 'getFrontEndInputHtml');
    }

    /**
     * @param $field
     *
     * @return mixed
     */
    public function getFieldClass($field)
    {
        return get_class($field);
    }

    /**
     * @param $formFieldHandle
     * @param $formId
     *
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function getFormField($formFieldHandle, $formId)
    {
        $form = Craft::$app->elements->getElementById($formId);

        if (!$form) {
            throw new BadRequestHttpException('No form exists with the ID '.$formId);
        }

        return $form->getField($formFieldHandle);
    }

    /**
     * @param $conditionClass
     * @param $formField
     *
     * @return Condition
     */
    public function getFieldCondition($conditionClass, $formField): Condition
    {
        return new $conditionClass(['formField' => $formField]);
    }

    /**
     * @param       $fieldSpecificRenderingOptions
     * @param       $globalFieldRenderingOptions
     * @param       $targetSettingsHandle
     *
     * @return array
     */
    protected function processFieldRenderingOptions($fieldSpecificRenderingOptions, $globalFieldRenderingOptions, $targetSettingsHandle): array
    {
        $fieldRenderingOptions = [];

        $supportedFieldRenderingOptions = ['id', 'class', 'errorClass', 'data'];

        foreach ($supportedFieldRenderingOptions as $fieldRenderingOption) {
            switch ($fieldRenderingOption) {
                case 'id':
                    $fieldRenderingOptions['id'] = $fieldSpecificRenderingOptions['id'] ?? null;
                    break;
                case 'class':

                    if (isset($globalFieldRenderingOptions['class'])
                        && is_array($globalFieldRenderingOptions['class'])) {
                        $class[] = $globalFieldRenderingOptions['class'][$targetSettingsHandle] ?? null;
                    } else {
                        $class[] = $globalFieldRenderingOptions['class'] ?? null;
                    }

                    if (isset($fieldSpecificRenderingOptions['class'])
                        && is_array($fieldSpecificRenderingOptions['class'])) {
                        $class[] = $fieldSpecificRenderingOptions['class'][$targetSettingsHandle] ?? null;
                    } else {
                        $class[] = $fieldSpecificRenderingOptions['class'] ?? null;
                    }

                    // Append any global classes to field specific ones
                    $fieldRenderingOptions['class'] = trim(implode(' ', $class));
                    break;
                case 'errorClass':

                    if (isset($globalFieldRenderingOptions['errorClass'])
                        && is_array($globalFieldRenderingOptions['errorClass'])) {
                        $errorClass[] = $globalFieldRenderingOptions['errorClass'][$targetSettingsHandle] ?? null;
                    } else {
                        $errorClass[] = $globalFieldRenderingOptions['errorClass'] ?? null;
                    }

                    if (isset($fieldSpecificRenderingOptions['errorClass'])
                        && is_array($fieldSpecificRenderingOptions['class'])) {
                        $errorClass[] = $fieldSpecificRenderingOptions['errorClass'][$targetSettingsHandle] ?? null;
                    } else {
                        $errorClass[] = $fieldSpecificRenderingOptions['errorClass'] ?? null;
                    }

                    // Append any global classes to field specific ones
                    $fieldRenderingOptions['errorClass'] = trim(implode(' ', $errorClass));
                    break;
                case 'data':
                    // give priority to more specific data attributes
                    $globalData = $globalFieldRenderingOptions['data'] ?? [];
                    $fieldSpecificData = $fieldSpecificRenderingOptions['data'] ?? [];
                    $fieldRenderingOptions['data'] = array_filter(array_merge($globalData, $fieldSpecificData));
                    break;
            }
        }

        return $fieldRenderingOptions;
    }
}

