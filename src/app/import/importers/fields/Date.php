<?php

namespace barrelstrength\sproutbase\app\import\importers\fields;

use barrelstrength\sproutbase\app\import\base\FieldImporter;
use barrelstrength\sproutbase\SproutBase;
use craft\fields\Date as DateField;

class Date extends FieldImporter
{
    /**
     * @return string
     */
    public function getModelName()
    {
        return DateField::class;
    }

    /**
     * @return mixed
     */
    public function getMockData()
    {
        $settings = $this->model->settings;

        $minuteIncrement = $settings['minuteIncrement'];
        $showDate = $settings['showDate'];
        $showTime = $settings['showTime'];

        $values = [];

        $values['time'] = '';

        if ($showDate == true) {
            $values['date'] = $this->fakerService->date('d/m/Y');
        }

        if ($showTime == true) {
            $randomTimestamp = strtotime($this->fakerService->time('g:i:s A'));

            $values['time'] = SproutBase::$app->fieldImporter->getMinutesByIncrement($randomTimestamp, $minuteIncrement);
        }

        return $values;
    }
}
