<?php

namespace barrelstrength\sproutcore\records\sproutfields;

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
		return '{{%sproutfields_addresses}}';
	}
}
