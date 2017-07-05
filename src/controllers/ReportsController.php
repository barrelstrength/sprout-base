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
			$variables['redirectUrl'] = $dataSource->getLowerPluginHandle() . '/reports/view/' . $reportId;

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

	public function actionEditReport(string $dataSourceId, Report $report = null, int $reportId = null)
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

		$variables['report']->dataSourceId = $dataSourceId;

		$variables['dataSource']           = $variables['report']->getDataSource();

		$variables['continueEditingUrl']   = $variables['dataSource']->getUrl() . '/edit/{id}';

		return $this->renderTemplate('sprout-core/sproutreports/reports/_edit', $variables);
	}

	/**
	 * Saves a report query to the database
	 */
	public function actionUpdateReport()
	{
		$this->requirePostRequest();

		$request = Craft::$app->getRequest();

		$reportId = $request->getBodyParam('reportId');
		$options  = $request->getBodyParam('options');

		if ($reportId && $options)
		{
			$reportModel = SproutCore::$app->reports->getReport($reportId);

			if (!$reportModel)
			{
				throw new \Exception(SproutCore::t('No report exists with the id “{id}”', array('id' => $reportId)));
			}

			$reportModel->options = is_array($options) ? $options : array();

			if (SproutCore::$app->reports->saveReport($reportModel))
			{
				Craft::$app->getSession()->setNotice(SproutCore::t('Query updated.'));

				return $this->redirectToPostedUrl($reportModel);
			}
		}

		Craft::$app->getSession()->setError(SproutCore::t('Could not update report.'));

		return $this->redirectToPostedUrl();
	}

	/**
	 * Saves a report query to the database
	 * @return null|\yii\web\Response
	 */
	public function actionSaveReport()
	{
		$this->requirePostRequest();

		$report = SproutCore::$app->reports->prepareFromPost();

		if (!SproutCore::$app->reports->saveReport($report))
		{
			Craft::$app->getSession()->setError(SproutCore::t('Couldn’t save report.'));

			// Send the section back to the template
			Craft::$app->getUrlManager()->setRouteParams([
				'report' => $report
			]);

			return null;
		}

		Craft::$app->getSession()->setNotice(SproutCore::t('Report saved.'));

		return $this->redirectToPostedUrl($report);
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
