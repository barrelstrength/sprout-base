<?php

namespace barrelstrength\sproutbase\app\import\importers\fields;

use barrelstrength\sproutbase\app\import\base\FieldImporter;
use barrelstrength\sproutbase\SproutBase;
use craft\elements\Category;
use craft\fields\Categories as CategoriesField;
use Craft;

class Categories extends FieldImporter
{
    /**
     * @return string
     */
    public function getModelName(): string
    {
        return CategoriesField::class;
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getSeedSettingsHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-base-import/settings/seed-defaults/categories/settings', [
            'settings' => $this->seedSettings['fields']['categories'] ?? []
        ]);
    }

    /**
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function getMockData()
    {
        $settings = $this->model->settings;

        $relatedMin = 1;
        $relatedMax = 3;

        $categorySettings = $this->seedSettings['fields']['categories'] ?? null;

        if ($categorySettings) {
            $relatedMin = $categorySettings['branchLimitMin'] ?: $relatedMin;
            $relatedMax = $categorySettings['branchLimitMax'] ?: $relatedMax;
        }

        $relatedMax = SproutBase::$app->fieldImporter->getLimit($settings['branchLimit'], $relatedMax);

        $mockDataSettings = [
            'fieldName' => $this->model->name,
            'required' => $this->model->required,
            'relatedMin' => $relatedMin,
            'relatedMax' => $relatedMax
        ];

        if (empty($settings['source'])) {
            SproutBase::info(Craft::t('sprout-base', 'Unable to generate Mock Data for relations field: {fieldName}. No Source found.', [
                'fieldName' => $this->model->name
            ]));
            return null;
        }

        $source = $settings['source'];

        $groupId = SproutBase::$app->fieldImporter->getElementGroupId($source);

        $attributes = null;

        if ($source != '*') {
            $attributes = [
                'groupId' => $groupId
            ];
        }

        $categoryElement = new Category();

        $elementIds = SproutBase::$app->fieldImporter->getMockRelations($categoryElement, $attributes, $mockDataSettings);

        return $elementIds;
    }
}
