<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutcore\records\sproutreports;

use craft\db\ActiveRecord;

/**
 * Class DataSource
 *
 * @package barrelstrength\sproutcore\records\sproutreports
 */
class DataSource extends ActiveRecord
{
	/**
	 * @return string
	 */
	public static function tableName(): string
	{
		return '{{%sproutreports_datasources}}';
	}
}