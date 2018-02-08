<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\services\sproutreports;

use barrelstrength\sproutbase\contracts\sproutreports\BaseReport;
use barrelstrength\sproutbase\models\sproutreports\ReportGroup as ReportGroupModel;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use yii\base\Component;
use barrelstrength\sproutbase\models\sproutreports\Report as ReportModel;
use barrelstrength\sproutbase\records\sproutreports\Report as ReportRecord;
use barrelstrength\sproutbase\records\sproutreports\ReportGroup as ReportGroupRecord;

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
     * @param ReportModel $model
     *
     * @throws \Exception
     * @return bool
     */
    public function saveReport(&$model)
    {
        if (!$model) {

            Craft::info('Report not saved due to validation error.', __METHOD__);

            return false;
        }

        if (empty($model->id)) {
            $record = new ReportRecord();
        } else {
            $record = ReportRecord::findOne($model->id);
        }

        if (!$this->validateOptions($model)) {
            return false;
        }

        $record->id = $model->id;
        $record->name = $model->name;
        $record->nameFormat = $model->nameFormat;
        $record->handle = $model->handle;
        $record->description = $model->description;
        $record->allowHtml = $model->allowHtml;
        $record->options = $model->options;
        $record->dataSourceId = $model->dataSourceId;
        $record->enabled = $model->enabled;
        $record->groupId = $model->groupId;

        $db = Craft::$app->getDb();
        $transaction = $db->beginTransaction();
        try {
            $record->save(false);

            $model->id = $record->id;

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    /**
     * @param $report
     *
     * @return bool
     */
    protected function validateOptions(ReportModel $report)
    {
        $errors = [];

        $dataSource = $report->getDataSource();

        if ($dataSource AND !$dataSource->validateOptions($report->options, $errors)) {
            $report->addError('options', $errors);

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
        $reportRecords = ReportRecord::find()->all();

        return $this->populateModels($reportRecords);
    }

    /**
     * Get only dataSources that are activated by other plugins
     * @return array
     */
    public function getAvailableReports()
    {
        $reports = $this->getAllReports();
        $dataSources = SproutBase::$app->dataSources->getAllDataSources();

        $dataSourceIds = array_keys($dataSources);

        $availableReports = [];
        if ($reports) {
            foreach ($reports as $report) {
                $dataSourceId = $report['dataSourceId'];

                if (in_array($dataSourceId, $dataSourceIds)) {
                    $availableReports[] = $report;
                }
            }
        }

        return $availableReports;
    }

    /**
     * @param                  $reports
     * @param ReportGroupModel $group
     */
    public function registerReports($reports, ReportGroupModel $group)
    {
        if (!is_array($reports)) {
            $reports = [$reports];
        }

        foreach ($reports as $report) {
            if ($report instanceof BaseReport) {
                $record = new ReportRecord();

                $record->name = $report->getName();
                $record->handle = $report->getHandle();
                $record->description = $report->getDescription();
                $record->options = $report->getOptions();
                $record->dataSourceId = $report->getDataSource()->getId();
                $record->enabled = true;
                $record->groupId = $group->id;

                if (!$record->save()) {
                    SproutBase::warning($record->getErrors(), 'sproutReports');
                }
            }
        }
    }

    /**
     * @param int $groupId
     *
     * @return null|ReportModel[]
     */
    public function getReportsByGroupId($groupId)
    {
        $reports = [];

        $group = ReportGroupRecord::findOne($groupId);

        if ($group != null) {
            $reportRecords = $group->getReports()->all();

            $reports = $this->populateModels($reportRecords);
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
