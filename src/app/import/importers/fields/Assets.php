<?php

namespace barrelstrength\sproutbase\app\import\importers\fields;

use barrelstrength\sproutbase\app\import\base\FieldImporter;
use barrelstrength\sproutbase\SproutBase;
use craft\elements\Asset;
use Craft;
use craft\fields\Assets as AssetsField;

class Assets extends FieldImporter
{
    /**
     * @return string
     */
    public function getModelName()
    {
        return AssetsField::class;
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getSeedSettingsHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-base-import/settings/seed-defaults/assets/settings', [
            'settings' => $this->seedSettings['fields']['assets'] ?? []
        ]);
    }

    /**
     * @return array|bool|mixed|null
     * @throws \Exception
     */
    public function getMockData()
    {
        $settings = $this->model->settings;

        $relatedMin = 1;
        $relatedMax = 3;

        $assetSettings = $this->seedSettings['fields']['assets'] ?? null;

        if ($assetSettings) {
            $relatedMin = $assetSettings['relatedMin'] ?: $relatedMin;
            $relatedMax = $assetSettings['relatedMax'] ?: $relatedMax;
        }

        $relatedMax = SproutBase::$app->fieldImporter->getLimit($settings['limit'], $relatedMax);

        $mockDataSettings = [
            'fieldName' => $this->model->name,
            'required' => $this->model->required,
            'relatedMin' => $relatedMin,
            'relatedMax' => $relatedMax
        ];

        if (empty($settings['sources'])) {
            SproutBase::info(Craft::t('sprout-base', 'Unable to generate Mock Data for relations field: {fieldName}. No Sources found.', [
                'fieldName' => $this->model->name
            ]));
            return null;
        }

        $sources = $settings['sources'];

        $sourceIds = SproutBase::$app->fieldImporter->getElementGroupIds($sources);

        $attributes = null;

        if ($sources != '*') {
            $attributes = [
                'volumeId' => $sourceIds
            ];
        }

        $assetElement = new Asset();

        $elementIds = SproutBase::$app->fieldImporter->getMockRelations($assetElement, $attributes, $mockDataSettings);

        return $elementIds;
    }
}
