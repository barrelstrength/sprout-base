<?php

namespace barrelstrength\sproutbase\app\import\importers\fields;

use barrelstrength\sproutbase\app\import\base\FieldImporter;
use barrelstrength\sproutbase\SproutBase;
use craft\fields\MultiSelect as MultiSelectField;

class MultiSelect extends FieldImporter
{
    /**
     * @return string
     */
    public function getModelName()
    {
        return MultiSelectField::class;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getMockData()
    {
        $settings = $this->model->settings;

        $values = [];

        if (!empty($settings['options'])) {
            $options = $settings['options'];

            $length = count($options);
            $number = random_int(1, $length);

            $randomArrayItems = SproutBase::$app->fieldImporter->getRandomArrayItems($options, $number);

            $values = SproutBase::$app->fieldImporter->getOptionValuesByKeys($randomArrayItems, $options);
        }

        return $values;
    }
}
