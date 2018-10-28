<?php

namespace barrelstrength\sproutbase\app\import\importers\fields;

use barrelstrength\sproutbase\app\import\base\FieldImporter;
use craft\redactor\Field;

class Redactor extends FieldImporter
{
    /**
     * @return string
     */
    public function getModelName()
    {
        return Field::class;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getMockData()
    {
        $lines = random_int(3, 5);
        $paragraphs = $this->fakerService->paragraphs($lines);

        return implode("\n\n", $paragraphs);
    }
}