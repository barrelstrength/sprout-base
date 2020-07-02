<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\lists\controllers;

use barrelstrength\sproutbase\app\lists\elements\ListElement;
use barrelstrength\sproutbase\app\lists\models\Subscription;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use Throwable;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class ListsController extends Controller
{
    /**
     * Allow users who are not logged in to subscribe and unsubscribe from lists
     *
     * @var array
     */
    protected $allowAnonymous = [
        'add',
        'remove',
    ];

    /**
     * @return Response
     */
    public function actionListsIndexTemplate(): Response
    {
        return $this->renderTemplate('sprout/lists/lists/index');
    }

    /**
     * Prepare variables for the List Edit Template
     *
     * @param null $listId
     * @param null $list
     *
     * @return Response
     * @throws ForbiddenHttpException
     */
    public function actionListEditTemplate($listId = null, $list = null): Response
    {
        $this->requirePermission('sprout:lists:editLists');

        $continueEditingUrl = null;

        if (!$list) {
            if ($listId !== null) {
                /** @var ListElement $list */
                $list = Craft::$app->elements->getElementById($listId, ListElement::class);
                $continueEditingUrl = 'sprout/lists/edit/'.$list->id;
            } else {
                $list = new ListElement();
            }
        }

        $redirectUrl = UrlHelper::cpUrl('sprout/lists');

        return $this->renderTemplate('sprout/lists/lists/_edit', [
            'list' => $list,
            'redirectUrl' => $redirectUrl,
            'continueEditingUrl' => $continueEditingUrl,
        ]);
    }

    /**
     * Saves a list
     *
     * @return null
     * @throws \Exception
     * @throws BadRequestHttpException
     */
    public function actionSaveList()
    {
        $this->requirePostRequest();
        $this->requirePermission('sprout:lists:editLists');

        $listType = Craft::$app->getRequest()->getBodyParam('listType');
        $listType = SproutBase::$app->lists->getListType($listType);

        $list = $listType->populateListFromPost();

        if (!$listType->saveList($list)) {
            Craft::$app->getSession()->setError(Craft::t('sprout', 'Unable to save list.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'list' => $list,
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout', 'List saved.'));

        return $this->redirectToPostedUrl();
    }

    /**
     * Deletes a list
     *
     * @return Response
     * @throws \Exception
     * @throws Throwable
     * @throws BadRequestHttpException
     */
    public function actionDeleteList(): Response
    {
        $this->requirePostRequest();
        $this->requirePermission('sprout:lists:editLists');

        $list = new ListElement();
        $list->type = Craft::$app->getRequest()->getRequiredBodyParam('listType');
        $list->id = Craft::$app->getRequest()->getRequiredBodyParam('listId');

        $listType = SproutBase::$app->lists->getListType($list->type);

        if (!$listType->deleteList($list)) {
            if (Craft::$app->getRequest()->getIsAjax()) {
                return $this->asJson([
                    'success' => false,
                ]);
            }

            Craft::$app->getSession()->setError(Craft::t('sprout', 'Unable to delete list.'));

            return $this->redirectToPostedUrl();
        }

        if (Craft::$app->getRequest()->getIsAjax()) {
            return $this->asJson([
                'success' => true,
            ]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout', 'List deleted.'));

        return $this->redirectToPostedUrl();
    }

    /**
     * Adds a subscriber to a list
     *
     * @return Response | null
     * @throws Throwable
     * @throws Exception
     * @throws BadRequestHttpException
     */
    public function actionAdd()
    {
        $this->requirePostRequest();

        $listType = Craft::$app->getRequest()->getBodyParam('list.type');
        $listType = SproutBase::$app->lists->getListType($listType);

        /** @var Subscription $subscription */
        $subscription = $listType->populateSubscriptionFromPost();

        if (!$listType->add($subscription)) {

            if (Craft::$app->getRequest()->getIsAjax()) {
                return $this->asJson([
                    'success' => false,
                    'errors' => $subscription->getErrors(),
                ]);
            }

            Craft::$app->getUrlManager()->setRouteParams([
                'subscription' => $subscription,
            ]);

            return null;
        }

        if (Craft::$app->getRequest()->getIsAjax()) {
            return $this->asJson([
                'success' => true,
            ]);
        }

        return $this->redirectToPostedUrl();
    }

    /**
     * Removes a subscriber from a list
     *
     * @return Response|null
     * @throws Exception
     * @throws BadRequestHttpException
     */
    public function actionRemove()
    {
        $this->requirePostRequest();

        $listType = Craft::$app->getRequest()->getBodyParam('list.type');
        $listType = SproutBase::$app->lists->getListType($listType);

        /** @var Subscription $subscription */
        $subscription = $listType->populateSubscriptionFromPost();

        if (!$listType->remove($subscription)) {
            if (Craft::$app->getRequest()->getIsAjax()) {
                return $this->asJson([
                    'success' => false,
                    'errors' => $subscription->getErrors(),
                ]);
            }

            Craft::$app->getUrlManager()->setRouteParams([
                'subscription' => $subscription,
            ]);

            return null;
        }

        if (Craft::$app->getRequest()->getIsAjax()) {
            return $this->asJson([
                'success' => true,
            ]);
        }

        return $this->redirectToPostedUrl();
    }
}