<?php

namespace barrelstrength\sproutcore\records;

use craft\db\ActiveRecord;

class Address extends ActiveRecord
{
	/**
	 * @inheritdoc
	 *
	 * @return string
	 */
	public static function tableName(): string
	{
		return '{{%sproutcore_addresses}}';
	}
}
