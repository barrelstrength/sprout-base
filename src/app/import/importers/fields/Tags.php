<?php

namespace barrelstrength\sproutbase\app\import\importers\fields;

use barrelstrength\sproutbase\app\import\base\FieldImporter;
use barrelstrength\sproutbase\SproutBase;
use craft\elements\Tag;
use craft\fields\Tags as TagsField;
use Craft;

class Tags extends FieldImporter
{
    /**
     * @return string
     */
    public function getModelName()
    {
        return TagsField::class;
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getSeedSettingsHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-base-import/settings/seed-defaults/tags/settings', [
            'settings' => $this->seedSettings['fields']['tags'] ?? []
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

        $tagSettings = $this->seedSettings['fields']['tags'] ?? null;

        if ($tagSettings) {
            $relatedMin = $tagSettings['relatedMin'] ?: $relatedMin;
            $relatedMax = $tagSettings['relatedMax'] ?: $relatedMax;
        }

        $relatedMax = SproutBase::$app->fieldImporter->getLimit($settings['limit'], $relatedMax);

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

        $tagElement = new Tag();

        $elementIds = SproutBase::$app->fieldImporter->getMockRelations($tagElement, $attributes, $mockDataSettings);

        return $elementIds;
    }
}
