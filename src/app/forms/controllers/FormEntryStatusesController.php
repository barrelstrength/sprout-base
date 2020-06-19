<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\controllers;

use barrelstrength\sproutbase\app\forms\models\EntryStatus;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\web\Controller as BaseController;
use Exception;
use Throwable;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class FormEntryStatusesController extends BaseController
{
    /**
     * @param int|null $entryStatusId
     * @param EntryStatus|null $entryStatus
     *
     * @return Response
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     */
    public function actionEdit(int $entryStatusId = null, EntryStatus $entryStatus = null): Response
    {
        $this->requireAdmin(false);

        if (!$entryStatus) {
            if ($entryStatusId) {
                $entryStatus = SproutBase::$app->formEntryStatuses->getEntryStatusById($entryStatusId);

                if (!$entryStatus->id) {
                    throw new NotFoundHttpException('Entry Status not found');
                }

                if ($entryStatus->handle == EntryStatus::SPAM_STATUS_HANDLE) {
                    Craft::$app->session->setError(Craft::t('sprout', "Spam status can't be updated"));

                    return $this->redirect(UrlHelper::cpUrl('sprout/settings/entry-statuses'));
                }
            } else {
                $entryStatus = new EntryStatus();
            }
        }

        return $this->renderTemplate('sprout/_settings/entry-statuses/edit', [
            'entryStatus' => $entryStatus,
            'entryStatusId' => $entryStatusId
        ]);
    }

    /**
     * @return null|Response
     * @throws \yii\base\Exception
     * @throws BadRequestHttpException
     */
    public function actionSave()
    {
        $this->requirePostRequest();
        $this->requireAdmin(false);

        $id = Craft::$app->request->getBodyParam('entryStatusId');
        $entryStatus = SproutBase::$app->formEntryStatuses->getEntryStatusById($id);

        $entryStatus->name = Craft::$app->request->getBodyParam('name');
        $entryStatus->handle = Craft::$app->request->getBodyParam('handle');
        $entryStatus->color = Craft::$app->request->getBodyParam('color');
        $entryStatus->isDefault = Craft::$app->request->getBodyParam('isDefault');

        if (empty($entryStatus->isDefault)) {
            $entryStatus->isDefault = 0;
        }

        if (!SproutBase::$app->formEntryStatuses->saveEntryStatus($entryStatus)) {
            Craft::$app->session->setError(Craft::t('sprout', 'Could not save Entry Status.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'entryStatus' => $entryStatus
            ]);

            return null;
        }

        Craft::$app->session->setNotice(Craft::t('sprout', 'Entry Status saved.'));

        return $this->redirectToPostedUrl();
    }

    /**
     * @return Response
     * @throws Exception
     * @throws BadRequestHttpException
     */
    public function actionReorder(): Response
    {
        $this->requirePostRequest();
        $this->requireAdmin(false);

        $ids = Json::decode(Craft::$app->request->getRequiredBodyParam('ids'));

        if ($success = SproutBase::$app->formEntryStatuses->reorderEntryStatuses($ids)) {
            return $this->asJson(['success' => $success]);
        }

        return $this->asJson(['error' => Craft::t('sprout', "Couldn't reorder Order Statuses.")]);
    }

    /**
     * @return Response
     * @throws Exception
     * @throws Throwable
     * @throws BadRequestHttpException
     */
    public function actionDelete(): Response
    {
        $this->requirePostRequest();
        $this->requireAdmin(false);

        $entryStatusId = Craft::$app->request->getRequiredBodyParam('id');

        if (!SproutBase::$app->formEntryStatuses->deleteEntryStatusById($entryStatusId)) {
            return $this->asJson(['success' => false]);
        }

        return $this->asJson(['success' => true]);
    }
}
