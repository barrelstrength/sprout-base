<?php

namespace barrelstrength\sproutbase\app\import\importers\fields;

use barrelstrength\sproutbase\app\import\base\FieldImporter;
use craft\fields\Color as ColorField;

class Color extends FieldImporter
{
    /**
     * @return string
     */
    public function getModelName(): string
    {
        return ColorField::class;
    }

    /**
     * @return mixed
     */
    public function getMockData()
    {
        return $this->fakerService->hexColor;
    }
}
