<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\reports\services;

use barrelstrength\sproutbase\app\reports\elements\Report;
use barrelstrength\sproutbase\app\reports\records\DataSource as DataSourceRecord;
use barrelstrength\sproutbase\app\reports\records\Report as ReportRecord;
use barrelstrength\sproutbase\app\reports\records\ReportGroup as ReportGroupRecord;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\db\Query;
use Throwable;
use yii\base\Component;
use yii\base\Exception;
use yii\db\Transaction;

/**
 *
 * @property array $defaultDataSourceIds
 * @property null|Report[] $allReports
 * @property array $reportsAsSelectFieldOptions
 * @property array $allowedDataSourceIds
 */
class Reports extends Component
{
    /**
     * @var array
     */
    protected $_allowedDataSourceIds;

    /**
     * @var array
     */
    protected $_defaultDataSourceIds;

    public function getAllowedDataSourceIds(): array
    {
        if (!$this->_allowedDataSourceIds) {
            $this->populateDataSourceIds();
        }

        return $this->_allowedDataSourceIds;
    }

    public function getDefaultDataSourceIds(): array
    {
        if (!$this->_defaultDataSourceIds) {
            $this->populateDataSourceIds();
        }

        return $this->_defaultDataSourceIds;
    }

    /**
     * @param Report $report
     *
     * @return bool
     * @throws Throwable
     */
    public function saveReport(Report $report): bool
    {
        if (!$report) {
            Craft::info('Report not saved due to validation error.', __METHOD__);

            return false;
        }

        $report->title = $report->name;

        $report->validate();

        if ($report->hasErrors()) {
            Craft::error('Unable to save Report.', __METHOD__);

            return false;
        }

        /** @var Transaction $transaction */
        $transaction = Craft::$app->db->beginTransaction();

        try {
            Craft::$app->getElements()->saveElement($report, false);

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    /**
     * @param $dataSourceId
     *
     * @return array
     */
    public function getReportsBySourceId($dataSourceId): array
    {
        $reportRecords = ReportRecord::find()
            ->where([
                'dataSourceId' => $dataSourceId
            ])
            ->all();

        return $this->populateModels($reportRecords);
    }

    /**
     * @return null|Report[]
     */
    public function getAllReports()
    {
        $rows = (new Query())
            ->select('reports.*')
            ->from(ReportRecord::tableName().' reports')
            ->innerJoin(
                DataSourceRecord::tableName().' datasource',
                '[[datasource.id]] = [[reports.dataSourceId]]')
            ->all();

        return $this->populateReports($rows);
    }

    /**
     * @param $groupId
     *
     * @return array
     * @throws Exception
     */
    public function getReportsByGroupId($groupId): array
    {
        $reports = [];

        $group = ReportGroupRecord::findOne($groupId);

        if ($group === null) {
            throw new Exception('No Report Group exists with ID: '.$groupId);
        }

        if ($group !== null) {
            $rows = (new Query())
                ->select('reports.*')
                ->from(ReportRecord::tableName().' reports')
                ->innerJoin(
                    DataSourceRecord::tableName().' datasource',
                    '[[datasource.id]] = [[reports.dataSourceId]]')
                ->where([
                    'groupId' => $groupId
                ])
                ->all();

            $reports = $this->populateReports($rows);
        }

        return $reports;
    }

    public function getReportsAsSelectFieldOptions(): array
    {
        $options = [];

        $reports = $this->getAllReports();

        if ($reports) {
            foreach ($reports as $report) {
                $options[] = [
                    'label' => $report->name,
                    'value' => $report->id,
                ];
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
    public function getCountByDataSourceId($dataSourceId): int
    {
        $totalReportsForDataSource = ReportRecord::find()
            ->where([
                'dataSourceId' => $dataSourceId
            ])
            ->count();

        return (int)$totalReportsForDataSource;
    }

    /**
     * @param array $records
     *
     * @return array
     */
    public function populateModels(array $records): array
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

    /**
     * @param Report $report
     *
     * @return bool
     */
    protected function validateSettings(Report $report): bool
    {
        $errors = [];

        $dataSource = $report->getDataSource();

        if ($dataSource && !$dataSource->validateSettings($report->settings, $errors)) {
            $report->addError('settings', $errors);

            return false;
        }

        return true;
    }

    private function populateReports($rows): array
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

    private function populateDataSourceIds()
    {
        $configs = SproutBase::$app->config->getConfigs(false);

        $dataSourceTypes = [];

        foreach ($configs as $config) {
            $settings = $config->getSettings();

            if (!$settings || ($settings && !$settings->getIsEnabled())) {
                continue;
            }

            if (!method_exists($config, 'getSupportedDataSourceTypes')) {
                continue;
            }

            foreach ($config->getSupportedDataSourceTypes() as $dataSourceType) {
                $dataSourceTypes[] = $dataSourceType;
            }
        }

        $dataSourceTypes = array_filter($dataSourceTypes);

        $dataSourceIds = (new Query())
            ->select('id')
            ->from('{{%sproutreports_datasources}}')
            ->where(['in', 'type', $dataSourceTypes])
            ->column();

        $reportsConfig = SproutBase::$app->config->getConfigByKey('reports');
        $reportsDataSourceTypes = $reportsConfig->getSupportedDataSourceTypes();

        $reportsDataSourceIds = (new Query())
            ->select('id')
            ->from('{{%sproutreports_datasources}}')
            ->where(['in', 'type', $reportsDataSourceTypes])
            ->column();

        $this->_allowedDataSourceIds = $dataSourceIds;
        $this->_defaultDataSourceIds = $reportsDataSourceIds;
    }
}
