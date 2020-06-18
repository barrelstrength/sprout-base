<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\reports\services;

use barrelstrength\sproutbase\app\reports\base\DataSource;
use barrelstrength\sproutbase\app\reports\base\DataSourceInterface;
use barrelstrength\sproutbase\app\reports\datasources\MissingDataSource;
use barrelstrength\sproutbase\app\reports\records\DataSource as DataSourceRecord;
use barrelstrength\sproutbase\app\reports\records\Report as ReportRecord;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\db\Query;
use craft\errors\MissingComponentException;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\Component as ComponentHelper;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\db\Transaction;

/**
 * Class DataSources
 *
 * @package Craft
 *
 * @property array $allDataSources
 * @property mixed $dataSourcePlugins
 * @property DataSource[] $installedDataSources
 * @property string[] $allDataSourceTypes
 */
class DataSources extends Component
{
    /**
     * @event
     */
    const EVENT_REGISTER_DATA_SOURCES = 'registerSproutReportsDataSources';

    private $dataSources;

    /**
     * @param int $id
     *
     * @return DataSource|null
     */
    public function getDataSourceById($id)
    {
        /**
         * @var DataSourceRecord $dataSourceRecord
         */
        $dataSourceRecord = DataSourceRecord::find()->where([
            'id' => $id
        ])->one();

        if ($dataSourceRecord === null) {
            return null;
        }

        if (class_exists($dataSourceRecord->type)) {
            $dataSource = new $dataSourceRecord->type;
            $dataSource->id = $dataSourceRecord->id;

            return $dataSource;
        }

        return null;
    }

    /**
     * Returns all available Data Source classes
     *
     * @return string[]
     */
    public function getAllDataSourceTypes(): array
    {
        $event = new RegisterComponentTypesEvent([
            'types' => []
        ]);

        $this->trigger(self::EVENT_REGISTER_DATA_SOURCES, $event);

        return $event->types;
    }

    /**
     * Returns all Data Sources
     *
     * @return DataSource[]
     */
    public function getInstalledDataSources(): array
    {
        $query = (new Query())
            ->select(['*'])
            ->from([DataSourceRecord::tableName()]);

        $dataSources = [];

        foreach ($query->all() as $dataSource) {

            $dataSourceType = $dataSource['type'];

            if (class_exists($dataSourceType)) {
                $dataSources[$dataSourceType] = new $dataSourceType();
                $dataSources[$dataSourceType]->id = $dataSource['id'];
                $dataSources[$dataSourceType]->allowNew = $dataSource['allowNew'];
            } else {
                Craft::error('Unable to find Data Source: '.$dataSourceType, __METHOD__);
                $dataSources[MissingDataSource::class] = new MissingDataSource();
                $dataSources[MissingDataSource::class]->id = $dataSource['id'];
                $dataSources[MissingDataSource::class]->setDescription($dataSourceType);
            }
        }

        $this->dataSources = $dataSources;

        uasort($dataSources, static function($a, $b) {
            /**
             * @var $a DataSource
             * @var $b DataSource
             */
            return $a::displayName() <=> $b::displayName();
        });

        return $dataSources;
    }

    /**
     * @param array $dataSourceTypes
     *
     * @return DataSource|null
     */
    public function installDataSources(array $dataSourceTypes = [])
    {
        $dataSources = null;

        foreach ($dataSourceTypes as $dataSourceClass) {

            /** @var DataSource $dataSource */
            $dataSource = new $dataSourceClass();

            $this->saveDataSource($dataSource);

            $dataSources = $dataSource;
        }

        return $dataSources;
    }

    /**
     * Save attributes to datasources record table
     *
     * @param DataSource $dataSource
     *
     * @return bool
     */
    public function saveDataSource(DataSource $dataSource): bool
    {
        /**
         * Check for an existing Data Source of this type
         *
         * @var $dataSourceRecord DataSourceRecord
         */
        $dataSourceRecord = DataSourceRecord::find()
            ->where(['type' => get_class($dataSource)])
            ->one();

        if ($dataSourceRecord === null) {
            $dataSourceRecord = new DataSourceRecord();
        }

        $dataSourceRecord->type = get_class($dataSource);
        $dataSourceRecord->allowNew = $dataSource->allowNew ?? $dataSourceRecord->allowNew ?? true;

        if (!$dataSourceRecord->validate()) {
            $dataSource->addErrors($dataSourceRecord->getErrors());

            return false;
        }

        $dataSourceRecord->save(false);

        $dataSource->id = $dataSourceRecord->id;

        return true;
    }

    /**
     * Delete reports by type
     *
     * @param $type
     *
     * @return bool
     * @throws \yii\db\Exception
     */
    public function deleteReportsByType($type): bool
    {
        $query = new Query();

        $source = $query
            ->select(['id', 'type'])
            ->from([DataSourceRecord::tableName()])
            ->where(['type' => $type])
            ->one();

        if ($source) {
            $query->createCommand()
                ->delete(ReportRecord::tableName(), ['[[dataSourceId]]' => $source['id']])
                ->execute();
        }

        return true;
    }

    /**
     * @param $config
     *
     * @return DataSourceInterface
     * @throws InvalidConfigException
     */
    public function createDataSource($config): DataSourceInterface
    {
        if (is_string($config)) {
            $config = ['type' => $config];
        }

        try {
            /** @var DataSource $dataSource */
            $dataSource = ComponentHelper::createComponent($config, DataSourceInterface::class);
        } catch (MissingComponentException $e) {
            $config['errorMessage'] = $e->getMessage();
            $config['expectedType'] = $config['type'];
            unset($config['type']);

            $dataSource = new MissingDataSource($config);
        }

        return $dataSource;
    }

    /**
     * @param $dataSourceId
     *
     * @return bool
     * @throws Exception
     */
    public function deleteDataSourceById($dataSourceId): bool
    {
        $reports = SproutBase::$app->reports->getReportsBySourceId($dataSourceId);

        /** @var Transaction $transaction */
        $transaction = Craft::$app->db->beginTransaction();

        try {
            foreach ($reports as $report) {
                Craft::$app->getDb()->createCommand()
                    ->delete(ReportRecord::tableName(), [
                        '[[id]]' => $report->id
                    ])
                    ->execute();
            }

            Craft::$app->getDb()->createCommand()
                ->delete(DataSourceRecord::tableName(), [
                    '[[id]]' => $dataSourceId
                ])
                ->execute();

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }
}
