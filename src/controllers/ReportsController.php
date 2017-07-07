<?php
namespace barrelstrength\sproutcore\controllers;

use barrelstrength\sproutcore\models\sproutreports\Report;
use barrelstrength\sproutcore\records\sproutreports\Report as ReportRecord;
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

	public function actionResultsIndex(Report $report = null, int $reportId = null)
	{
		if (isset($report))
		{
			$reportModel = $report;
		}
		else
		{
			$reportModel = SproutCore::$app->reports->getReport($reportId);
		}

		if ($reportModel)
		{
			$dataSource = $reportModel->getDataSource();

			$labels     = $dataSource->getDefaultLabels($reportModel);

			$variables['dataSource'] = null;
			$variables['report']     = $reportModel;
			$variables['values']     = array();
			$variables['reportId']   = $reportId;
			$variables['redirectUrl'] = $dataSource->getLowerPluginHandle() . '/reports/view/' . $reportId;

			if ($dataSource)
			{
				$values = $dataSource->getResults($reportModel);

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
		$reportModel = new Report();

		if (isset($report))
		{
			$reportModel = $report;
		}
		elseif ($reportId != null)
		{
			$reportModel = SproutCore::$app->reports->getReport($reportId);
		}

		// This is for creating new report
		if ($dataSourceId != null)
		{
			$reportModel->dataSourceId = $dataSourceId;
		}

		$dataSource = $reportModel->getDataSource();

		return $this->renderTemplate('sprout-core/sproutreports/reports/_edit', array(
			'report'             => $reportModel,
			'dataSource'         => $dataSource,
			'continueEditingUrl' => $dataSource->getUrl() . '/edit/{id}'
		));
	}

	/**
	 * Saves a report query to the database
	 */
	public function actionUpdateReport()
	{
		$this->requirePostRequest();

		$request = Craft::$app->getRequest();

		$reportModel = new Report();

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

		// Encode back to object after validation for getResults method to recognize option object
		$reportModel->options = json_encode($reportModel->options);

		Craft::$app->getSession()->setError(SproutCore::t('Could not update report.'));

		// Send the report back to the template
		Craft::$app->getUrlManager()->setRouteParams([
			'report' => $reportModel
		]);

		return null;
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

			// Send the report back to the template
			Craft::$app->getUrlManager()->setRouteParams([
				'report' => $report
			]);

			return null;
		}

		Craft::$app->getSession()->setNotice(SproutCore::t('Report saved.'));

		return $this->redirectToPostedUrl($report);
	}

	public function actionDeleteReport()
	{
		$this->requirePostRequest();

		$reportId = Craft::$app->getRequest()->getBodyParam('reportId');

		if ($record = ReportRecord::findOne($reportId))
		{
			$record->delete();

			Craft::$app->getSession()->setNotice(SproutCore::t('Report deleted.'));

			return $this->redirectToPostedUrl($record);
		}
		else
		{
			throw new \Exception(SproutCore::t('Report not found.'));
		}
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
