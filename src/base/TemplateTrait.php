<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\base;

use Craft;
use craft\base\Model;

trait TemplateTrait
{
    /**
     * Returns whether or not a site template exists
     *
     * @param $template
     *
     * @return bool
     * @throws \yii\base\Exception
     */
    public function doesSiteTemplateExist($template)
    {
        $path = Craft::$app->getView()->getTemplatesPath();

        Craft::$app->getView()->setTemplatesPath(Craft::$app->getPath()->getSiteTemplatesPath());

        $exists = Craft::$app->getView()->doesTemplateExist($template);

        Craft::$app->getView()->setTemplatesPath($path);

        return $exists;
    }

    /**
     * @param Model $model
     *
     * @return array
     */
    public function getModelTabs(Model $model)
    {
        $tabs = [];
        /**
         * @var $model Model
         */
        if (!empty($model->getFieldLayout())) {
            $modelTabs = $model->getFieldLayout()->getTabs();

            if (!empty($modelTabs)) {
                foreach ($modelTabs as $index => $tab) {
                    // Do any of the fields on this tab have errors?
                    $hasErrors = false;

                    if ($model->hasErrors()) {
                        foreach ($tab->getFields() as $field) {
                            if ($model->getErrors($field->handle)) {
                                $hasErrors = true;
                                break;
                            }
                        }
                    }

                    $tabs[] = [
                        'label' => Craft::t('sprout-base', $tab->name),
                        'url' => '#tab'.($index + 1),
                        'class' => ($hasErrors ? 'error' : null)
                    ];
                }
            }
        }

        return $tabs;
    }
}