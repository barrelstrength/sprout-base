<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\lists\controllers;

use barrelstrength\sproutbase\app\lists\base\BaseSubscriberList;
use barrelstrength\sproutbase\app\lists\elements\Subscriber;
use barrelstrength\sproutbase\app\lists\listtypes\SubscriberList;
use barrelstrength\sproutbase\app\lists\models\Subscription;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\errors\MissingComponentException;
use craft\web\Controller;
use Exception;
use Throwable;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class SubscribersController extends Controller
{
    /**
     * @return Response
     */
    public function actionSubscribersIndexTemplate(): Response
    {
        return $this->renderTemplate('sprout/lists/subscribers/index');
    }

    /**
     * Prepare variables for Subscriber Edit Template
     *
     * @param null $id
     * @param null $subscriber
     *
     * @return Response
     * @throws Exception
     * @throws Throwable
     */
    public function actionEditSubscriberTemplate($id = null, $subscriber = null): Response
    {
        $this->requirePermission('sprout:lists:editSubscribers');

        /**  @var SubscriberList $listType */
        $listType = SproutBase::$app->lists->getListType(SubscriberList::class);
        $listTypes[] = $listType;

        if ($id !== null && $subscriber === null) {
            $subscription = new Subscription();
            $subscription->itemId = $id;

            $subscriber = $listType->getSubscriberOrItem($subscription);
        }

        return $this->renderTemplate('sprout/lists/subscribers/_edit', [
            'subscriber' => $subscriber,
            'listTypes' => $listTypes,
        ]);
    }

    /**
     * Saves a subscriber
     *
     * @return Response|null
     * @throws MissingComponentException
     * @throws \yii\base\Exception
     * @throws BadRequestHttpException
     */
    public function actionSaveSubscriber()
    {
        $this->requirePostRequest();
        $this->requirePermission('sprout:lists:editSubscribers');

        /** @var BaseSubscriberList $listType */
        $listType = Craft::$app->getRequest()->getBodyParam('listType');
        $listType = SproutBase::$app->lists->getListType($listType);

        $subscriber = $listType->populateSubscriberFromPost();

        if (!$listType->saveSubscriber($subscriber)) {
            Craft::$app->getSession()->setError(Craft::t('sprout', 'Unable to save subscriber.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'subscriber' => $subscriber,
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout', 'Subscriber saved.'));

        return $this->redirectToPostedUrl($subscriber);
    }

    /**
     * Deletes a subscriber
     *
     * @return Response
     * @throws Exception
     * @throws BadRequestHttpException
     */
    public function actionDeleteSubscriber(): Response
    {
        $this->requirePostRequest();
        $this->requirePermission('sprout:lists:editSubscribers');

        $subscriber = new Subscriber();
        $subscriber->listType = Craft::$app->getRequest()->getRequiredBodyParam('listType');
        $subscriber->id = Craft::$app->getRequest()->getBodyParam('subscriberId');

        /** @var BaseSubscriberList $listType */
        $listType = SproutBase::$app->lists->getListType($subscriber->listType);

        if (!$listType->deleteSubscriber($subscriber)) {
            Craft::$app->getSession()->setError(Craft::t('sprout', 'Unable to delete subscriber.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'subscriber' => $subscriber,
            ]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout', 'Subscriber deleted.'));

        return $this->redirectToPostedUrl();
    }
}