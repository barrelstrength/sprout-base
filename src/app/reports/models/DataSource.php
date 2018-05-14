<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\reports\models;

use craft\base\Model;

class DataSource extends Model
{
    public $id;

    public $pluginId;

    public $type;

    public $settings;

    public $allowNew;

    /**
     * @return array
     */
    public function safeAttributes()
    {
        return ['id', 'pluginId', 'type', 'settings', 'allowNew'];
    }
}