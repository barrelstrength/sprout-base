<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\services\sproutreports;

use barrelstrength\sproutbase\elements\sproutreports\Report;
use barrelstrength\sproutreports\SproutReports;
use Craft;
use craft\db\Query;
use yii\base\Component;
use barrelstrength\sproutbase\records\sproutreports\Report as ReportRecord;
use barrelstrength\sproutbase\records\sproutreports\ReportGroup as ReportGroupRecord;
use yii\base\Exception;

class Reports extends Component
{
    /**
     * @param $reportId
     *
     * @return Report
     */
    public function getReport($reportId)
    {
        $reportRecord = ReportRecord::findOne($reportId);

        $report = new Report();

        $report->id = $reportRecord->id;
        $report->dataSourceId = $reportRecord->dataSourceId;
        $report->groupId = $reportRecord->groupId;
        $report->name = $reportRecord->name;
        $report->hasNameFormat = $reportRecord->hasNameFormat;
        $report->nameFormat = $reportRecord->nameFormat;
        $report->handle = $reportRecord->handle;
        $report->description = $reportRecord->description;
        $report->allowHtml = $reportRecord->allowHtml;
        $report->settings = $reportRecord->settings;
        $report->enabled = $reportRecord->enabled;

        return $report;
    }

    /**
     * @param Report $report
     *
     * @throws \Exception
     * @return bool
     */
    public function saveReport(Report $report)
    {
        if (!$report) {

            Craft::info('Report not saved due to validation error.', __METHOD__);

            return false;
        }

        if (empty($report->id)) {
            $reportRecord = new ReportRecord();
        } else {
            $reportRecord = ReportRecord::findOne($report->id);
        }

//        if (!$this->validateSettings($report)) {
//            return false;
//        }

        $report->title = $report->name;

        $report->validate();

        if ($report->hasErrors()) {

            SproutReports::error('Unable to save Report.');

            return false;
        }

        $transaction = Craft::$app->db->beginTransaction();

        try {
            Craft::$app->getElements()->saveElement($report, false);

//            $reportRecord->save(false);

//            $report->id = $reportRecord->id;

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    /**
     * @param Report $report
     *
     * @return bool
     * @throws Exception
     */
    protected function validateSettings(Report $report)
    {
        $errors = [];

        $dataSource = $report->getDataSource();

        if ($dataSource AND !$dataSource->validateSettings($report->settings, $errors)) {
            $report->addError('settings', $errors);

            return false;
        }

        return true;
    }

    /**
     * @param $dataSourceId
     *
     * @return array
     */
    public function getReportsBySourceId($dataSourceId)
    {
        $reportRecords = ReportRecord::find()->where(['dataSourceId' => $dataSourceId])->all();

        return $this->populateModels($reportRecords);
    }

    /**
     * @return null|Report[]
     */
    public function getAllReports()
    {
        $rows = $this->getReportsQuery()->all();

        return $this->populateReports($rows);
    }

    private function getReportsQuery()
    {
        $query = new Query();
        // We only get reports that currently has dataSourceId or existing installed dataSource
        $query->select('reports.*')
        ->from('{{%sproutreports_reports}} as reports')
        ->innerJoin('{{%sproutreports_datasources}} as datasource', 'datasource.id = reports.dataSourceId');

        return $query;
    }

    private function populateReports($rows)
    {
        $reports = [];

        if ($rows) {
            foreach ($rows as $row) {

                $model = new Report();
                $model->setAttributes($row, false);
                $reports[] = $model;
            }
        }

        return $reports;
    }

    /**
     * @param $groupId
     *
     * @return array
     * @throws Exception
     */
    public function getReportsByGroupId($groupId)
    {
        $reports = [];

        $group = ReportGroupRecord::findOne($groupId);

        if ($group === null) {
            throw new Exception(Craft::t('sprout-base', 'No Report Group exists with id: {id}', [
                'id' => $groupId
            ]));
        }

        if ($group !== null) {
            $rows = $this->getReportsQuery()->where([
                'groupId' => $groupId
            ])->all();

            $reports = $this->populateReports($rows);
        }

        return $reports;
    }

    public function getReportsAsSelectFieldOptions()
    {
        $options = array();

        $reports = $this->getAllReports();

        if ($reports)
        {
            foreach ($reports as $report)
            {
                $options[] = array(
                    'label' => $report->name,
                    'value' => $report->id,
                );
            }
        }
        return $options;
    }

    /**
     * Returns the number of reports that have been created based on a given data source
     *
     * @param $dataSourceId
     *
     * @return int
     *
     */
    public function getCountByDataSourceId($dataSourceId)
    {
        return (int)ReportRecord::find()->where(['dataSourceId' => $dataSourceId])->count();
    }

    /**
     * @param array $records
     *
     * @return array
     */
    public function populateModels(array $records)
    {
        $models = [];

        if (!empty($records)) {
            foreach ($records as $record) {
                $recordAttributes = $record->getAttributes();
                $model = new Report();
                $model->setAttributes($recordAttributes);

                $models[] = $model;
            }
        }

        return $models;
    }
}
