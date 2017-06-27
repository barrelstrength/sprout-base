<?php
namespace barrelstrength\sproutcore\services\sproutreports;

use barrelstrength\sproutcore\contracts\sproutreports\BaseDataSource;
use yii\base\Component;
use craft\events\RegisterComponentTypesEvent;

/**
 * Class DataSources
 *
 * @package Craft
 */
class DataSourcesCore  extends Component
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

		throw new \Exception(\Craft::t('Could not find data source with id {id}.', compact('id')));
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
}
