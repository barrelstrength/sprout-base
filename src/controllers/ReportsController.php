<?php
namespace barrelstrength\sproutcore\controllers;

use barrelstrength\sproutcore\models\sproutreports\Report;
use barrelstrength\sproutcore\services\sproutreports\DataSources;
use barrelstrength\sproutcore\SproutCore;
use Craft;

use craft\web\assets\cp\CpAsset;
use craft\web\Controller;

class ReportsController extends Controller
{
	public function actionIndex($dataSourceId = null)
	{
		$dataSource = SproutCore::$app->dataSources->getDataSourceById($dataSourceId);

		$reports = SproutCore::$app->reports->getReportsBySourceId($dataSourceId);

		return $this->renderTemplate('sprout-core/sproutreports/reports/index', [
			'groupId' => null,
			'reports' => $reports,
		  'dataSource' => $dataSource
		]);
	}

	public function actionResultsIndex($reportId = null)
	{
		$report = SproutCore::$app->reports->getReport($reportId);

		$options = Craft::$app->getRequest()->getBodyParam('options');
		$options = count($options) ? $options : array();

		if ($report)
		{

			$dataSource = SproutCore::$app->dataSources->getDataSourceById($report->dataSourceId);

			$dataSource->setReport($report);

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

			$this->getView()->registerAssetBundle(CpAsset::class);

			// @todo Hand off to the export service when a blank page and 404 issues are sorted out
			return $this->renderTemplate('sprout-core/sproutreports/results/index', $variables);
		}

		throw new \HttpException(404, SproutCore::t('Report not found.'));
	}

	public function actionEditReport(string $dataSourceKey, Report $report = null, int $reportId = null)
	{
		$variables = array();

		$variables['report'] = new Report();

		if (isset($report))
		{
			$variables['report'] = $report;
		}

		if ($reportId != null)
		{
			$reportModel = SproutCore::$app->reports->getReport($reportId);

			$variables['report'] = $reportModel;
		}

		$variables['report']->dataSourceId = $dataSourceKey;

		$variables['dataSource']           = $variables['report']->getDataSource();

		$variables['continueEditingUrl']   = $variables['dataSource']->getUrl() . '/edit/{id}';

		return $this->renderTemplate('sprout-core/sproutreports/reports/_edit', $variables);
	}

	public function actionExportReport()
	{
		$reportId = Craft::$app->getRequest()->getParam('reportId');

		$report   = SproutCore::$app->reports->getReport($reportId);

		$options = Craft::$app->getRequest()->getBodyParam('options');

		$options = count($options) ? $options : array();

		if ($report)
		{
			$dataSource = SproutCore::$app->dataSources->getDataSourceById($report->dataSourceId);

			if ($dataSource)
			{
				$date = date("Ymd-his");

				$filename = $report->name . '-' . $date;
				$labels   = $dataSource->getDefaultLabels($report, $options);
				$values   = $dataSource->getResults($report, $options);

				SproutCore::$app->exports->toCsv($values, $labels, $filename);
			}
		}
	}
}
