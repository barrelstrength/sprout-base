<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\reports\records;

use craft\db\ActiveRecord;
use yii\db\ActiveQuery;

/**
 *
 * @property ActiveQuery $reports
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

    public function getReports(): ActiveQuery
    {
        return $this->hasMany(Report::class, ['groupId' => 'id']);
    }

    public function beforeDelete(): bool
    {
//        $reports = SproutReports_ReportRecord::model()->findAll('groupId =:groupId', [
//            ':groupId' => $this->id
//        ]);
//
//        foreach ($reports as $report)
//        {
//            $record = SproutReports_ReportRecord::model()->findById($report->id);
//            $record->groupId = null;
//            $record->save(false);
//        }

        return true;
    }
}
