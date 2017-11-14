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
use barrelstrength\sproutbase\SproutBase;
use yii\base\Component;
use craft\events\RegisterComponentTypesEvent;

/**
 * Class DataSources
 *
 * @package Craft
 */
class DataSources  extends Component
{

	const EVENT_REGISTER_DATA_SOURCES = "registerSproutReportsDataSources";
	/**
	 * @var BaseDataSource[]
	 */
	protected $dataSources;

	/**
	 *
	 * @param string $id
	 *
	 * @throws \Exception
	 * @return BaseDataSource
	 */
	public function getDataSourceById($id)
	{
		$sources = $this->getAllDataSources();

		if (isset($sources[$id]))
		{
			return $sources[$id];
		}

		throw new \Exception(SproutBase::t('Could not find data source with id {id}.', compact('id')));
	}

	/**
	 * @return null|BaseDataSource[]
	 */
	public function getAllDataSources()
	{
		if (is_null($this->dataSources))
		{
			$event = new RegisterComponentTypesEvent([
				'types' => []
			]);

			$this->trigger(self::EVENT_REGISTER_DATA_SOURCES, $event);

			$responses = $event->types;

			$names = array();

			if ($responses)
			{
				/**
				 * @var BaseDataSource $dataSource
				 */
				foreach ($responses as $dataSource)
				{
					if ($dataSource && $dataSource instanceof BaseDataSource)
					{
						$this->dataSources[$dataSource->getId()] = $dataSource;

						$names[] = $dataSource->getName();
					}
				}

				// Sort data sources by name
				$this->_sortDataSources($names, $this->dataSources);
			}
		}

		return $this->dataSources;
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
	 * @param DataSourceModel $model
	 *
	 * @return bool
	 */
	public function saveDataSource(DataSourceModel $model)
	{
		$result = false;

		$record = DataSourceRecord::find()
			->where(['dataSourceId' => $model->dataSourceId])
			->one();

		if ($record == null)
		{
			$record = new DataSourceRecord();
		}

		$attributes = $model->getAttributes();

		if (!empty($attributes))
		{
			foreach ($attributes as $handle => $value)
			{
				// Ignore id for dataSourceId
				if ($handle == 'id') continue;

				$record->setAttribute($handle, $value);
			}
		}

		$db = \Craft::$app->getDb();
		$transaction = $db->beginTransaction();

		if ($record->validate())
		{
			if ($record->save(false))
			{
				$model->id = $record->id;

				if ($transaction)
				{
					$transaction->commit();
				}

				$result = true;
			}
		}
		else
		{
			$model->addErrors($record->getErrors());
		}

		return $result;
	}
}
