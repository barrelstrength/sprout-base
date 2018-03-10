<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\services\sproutreports;

use barrelstrength\sproutbase\models\sproutreports\Report;
use Craft;
use craft\db\Query;
use yii\base\Component;
use barrelstrength\sproutbase\models\sproutreports\Report as ReportModel;
use barrelstrength\sproutbase\records\sproutreports\Report as ReportRecord;
use barrelstrength\sproutbase\records\sproutreports\ReportGroup as ReportGroupRecord;
use yii\base\Exception;

class Reports extends Component
{
    /**
     * @param $reportId
     *
     * @return ReportModel
     */
    public function getReport($reportId)
    {
        $reportRecord = ReportRecord::findOne($reportId);

        $reportModel = new ReportModel();

        if ($reportRecord != null) {
            $reportModel->setAttributes($reportRecord->getAttributes());
        }

        return $reportModel;
    }

    /**
     * @param ReportModel $reportModel
     *
     * @throws \Exception
     * @return bool
     */
    public function saveReport(ReportModel $reportModel)
    {
        if (!$reportModel) {

            Craft::info('Report not saved due to validation error.', __METHOD__);

            return false;
        }

        if (empty($reportModel->id)) {
            $reportRecord = new ReportRecord();
        } else {
            $reportRecord = ReportRecord::findOne($reportModel->id);
        }

        if (!$this->validateSettings($reportModel)) {
            return false;
        }

        $reportRecord->id = $reportModel->id;
        $reportRecord->name = $reportModel->name;
        $reportRecord->hasNameFormat = (bool) $reportModel->hasNameFormat;
        $reportRecord->nameFormat = $reportModel->nameFormat;
        $reportRecord->handle = $reportModel->handle;
        $reportRecord->description = $reportModel->description;
        $reportRecord->allowHtml = (bool) $reportModel->allowHtml;
        $reportRecord->settings = $reportModel->settings;
        $reportRecord->dataSourceId = $reportModel->dataSourceId;
        $reportRecord->enabled = (bool) $reportModel->enabled;
        $reportRecord->groupId = $reportModel->groupId;

        $db = Craft::$app->getDb();
        $transaction = $db->beginTransaction();

        try {
            $reportRecord->save(false);

            $reportModel->id = $reportRecord->id;

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    /**
     * @param ReportModel $report
     *
     * @return bool
     * @throws Exception
     */
    protected function validateSettings(ReportModel $report)
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
     * @return null|ReportModel[]
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
                $model = new ReportModel();
                $model->setAttributes($recordAttributes);

                $models[] = $model;
            }
        }

        return $models;
    }
}
