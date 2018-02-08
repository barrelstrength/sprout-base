<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\records\sproutreports;

use craft\db\ActiveRecord;

/**
 * Class Report
 *
 * @property int $id
 * @property string $name
 * @property string $nameFormat
 * @property string $handle
 * @property string $description
 * @property bool $allowHtml
 * @property mixed $options
 * @property int $dataSourceId
 * @property bool $enabled
 * @property int $groupId
 */
class Report extends ActiveRecord
{
    public $id;

    public $name;

    public $nameFormat;

    public $handle;

    public $description;

    public $allowHtml;

    public $options;

    public $dataSourceId;

    public $enabled;

    public $groupId;

    public const SCENARIO_ALL = 'all';

    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%sproutreports_report}}';
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_ALL => [
                'id', 'name', 'nameFormat', 'handle',
                'description', 'options', 'dataSourceId',
                'groupId', 'enabled', 'allowHtml'
            ]
        ];
    }
}