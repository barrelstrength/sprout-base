<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\metadata\schema;

use barrelstrength\sproutbase\app\metadata\base\Schema;

class GeoSchema extends Schema
{
    public $latitude;

    public $longitude;

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Geo';
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return 'GeoCoordinates';
    }

    /**
     * @return bool
     */
    public function isUnlistedSchemaType(): bool
    {
        return true;
    }

    /**
     * @return null|void
     */
    public function addProperties()
    {
        $this->addText('latitude', $this->latitude);
        $this->addText('longitude', $this->longitude);
    }
}