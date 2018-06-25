<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\import\base;


use barrelstrength\sproutimport\models\jobs\SeedJob;
use Craft;
use barrelstrength\sproutimport\SproutImport;
use craft\base\Element;
use craft\elements\db\ElementQuery;
use craft\helpers\DateTimeHelper;

/**
 * Class ElementImporter
 *
 * @package Craft
 */
abstract class ElementImporter extends Importer
{
    /**
     * @inheritdoc Importer::getName()
     *
     * @return string
     */
    public function getName()
    {
        /**
         * @var $model Element
         */
        $model = $this->getModel();

        if (!is_object($model)) {
            return $model.Craft::t('sprout-import', ' Model definition not found.');
        }

        return $model::displayName();
    }

    /**
     * @return bool
     */
    public function isElement()
    {
        return true;
    }

    /**
     * @return mixed
     */
    public function getElement()
    {
        $elementName = get_class($this->getModel());

        return new $elementName;
    }

    /**
     * @param       $model
     * @param array $settings
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public function setModel($model, array $settings = [])
    {
        /**
         * @var $model Element
         */
        if ($existingElement = $this->getExistingElement($model, $settings)) {
            $model = $existingElement;
            $this->isUpdated = true;
        }

        if (isset($settings['attributes'])) {
            $attributes = $settings['attributes'];

            $relatedAttributes = [];
            if (isset($attributes['related']) && count($attributes['related'])) {
                $relatedAttributes = SproutImport::$app->elementImporter->resolveRelationships($attributes['related'], $relatedAttributes);
                unset($attributes['related']);
            }

            $attributes = array_merge($relatedAttributes, $attributes);

            foreach ($attributes as $handle => $attribute) {
                // Convert date time object to fix error when storing date attributes
                if ($this->isDateAttribute($handle)) {
                    $value = DateTimeHelper::toDateTime($attribute) ?: null;
                } else {
                    $value = $attribute;
                }

                $model->{$handle} = $value;
            }

            // Check if we have defaults for any unset attributes
            if (isset($settings['settings']['defaults'])) {
                $defaults = $settings['settings']['defaults'];
                /**
                 * @var $elementQuery ElementQuery
                 */
                $elementQuery = $model::find();

                $criteriaAttributes = $elementQuery->criteriaAttributes();

                foreach ($criteriaAttributes as $attribute) {
                    if (property_exists($model, $attribute) && !empty($model->{$attribute})) {
                        if (isset($defaults[$attribute])) {
                            $model->{$attribute} = $defaults[$attribute];
                        }
                    }
                }
            }
        }

        if (isset($settings['content'])) {
            if (!empty($settings['content']['title'])) {
                $model->title = $settings['content']['title'];
            }
            $relatedFields = [];
            if (isset($settings['content']['related']) && count($settings['content']['related'])) {
                $related = $settings['content']['related'];
                $relatedFields = SproutImport::$app->elementImporter->resolveRelationships($related, $relatedFields);

                $message = [];
                if (!$relatedFields) {
                    $message['error'] = Craft::t('sprout-import', 'Unable to resolve related relationships.');
                    $message['fields'] = $relatedFields;

                    SproutImport::error($message);
                }
            }

            $fields = [];

            if (!empty($settings['content']['fields'])) {
                $fields = $settings['content']['fields'];

                if (!empty($fields)) {
                    $fields = SproutImport::$app->elementImporter->resolveMatrixRelationships($fields);

                    $message = [];
                    if (!$fields) {
                        $message['error'] = Craft::t('sprout-import', 'Unable to resolve matrix relationships.');
                        $message['fields'] = $fields;

                        SproutImport::error($message);
                    }
                }
            }

            $fields = array_merge($relatedFields, $fields);

            // Required to associate fields on the element
            $model->fieldLayoutId = $this->getFieldLayoutId($model);

            $model->setFieldValues($fields);

            if (isset($settings['content']['title'])) {
                $model->title = $settings['content']['title'];
            }
        }

        $this->model = $model;

        return $this->model;
    }

    public abstract function getFieldLayoutId($model);

    /**
     * Delete an Element using the Element ID
     *
     * @param $id
     *
     * @return bool
     * @throws \Throwable
     */
    public function deleteById($id)
    {
        return Craft::$app->getElements()->deleteElementById($id);
    }

    /**
     * Determine if we have any elements we should handle before handling the current Element
     *
     * @param $element Element
     * @param $settings
     *
     * @return bool
     */
    protected function getExistingElement($element, $settings)
    {
        if (!isset($settings['settings']['updateElement'])) {
            return null;
        }

        $updateElementSettings = $settings['settings']['updateElement'];

        $utilities = SproutImport::$app->utilities;

        $matchBy = $utilities->getValueByKey('matchBy', $updateElementSettings);
        $matchValue = $utilities->getValueByKey('matchValue', $updateElementSettings);

        if ($matchBy && $matchValue) {
            if (is_array($matchValue)) {
                $matchValue = $matchValue[0];

                if (count($matchValue) > 0) {
                    $message = Craft::t('sprout-import', 'The updateElement key can only retrieve a single match. Array with multiple values was provided. Only the first value has been used to find a match: {matchValue}', [
                        'matchValue' => $matchValue
                    ]);

                    $utilities->addError('invalid-match', $message);
                }
            }

            $elementTypeName = get_class($element);

            return SproutImport::$app->elementImporter->getElementFromImportSettings($elementTypeName, $updateElementSettings);
        }

        return null;
    }

    /**
     * @return bool
     * @throws \Throwable
     */
    public function save()
    {
        $utilities = SproutImport::$app->utilities;

        try {
            $element = Craft::$app->getElements()->saveElement($this->model);

            $this->afterSaveElement();

            return $element;
        } catch (\Exception $e) {
            SproutImport::error($e->getMessage());

            $utilities->addError('invalid-entry-model', $e->getMessage());

            return false;
        }
    }

    public function beforeValidateElement()
    {

    }

    public function afterSaveElement()
    {

    }

    /**
     * @param $quantity
     * @param $settings
     *
     * @return array|null
     */
    public function getMockData($quantity, $settings)
    {
        return null;
    }

    private function isDateAttribute($handle)
    {
        $dates = ['postDate', 'dateCreated', 'dateUpdated'];

        return in_array($handle, $dates, false);
    }

    /**
     * Validate any settings required by this Element's seed importer
     *
     * string = error message
     * null = no errors
     *
     * @param $settings
     *
     * @return string|null
     */
    public function getSeedSettingsErrors($settings)
    {
        return null;
    }

    /**
     * @param SeedJob $seedJob
     *
     * @return string
     */
    public function getSeedSettingsHtml(SeedJob $seedJob)
    {
        return '';
    }
}