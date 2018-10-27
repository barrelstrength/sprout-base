<?php

namespace barrelstrength\sproutbase\app\import\importers\fields;

use barrelstrength\sproutbase\app\import\base\FieldImporter;
use craft\fields\Checkboxes as CheckboxesField;
use barrelstrength\sproutbase\SproutBase;

class Checkboxes extends FieldImporter
{
    /**
     * @return string
     */
    public function getModelName()
    {
        return CheckboxesField::class;
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
