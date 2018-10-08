<?php

namespace barrelstrength\sproutbase\app\import\importers\settings;

use barrelstrength\sproutbase\app\import\base\SettingsImporter;
use barrelstrength\sproutbase\app\import\models\importers\Field as FieldModel;
use barrelstrength\sproutbase\SproutBase;
use craft\records\Field as FieldRecord;
use Craft;

class Field extends SettingsImporter
{
    /**
     * @return string
     */
    public function getName()
    {
        return Craft::t('sprout-base', 'Field');
    }

    /**
     * @return string
     */
    public function getModelName()
    {
        return FieldModel::class;
    }

    /**
     * @param $settings
     *
     * @return \craft\base\FieldInterface
     */
    private function getFieldType($settings)
    {
        $fieldsService = Craft::$app->getFields();

        unset($settings['@model']);

        return $fieldsService->createField($settings);
    }


    /**
     * @param null $handle
     *
     * @return \craft\base\FieldInterface|null
     */
    public function getModelByHandle($handle = null)
    {
        return Craft::$app->getFields()->getFieldByHandle($handle);
    }

    /**
     * @inheritdoc
     */
    public function getRecord()
    {
        return FieldRecord::class;
    }

    /**
     * @return bool|\craft\base\FieldInterface|mixed
     * @throws \Throwable
     */
    public function save()
    {
        $fieldsService = Craft::$app->getFields();

        if (!isset($this->model->id)) {
            $fieldType = $this->getFieldType($this->rows);

            if (!$fieldsService->saveField($fieldType)) {

                SproutBase::error(Craft::t('sprout-base', 'Cannot save Field: '.$fieldType::displayName()));
                SproutBase::info($fieldType);

                return false;
            }

            $this->model = $fieldType;
        } else {
            $fieldType = $this->model;
        }


        return $fieldType;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function deleteById($id)
    {
        return Craft::$app->getFields()->deleteFieldById($id);
    }
}
