<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\services;

use barrelstrength\sproutbase\app\forms\base\Integration;
use barrelstrength\sproutbase\app\forms\elements\Form;
use barrelstrength\sproutbase\app\forms\elements\Form as FormElement;
use barrelstrength\sproutbase\app\forms\records\Form as FormRecord;
use barrelstrength\sproutbase\app\forms\records\Integration as IntegrationRecord;
use barrelstrength\sproutbase\app\forms\rules\FieldRule;
use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\migrations\forms\CreateFormContentTable;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\db\Query;
use craft\errors\MissingComponentException;
use craft\helpers\MigrationHelper;
use craft\helpers\StringHelper;
use Throwable;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\db\Transaction;
use yii\web\BadRequestHttpException;

/**
 *
 * @property array $allEnabledCaptchas
 * @property array $allCaptchas
 * @property array $allFormTemplateTypes
 * @property array[] $allFormTemplates
 * @property array[] $allCaptchaTypes
 */
class Forms extends Component
{


    /**
     * @var array
     */
    protected static $fieldVariables = [];

    /**
     * @var
     */
    public $activeEntries;

    /**
     * @var
     */
    public $activeCpEntry;

    /**
     * @var FormRecord
     */
    protected $formRecord;

    /**
     * Constructor
     *
     * @param object $formRecord
     */
    public function __construct($formRecord = null)
    {
        $this->formRecord = $formRecord;

        if ($this->formRecord === null) {
            $this->formRecord = new FormRecord();
        }

        parent::__construct($formRecord);
    }

    /**
     *
     * Allows a user to add variables to an object that can be parsed by fields
     *
     * @param array $variables
     *
     * @example
     * {% do sprout.forms.addFieldVariables({ entryTitle: entry.title }) %}
     * {{ sprout.forms.displayForm('contact') }}
     *
     */
    public static function addFieldVariables(array $variables)
    {
        static::$fieldVariables = array_merge(static::$fieldVariables, $variables);
    }

    /**
     * @return mixed
     */
    public static function getFieldVariables()
    {
        return static::$fieldVariables;
    }

