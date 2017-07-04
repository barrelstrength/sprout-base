<?php
namespace barrelstrength\sproutcore\records\sproutreports;

use craft\db\ActiveRecord;

/**
 * Class ReportGroup
 *
 * @package barrelstrength\sproutcore\records\sproutreports
 */
class ReportGroup extends ActiveRecord
{
	/**
	 * @return string
	 */
	public static function tableName(): string
	{
		return '{{%sproutreports_reportgroups}}';
	}

	public function getReports()
	{
		return $this->hasMany(Report::class, ['groupId' => 'id']);
	}

	public function beforeDelete()
	{
/*
		$reports = SproutReports_ReportRecord::model()->findAll('groupId =:groupId',array(
				':groupId' => $this->id
			)
		);

		foreach ($reports as $report)
		{
			$record = SproutReports_ReportRecord::model()->findById($report->id);
			$record->groupId = null;
			$record->save(false);
		}*/

		return true;
	}
}
