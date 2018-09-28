<?php

namespace barrelstrength\sproutbase\app\import\importers\fields;

use barrelstrength\sproutbase\app\import\base\FieldImporter;
use barrelstrength\sproutbase\app\import\SproutImport;
use craft\elements\Entry;
use craft\fields\Entries as EntriesField;
use Craft;

class Entries extends FieldImporter
{
    /**
     * @return string
     */
    public function getModelName()
    {
        return EntriesField::class;
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getSeedSettingsHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-base-import/settings/seed-defaults/entries/settings', [
            'settings' => $this->seedSettings['fields']['entries'] ?? []
        ]);
    }

    /**
     * @return array|bool|mixed
     */
    public function getMockData()
    {
        $settings = $this->model->settings;

        $relatedMin = 1;
        $relatedMax = 3;

        $entrySettings = $this->seedSettings['fields']['entries'] ?? null;

        if ($entrySettings)
        {
            $relatedMin = $entrySettings['relatedMin'] ?: $relatedMin;
            $relatedMax = $entrySettings['relatedMax'] ?: $relatedMax;
        }

        $relatedMax = SproutBase::$app->fieldImporter->getLimit($settings['limit'], $relatedMax);

        $mockDataSettings = [
            'fieldName' => $this->model->name,
            'required' => $this->model->required,
            'relatedMin' => $relatedMin,
            'relatedMax' => $relatedMax
        ];

        if (!isset($settings['sources'])) {
            SproutBase::info(Craft::t('sprout-base', 'Unable to generate Mock Data for relations field: {fieldName}. No Sources found.', [
                'fieldName' => $this->model->name
            ]));
            return null;
        }

        $sources = $settings['sources'];

        $sectionIds = SproutBase::$app->fieldImporter->getElementGroupIds($sources);

        $attributes = null;

        if ($sources != '*') {
            $attributes = [
                'sectionId' => $sectionIds
            ];
        }

        $entryElement = new Entry();

        $elementIds = SproutBase::$app->fieldImporter->getMockRelations($entryElement, $attributes, $mockDataSettings);

        return $elementIds;
    }
}
