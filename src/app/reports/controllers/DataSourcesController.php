<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\reports\controllers;

use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\errors\MissingComponentException;
use craft\web\Controller;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class DataSourcesController extends Controller
{
    /**
     * @throws ForbiddenHttpException
     */
    public function init()
    {
        // All Data Source actions require sproutReports-editDataSources permission
        $this->requirePermission('sprout:reports:editDataSources');
    }

    public function actionDataSourcesIndexTemplate(): Response
    {
        $dataSources = SproutBase::$app->dataSources->getDataSources();

        $config = SproutBase::$app->config->getConfigByKey('reports');

        return $this->renderTemplate('sprout/reports/datasources/index', [
            'dataSources' => $dataSources,
            'config' => $config
        ]);
    }

    /**
     * Save the Data Source
     *
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionSaveDataSource(): Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $dataSourceType = $request->getBodyParam('dataSourceType');

        $dataSource = new $dataSourceType();

        if (SproutBase::$app->dataSources->saveDataSource($dataSource)) {
            return $this->asJson(true);
        }

        return $this->asJson(false);
    }

    /**
     * @return Response
     * @throws MissingComponentException
     * @throws Exception
     * @throws BadRequestHttpException
     */
    public function actionDeleteDataSource(): Response
    {
        $this->requirePostRequest();

        $dataSourceId = Craft::$app->getRequest()->getRequiredBodyParam('dataSourceId');

        if (!SproutBase::$app->dataSources->deleteDataSourceById($dataSourceId)) {
            Craft::$app->getSession()->setError(Craft::t('sprout', 'Could not delete Data Source.'));
        } else {
            Craft::$app->getSession()->setNotice(Craft::t('sprout', 'Data Source deleted.'));
        }

        return $this->redirectToPostedUrl();
    }
}