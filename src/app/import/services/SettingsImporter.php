<?php

namespace barrelstrength\sproutbase\app\import\services;

use barrelstrength\sproutbase\app\import\base\SettingsImporter as BaseSettingsImporter;
use barrelstrength\sproutbase\SproutBase;
use craft\base\Component;
use Craft;

class SettingsImporter extends Component
{
    /**
     * @param                           $rows
     * @param BaseSettingsImporter|null $importerClass
     *
     * @return bool|\craft\base\Model|mixed|null
     * @throws \Exception
     */
    public function saveSetting($rows, BaseSettingsImporter $importerClass = null)
    {
        $model = $importerClass->getModel();

        if (!$model->validate(null, false)) {

            SproutBase::error(Craft::t('sprout-base', 'Errors found on model while saving Settings'));

            SproutBase::$app->importUtilities->addError('invalid-model', $model->getErrors());

            return false;
        }

        try {

            if ($importerClass->save()) {
                // Get updated model after save
                $model = $importerClass->getModel();

                $importerClass->resolveNestedSettings($model, $rows);

                return $model;
            }

            return false;
        } catch (\Exception $e) {

            $message = Craft::t('sprout-base', 'Unable to import Settings.');

            SproutBase::error($message);
            SproutBase::error($e->getMessage());

            SproutBase::$app->importUtilities->addError('save-setting-importer', $message);

            return false;
        }
    }
}