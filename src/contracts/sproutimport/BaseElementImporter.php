<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\contracts\sproutimport;

use barrelstrength\sproutimport\models\jobs\SeedJob;
use Craft;
use barrelstrength\sproutimport\SproutImport;
use craft\base\Element;
use craft\elements\db\ElementQuery;
use craft\helpers\DateTimeHelper;

/**
 * Class BaseSproutImportElementImporter
 *
 * @package Craft
 */
abstract class BaseElementImporter extends BaseImporter
{
    /**
     * @inheritdoc BaseImporter::getName()
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
        $name = $this->getModelName();

        $elementName = Craft::$app->getElements()->getElementTypeByRefHandle($name);

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
        $model = $this->processUpdateElement($model, $settings);

        $authorId = null;

        if (isset($settings['attributes'])) {
            $attributes = $settings['attributes'];

            foreach ($attributes as $handle => $attribute) {
                // Convert date time object to fix error when storing date attributes
                if ($this->isDateAttribute($handle)) {
                    $value = DateTimeHelper::toDateTime($attribute) ?: null;
                } else {
                    $value = $attribute;
                }

                $model->{$handle} = $value;
            }

            // Check for email and username values if authorId attribute
            if (isset($attributes['authorId']) && $authorId = $this->getAuthorId($attributes['authorId'])) {
                $model->authorId = $authorId;
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
                        // Check for email and username values if authorId attribute
                        if ($attribute == 'authorId' && isset($defaults['authorId'])) {
                            if ($authorId = $this->getAuthorId($defaults['authorId'])) {
                                $model->authorId = $authorId;
                            }

                            continue;
                        }

                        if (isset($defaults[$attribute])) {
                            $model->{$attribute} = $defaults[$attribute];
                        }
                    }
                }
            }

            // Check only for models that has authorId attribute.
            if ($authorId == null && in_array('authorId', $model->attributes(), false)) {
                $message = Craft::t('sprout-import', 'Could not find Author by ID, Email, or Username.');

                Craft::error($message);

                SproutImport::$app->utilities->addError('invalid-author', $message);
                SproutImport::$app->utilities->addError('invalid-author', $settings);
            }
        }

        if (isset($settings['content'])) {
            if (!empty($settings['content']['title'])) {
                $model->title = $settings['content']['title'];
            }

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

                if (isset($settings['content']['related']) && count($settings['content']['related'])) {
                    $related = $settings['content']['related'];
                    $fields = SproutImport::$app->elementImporter->resolveRelationships($related, $fields);

                    $message = [];
                    if (!$fields) {
                        $message['error'] = Craft::t('sprout-import', 'Unable to resolve related relationships.');
                        $message['fields'] = $fields;

                        SproutImport::error($message);
                    }
                }

                $fields = ['fields' => $fields];

                // Required to associate fields on the element
                $model->fieldLayoutId = $this->getFieldLayoutId($model);

                Craft::$app->getRequest()->setBodyParams($fields);

                $model->setFieldValuesFromRequest('fields');

                if (isset($settings['content']['title'])) {
                    $model->title = $settings['content']['title'];
                }
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
     * @param $model
     * @param $settings
     *
     * @return bool
     */
    protected function processUpdateElement($model, $settings)
    {
        if (!isset($settings['settings']['updateElement'])) {
            return $model;
        }

        $updateElement = $settings['settings']['updateElement'];

        $element = SproutImport::$app->elementImporter->getModelByMatches($model, $updateElement);

        if ($element) {
            return $element;
        }

        return $model;
    }

    /**
     * @param $authorId
     *
     * @return mixed|null
     */
    protected function getAuthorId($authorId)
    {
        if (is_int($authorId)) {
            $userModel = Craft::$app->getUsers()->getUserById($authorId);
        } else {
            $userModel = Craft::$app->getUsers()->getUserByUsernameOrEmail($authorId);
        }

        return isset($userModel) ? $userModel->id : null;
    }

    /**
     * @return bool
     * @throws \Throwable
     */
    public function save()
    {
        $utilities = SproutImport::$app->utilities;

        try {
            return Craft::$app->getElements()->saveElement($this->model);
        } catch (\Exception $e) {
            SproutImport::error($e->getMessage());

            $utilities->addError('invalid-entry-model', $e->getMessage());

            return false;
        }
    }

    private function isDateAttribute($handle)
    {
        $dates = ['postDate', 'dateCreated', 'dateUpdated'];

        return in_array($handle, $dates);
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