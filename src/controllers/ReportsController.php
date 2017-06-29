<?php
namespace barrelstrength\sproutcore\controllers;

use barrelstrength\sproutcore\models\sproutreports\Report;
use barrelstrength\sproutcore\services\sproutreports\DataSourcesCore;
use barrelstrength\sproutcore\SproutCore;
use Craft;

use craft\web\assets\cp\CpAsset;
use craft\web\Controller;
use barrelstrength\sproutreports\SproutReports;

class ReportsController extends Controller
{
	public function actionIndex($groupId = null)
	{
		return $this->renderTemplate('sprout-core/sproutreports/reports/index', [
			'groupId' => $groupId
		]);
	}

	public function actionResultsIndex($dataSourceId = null)
	{
		$dataSource = null;

		$report = new Report();

		if (Craft::$app->getRequest()->getParam('dataSourceId'))
		{
			$dataSourceId = Craft::$app->getRequest()->getParam('dataSourceId');
		}

		if ($dataSourceId != null)
		{
			$dataSourcesCore = new DataSourcesCore();

			$dataSource = $dataSourcesCore->getDataSourceById($dataSourceId);

			$dataSource->setReport($report);
		}

		$options = Craft::$app->getRequest()->getParam('options');

		$options = count($options) ? $options : array();

		if ($report)
		{
			$labels     = $dataSource->getDefaultLabels($report, $options);

			$variables['dataSource'] = null;
			$variables['report']     = $report;
			$variables['values']     = array();
			$variables['options']    = $options;

			if ($dataSource)
			{
				$values = $dataSource->getResults($report, $options);

				if (empty($labels) && !empty($values))
				{
					$firstItemInArray = reset($values);
					$labels           = array_keys($firstItemInArray);
				}

				$variables['labels']     = $labels;
				$variables['values']     = $values;
				$variables['dataSource'] = $dataSource;
			}

			$this->getView()->registerAssetBundle(CpAsset::class);

			// @todo Hand off to the export service when a blank page and 404 issues are sorted out
			return $this->renderTemplate('sprout-core/sproutreports/results/index', $variables);
		}

		throw new \HttpException(404, SproutReports::t('Report not found.'));
	}
}