    /**
     * @param FormElement $form
     * @param bool $duplicate
     *
     * @return bool
     * @throws Throwable
     * @throws InvalidConfigException
     */
    public function saveForm(FormElement $form, bool $duplicate = false): bool
    {
        $isNew = !$form->id;
        $hasLayout = count($form->getFieldLayout()->getFields()) > 0;
        $oldForm = null;

        if (!$isNew) {
            // Add the oldHandle to our model so we can determine if we
            // need to rename the content table
            /** @var FormRecord $formRecord */
            $formRecord = FormRecord::findOne($form->id);
            $form->oldHandle = $formRecord->getOldHandle();
            $oldForm = $formRecord;

            if ($duplicate) {
                $form->name = $oldForm->name;
                $form->handle = $oldForm->handle;
                $form->oldHandle = null;
            }
        }

        $form->validate();

        if ($form->hasErrors()) {
            Craft::error($form->getErrors(), __METHOD__);

            return false;
        }

        /** @var Transaction $transaction */
        $transaction = Craft::$app->db->beginTransaction();

        try {
            if ($isNew) {
                $fieldLayout = $form->getFieldLayout();

                // Save the field layout
                Craft::$app->getFields()->saveLayout($fieldLayout);

                // Assign our new layout id info to our form model and record
                $form->fieldLayoutId = $fieldLayout->id;
                $form->setFieldLayout($fieldLayout);
            } else if ($oldForm !== null && $hasLayout) {
                // Delete our previous record
                Craft::$app->getFields()->deleteLayoutById($oldForm->fieldLayoutId);

                $fieldLayout = $form->getFieldLayout();

                // Save the field layout
                Craft::$app->getFields()->saveLayout($fieldLayout);

                // Assign our new layout id info to our form model
                $form->fieldLayoutId = $fieldLayout->id;
                $form->setFieldLayout($fieldLayout);
            } else {
                // We don't have a field layout right now
                $form->fieldLayoutId = null;
            }

            // Set the field context
            Craft::$app->content->fieldContext = $form->getFieldContext();
            Craft::$app->content->contentTable = $form->getContentTable();

            // Create the content table first since the form will need it
            $oldContentTable = $this->getContentTableName($form, true);
            $newContentTable = $this->getContentTableName($form);

            // Do we need to create/rename the content table?
            if (!Craft::$app->db->tableExists($newContentTable) && !$duplicate) {
                if ($oldContentTable && Craft::$app->db->tableExists($oldContentTable)) {
                    MigrationHelper::renameTable($oldContentTable, $newContentTable);
                } else {
                    $this->_createContentTable($newContentTable);
                }
            }

            // Save the Form
            if (!Craft::$app->elements->saveElement($form)) {
                Craft::error('Couldn’t save Element.', __METHOD__);

                return false;
            }

            // FormRecord saved on afterSave form element
            $transaction->commit();

            Craft::info('Form Saved.', __METHOD__);
        } catch (\Exception $e) {
            Craft::error('Unable to save form: '.$e->getMessage(), __METHOD__);
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    /**
     * Removes a form and related records from the database
     *
     * @param FormElement $form
     *
     * @return bool
     * @throws \Exception
     * @throws Throwable
     */
    public function deleteForm(FormElement $form): bool
    {
        /** @var Transaction $transaction */
        $transaction = Craft::$app->db->beginTransaction();

        try {
            $originalContentTable = Craft::$app->content->contentTable;
            $contentTable = $this->getContentTableName($form);
            Craft::$app->content->contentTable = $contentTable;

            //Delete all entries
            $entries = (new Query())
                ->select(['elementId'])
                ->from([$contentTable])
                ->all();

            foreach ($entries as $entry) {
                Craft::$app->elements->deleteElementById($entry['elementId']);
            }

            // Delete form fields
            foreach ($form->getFields() as $field) {
                Craft::$app->getFields()->deleteField($field);
            }

            // Delete the Field Layout
            Craft::$app->getFields()->deleteLayoutById($form->fieldLayoutId);

            // Drop the content table
            Craft::$app->db->createCommand()
                ->dropTable($contentTable)
                ->execute();

            Craft::$app->content->contentTable = $originalContentTable;

            // Delete the Element and Form
            $success = Craft::$app->elements->deleteElementById($form->id);

            if (!$success) {
                $transaction->rollBack();
                Craft::error('Couldn’t delete Form', __METHOD__);

                return false;
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    /**
     * Returns an array of models for forms found in the database
     *
     * @param int|null $siteId
     *
     * @return FormElement[]
     */
    public function getAllForms(int $siteId = null): array
    {
        $query = FormElement::find();
        $query->siteId($siteId);
        $query->orderBy(['name' => SORT_ASC]);

        return $query->all();
    }

    /**
     * Returns a form model if one is found in the database by id
     *
     * @param int $formId
     * @param int|null $siteId
     *
     * @return FormElement|ElementInterface|null
     */
    public function getFormById(int $formId, int $siteId = null)
    {
        $query = FormElement::find();
        $query->id($formId);
        $query->siteId($siteId);

        return $query->one();
    }

    /**
     * Returns a form model if one is found in the database by handle
     *
     * @param string $handle
     * @param int|null $siteId
     *
     * @return Form|ElementInterface|null
     */
    public function getFormByHandle(string $handle, int $siteId = null)
    {
        $query = FormElement::find();
        $query->handle($handle);
        $query->siteId($siteId);

        return $query->one();
    }

    /**
     * Returns the content table name for a given form field
     *
     * @param FormElement $form
     * @param bool $useOldHandle
     *
     * @return string|false
     */
    public function getContentTableName(FormElement $form, $useOldHandle = false)
    {
        if ($useOldHandle) {
            if (!$form->oldHandle) {
                return false;
            }

            $handle = $form->oldHandle;
        } else {
            $handle = $form->handle;
        }

        $name = '_'.StringHelper::toLowerCase($handle);

        return '{{%sproutformscontent'.$name.'}}';
    }

    /**
     * @param $formId
     *
     * @return string
     */
    public function getContentTable($formId): string
    {
        $form = $this->getFormById($formId);

        if ($form) {
            return sprintf('sproutformscontent_%s', strtolower(trim($form->handle)));
        }

        return 'content';
    }

    /**
     * Returns the value of a given field
     *
     * @param $field
     * @param $value
     *
     * @return null|FormRecord
     */
    public function getFieldValue($field, $value)
    {
        return FormRecord::findOne([
            $field => $value
        ]);
    }

    /**
     * Remove a field handle from title format
     *
     * @param int $fieldId
     *
     * @return string newTitleFormat
     */
    public function cleanTitleFormat($fieldId)
    {
        /** @var Field $field */
        $field = Craft::$app->getFields()->getFieldById($fieldId);

        if ($field) {
            $context = explode(':', $field->context);
            $formId = $context[1];

            /** @var FormRecord $formRecord */
            $formRecord = FormRecord::findOne($formId);

            // Check if the field is in the titleformat
            if (strpos($formRecord->titleFormat, $field->handle) !== false) {
                // Let's remove the field from the titleFormat
                $newTitleFormat = preg_replace('/{'.$field->handle.'.*}/', '', $formRecord->titleFormat);
                $formRecord->titleFormat = $newTitleFormat;
                $formRecord->save(false);

                return $formRecord->titleFormat;
            }
        }

        return null;
    }

    /**
     * If a field is deleted remove it from the rules
     *
     * @param $oldHandle
     * @param $form
     *
     * @throws Throwable
     */
    public function removeFieldRulesUsingField($oldHandle, $form)
    {
        $rules = SproutBase::$app->formRules->getRulesByFormId($form->id, FieldRule::class);

        /** @var Field[] $fields */
        $fields = $form->getFieldLayout()->getFields();

        $fieldHandles = [];
        foreach ($fields as $field) {
            $fieldHandles[] = $field->handle;
        }

        // Clean up rules if any SOURCE or TARGET fields were deleted
        foreach ($rules as $rule) {

            if (!in_array($rule->behaviorTarget, $fieldHandles, true)) {
                SproutBase::$app->formRules->deleteRule($rule);
                continue;
            }

            foreach ($rule->conditions as $conditionSetKey => $conditionSet) {
                foreach ($conditionSet as $conditionKey => $condition) {
                    // $condition[0] is the fieldHandle of the Source Field for the rule
                    $ruleSourceFieldHandle = $condition[0] ?? null;
                    if ($ruleSourceFieldHandle
                        && !in_array($ruleSourceFieldHandle, $fieldHandles, true)) {
                        unset($rule->conditions[$conditionSetKey][$conditionKey]);
                    }
                }

                if (empty($rule->conditions[$conditionSetKey])) {
                    // If we removed all conditions from a rule, delete the entire rule
                    SproutBase::$app->formRules->deleteRule($rule);
                    continue 2;
                }
            }

            SproutBase::$app->formRules->saveRule($rule);
        }
    }

    /**
     * IF a field is deleted remove it from the rules
     *
     * @param $oldHandle
     * @param $newHandle
     * @param $form
     *
     * @throws InvalidConfigException
     * @throws MissingComponentException
     */
    public function updateFieldOnFieldRules($oldHandle, $newHandle, $form)
    {
        $rules = SproutBase::$app->formRules->getRulesByFormId($form->id);

        /** @var FieldRule $rule */
        foreach ($rules as $rule) {
            $conditions = $rule->conditions;
            if ($conditions) {
                foreach ($conditions as $key => $orConditions) {
                    foreach ($orConditions as $key2 => $condition) {
                        if (isset($condition[0]) && $condition[0] === $oldHandle) {
                            $conditions[$key][$key2][0] = $newHandle;
                        }
                    }
                }
            }

            if ($rule->behaviorTarget === $oldHandle) {
                $rule->behaviorTarget = $newHandle;
            }

            $rule->conditions = $conditions;
            SproutBase::$app->formRules->saveRule($rule);
        }
    }

    /**
     * IF a field is updated, update the integrations
     *
     * @param $oldHandle
     * @param $newHandle
     * @param $form
     *
     * @throws InvalidConfigException
     * @throws MissingComponentException
     */
    public function updateFieldOnIntegrations($oldHandle, $newHandle, $form)
    {
        $integrations = SproutBase::$app->formIntegrations->getIntegrationsByFormId($form->id);

        /** @var Integration $integration */
        foreach ($integrations as $integration) {
            $integrationResult = (new Query())
                ->select(['id', 'settings'])
                ->from([IntegrationRecord::tableName()])
                ->where(['id' => $integration->id])
                ->one();

            if ($integrationResult === null) {
                continue;
            }

            $settings = json_decode($integrationResult['settings'], true);

            $fieldMapping = $settings['fieldMapping'];
            foreach ($fieldMapping as $pos => $map) {
                if (isset($map['sourceFormField']) && $map['sourceFormField'] === $oldHandle) {
                    $fieldMapping[$pos]['sourceFormField'] = $newHandle;
                }
            }

            $integration->fieldMapping = $fieldMapping;
            SproutBase::$app->formIntegrations->saveIntegration($integration);
        }
    }

    /**
     * Update a field handle with an new title format
     *
     * @param string $oldHandle
     * @param string $newHandle
     * @param string $titleFormat
     *
     * @return string newTitleFormat
     */
    public function updateTitleFormat($oldHandle, $newHandle, $titleFormat): string
    {
        return str_replace($oldHandle, $newHandle, $titleFormat);
    }

    /**
     * Create a secuencial string for the "name" and "handle" fields if they are already taken
     *
     * @param $field
     * @param $value
     *
     * @return null|string
     */
    public function getFieldAsNew($field, $value)
    {
        $newField = null;
        $i = 1;
        $band = true;
        do {
            $newField = $field == 'handle' ? $value.$i : $value.' '.$i;
            $form = $this->getFieldValue($field, $newField);
            if ($form === null) {
                $band = false;
            }

            $i++;
        } while ($band);

        return $newField;
    }

    /**
     * Removes forms and related records from the database given the ids
     *
     * @param $formElements
     *
     * @return bool
     * @throws \Exception
     * @throws Throwable
     */
    public function deleteForms($formElements): bool
    {
        foreach ($formElements as $key => $formElement) {
            $form = SproutBase::$app->forms->getFormById($formElement->id);

            if ($form) {
                SproutBase::$app->forms->deleteForm($form);
            } else {
                Craft::error("Can't delete the form with id: {$formElement->id}", __METHOD__);
            }
        }

        return true;
    }

    /**
     * Creates a form with a empty default tab
     *
     * @param string|null $name
     * @param string|null $handle
     *
     * @return FormElement|null
     * @throws \Exception
     * @throws Throwable
     */
    public function createNewForm($name = null, $handle = null)
    {
        $form = new FormElement();
        $name = $name ?? 'Form';
        $handle = $handle ?? 'form';

        $settings = SproutBase::$app->settings->getSettingsByKey('forms');

        $form->name = $this->getFieldAsNew('name', $name);
        $form->handle = $this->getFieldAsNew('handle', $handle);
        $form->titleFormat = "{dateCreated|date('D, d M Y H:i:s')}";
        $form->formTemplateId = '';
        $form->saveData = $settings->enableSaveData ? $settings->enableSaveDataDefaultValue : false;

        // Set default tab

        /** @var Field $field */
        $field = null;
        $form = SproutBase::$app->formFields->addDefaultTab($form, $field);

        if ($this->saveForm($form)) {
            // Let's delete the default field
            if ($field !== null && $field->id) {
                Craft::$app->getFields()->deleteFieldById($field->id);
            }

            return $form;
        }

        return null;
    }

    /**
     * Checks if the current plugin edition allows a user to create a Form
     *
     * @return bool
     */
    public function canCreateForm(): bool
    {
        $isPro = SproutBase::$app->config->isEdition('forms', Config::EDITION_PRO);

        if (!$isPro) {
            $forms = $this->getAllForms();

            if (count($forms) >= 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param FormElement $form
     *
     * @return array
     * @throws InvalidConfigException
     */
    public function getTabsForFieldLayout(Form $form): array
    {
        $tabs = [];

        foreach ($form->getFieldLayout()->getTabs() as $index => $tab) {
            // Do any of the fields on this tab have errors?
            $hasErrors = false;

            if ($form->hasErrors()) {
                foreach ($tab->getFields() as $field) {
                    /** @var Field $field */
                    if ($hasErrors = $form->hasErrors($field->handle.'.*')) {
                        break;
                    }
                }
            }

            $tabs[$tab->id] = [
                'label' => Craft::t('sprout', $tab->name),
                'url' => '#sproutforms-tab-'.$tab->id,
                'class' => $hasErrors ? 'error' : null
            ];
        }

        return $tabs;
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
     * Creates the content table for a Form.
     *
     * @param $tableName
     *
     * @throws Throwable
     */
    private function _createContentTable($tableName)
    {
        $migration = new CreateFormContentTable([
            'tableName' => $tableName
        ]);

        ob_start();
        $migration->up();
        ob_end_clean();
    }

    /**
     * @param $context
     *
     * @return string|null
     */
    public function handleModifyFormHook($context)
    {
        /** @var Form $form */
        $form = $context['form'] ?? null;
        if ($form !== null && $form->enableCaptchas) {
            return SproutBase::$app->formCaptchas->getCaptchasHtml($form);
        }

        return null;
    }
}
