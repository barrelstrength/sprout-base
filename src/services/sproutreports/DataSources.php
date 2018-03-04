<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\services\sproutreports;

use barrelstrength\sproutbase\contracts\sproutreports\BaseDataSource;
use barrelstrength\sproutbase\models\sproutreports\DataSource as DataSourceModel;
use barrelstrength\sproutbase\records\sproutreports\DataSource as DataSourceRecord;
use yii\base\Component;
use craft\events\RegisterComponentTypesEvent;
use craft\db\Query;
use Craft;

/**
 * Class DataSources
 *
 * @package Craft
 */
class DataSources extends Component
{
    /**
     * @event
     */
    const EVENT_REGISTER_DATA_SOURCES = 'registerSproutReportsDataSources';

    /**
     * @param $dataSourceId
     *
     * @return BaseDataSource|null
     */
    public function getDataSourceById($dataSourceId)
    {
        $dataSourceRecord = DataSourceRecord::find()->where([
            'id' => $dataSourceId
        ])->one();

        if ($dataSourceRecord === null)
        {
            return null;
        }

        $dataSource = new $dataSourceRecord->type;
        $dataSource->dataSourceId = $dataSourceRecord->id;

        return $dataSource;
    }

    public function installDataSources(array $dataSourceClasses = [])
    {
        foreach ($dataSourceClasses as $dataSourceClass) {

            $dataSourceModel = new DataSourceModel();
            $dataSourceModel->type = $dataSourceClass;
            $dataSourceModel->allowNew = 1;

            $this->saveDataSource($dataSourceModel);
        }
    }

    /**
     * Returns all available Data Source classes
     *
     * @return string[]
     */
    public function getAllDataSourceTypes()
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
     * @return array
     */
    public function getAllDataSources()
    {
        $dataSourceTypes = $this->getAllDataSourceTypes();
        $dataSourceRecords = DataSourceRecord::find()->all();

        $dataSources = [];

        foreach ($dataSourceTypes as $dataSourceType) {
            $dataSources[$dataSourceType] = new $dataSourceType;
        }



        // Add the additional data we store in the database to the Data Source classes
        foreach ($dataSourceRecords as $dataSourceRecord)
        {
            if ($dataSourceRecord->type === get_class($dataSources[$dataSourceRecord->type]))
            {
                $dataSources[$dataSourceRecord->type]->dataSourceId = $dataSourceRecord->id;
                $dataSources[$dataSourceRecord->type]->allowNew = $dataSourceRecord->allowNew;
            }
        }

        // Make sure all registered datasources have a record in the database
        foreach ($dataSources as $dataSourceClass => $dataSource)
        {
            if ($dataSource->dataSourceId === null)
            {
                $this->installDataSources([$dataSourceClass]);
            }
        }

        return $dataSources;
    }

    /**
     * @param $names
     * @param $secondaryArray
     */
    private function _sortDataSources(&$names, &$secondaryArray)
    {
        // Sort plugins by name
        array_multisort($names, SORT_NATURAL | SORT_FLAG_CASE, $secondaryArray);
    }

    /**
     * Save attributes to datasources record table
     *
     * @param DataSourceModel $dataSourceModel
     *
     * @return bool
     * @throws \yii\db\Exception
     */
    public function saveDataSource(DataSourceModel $dataSourceModel)
    {
        $dataSourceRecord = DataSourceRecord::find()
            ->where(['id' => $dataSourceModel->id])
            ->one();

        if ($dataSourceRecord !== null)
        {
            $dataSourceRecord->id = $dataSourceModel->id;
        }
        else
        {
            $dataSourceRecord = new DataSourceRecord();
            $dataSourceRecord->type = $dataSourceModel->type;
        }

        $dataSourceRecord->allowNew = $dataSourceModel->allowNew;

        $transaction = Craft::$app->getDb()->beginTransaction();

        if ($dataSourceRecord->validate()) {
            if ($dataSourceRecord->save(false)) {
                $dataSourceModel->id = $dataSourceRecord->id;

                if ($transaction) {
                    $transaction->commit();
                }

                return true;
            }
        } else {
            $dataSourceModel->addErrors($dataSourceRecord->getErrors());
        }

        return false;
    }

    /**
     * Delete reports by type
     *
     * @param $type
     *
     * @return int
     * @throws \yii\db\Exception
     */
    public function deleteReportsByType($type)
    {
        $query = new Query();
        $result = $query->createCommand()
            ->delete('sproutreports_report', ['type' => $type])
            ->execute();

        return $result;
    }
}
