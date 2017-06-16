<?php
namespace barrelstrength\sproutcore\controllers;

use Craft;
use craft\web\Controller;
use barrelstrength\sproutreports\SproutReports;
use barrelstrength\sproutreports\models\Report;
use barrelstrength\sproutreports\records\Report as ReportRecord;

class ReportsController extends Controller
{
	public function actionResultsIndex($reportId = null)
	{
		$report = SproutReports::$app->reports->getReport($reportId);

		$options = Craft::$app->getRequest()->getBodyParam('options');
		$options = count($options) ? $options : array();

		if ($report)
		{
			$dataSource = SproutReports::$app->dataSources->getDataSourceById($report->dataSourceId);
			$labels     = $dataSource->getDefaultLabels($report, $options);

			$variables['dataSource'] = null;
			$variables['report']     = $report;
			$variables['values']     = array();
			$variables['options']    = $options;
			$variables['reportId']   = $reportId;

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

			// @todo Hand off to the export service when a blank page and 404 issues are sorted out
			return $this->renderTemplate('sproutreports/results/index', $variables);
		}

		throw new \HttpException(404, SproutReports::t('Report not found.'));
	}
}
