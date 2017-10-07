<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutcore\records\sproutreports;

use craft\db\ActiveRecord;

/**
 * Class Report
 *
 * @package barrelstrength\sproutcore\records\sproutreports
 */
class Report extends ActiveRecord
{
	const SCENARIO_ALL = 'all';
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
			self::SCENARIO_ALL => ['id', 'name', 'handle',
			                       'description', 'options', 'dataSourceId',
			                       'groupId', 'enabled', 'allowHtml']
		];
	}
}