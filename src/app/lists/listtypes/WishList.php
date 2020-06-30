<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\lists\listtypes;

use barrelstrength\sproutbase\app\lists\base\ListInterface;
use barrelstrength\sproutbase\app\lists\base\ListTrait;
use barrelstrength\sproutbase\app\lists\base\ListType;
use barrelstrength\sproutbase\app\lists\base\SubscriptionInterface;
use barrelstrength\sproutbase\app\lists\elements\ListElement;
use barrelstrength\sproutbase\app\lists\models\Subscription;
use Craft;
use craft\base\Element;
use craft\helpers\StringHelper;
use yii\web\BadRequestHttpException;

/**
 *
 * @property string $name
 * @property array  $listsWithSubscribers
 * @property string $handle
 */
class WishList extends ListType
{
    use ListTrait;

    /**
     * @return string
     */
    public static function displayName(): string
    {
        return Craft::t('sprout', 'Wish List');
    }

    /**
     * Prepare the Subscription model for the `add` and `remove` methods
     *
     * @return SubscriptionInterface
     */
    public function populateSubscriptionFromPost(): SubscriptionInterface
    {
        $currentUser = Craft::$app->getUser()->getIdentity();
        $currentUserId = $currentUser->id ?? null;

        $subscription = new Subscription();
        $subscription->listType = get_class($this);
        $subscription->listId = Craft::$app->getRequest()->getBodyParam('list.id');
        $subscription->elementId = Craft::$app->getRequest()->getBodyParam('list.elementId', $currentUserId);
        $subscription->listHandle = Craft::$app->getRequest()->getBodyParam('list.handle');
        $subscription->itemId = Craft::$app->getRequest()->getBodyParam('subscription.itemId');

        return $subscription;
    }

    /**
     * Prepare the ListElement for the `saveList` method
     *
     * @return ListElement
     * @throws BadRequestHttpException
     */
    public function populateListFromPost(): ListInterface
    {
        $currentUser = Craft::$app->getUser()->getIdentity();
        $currentUserId = $currentUser->id ?? null;

        $list = new ListElement();
        $list->type = get_class($this);
        $list->id = Craft::$app->getRequest()->getBodyParam('listId');
        $list->elementId = Craft::$app->getRequest()->getBodyParam('elementId', $currentUserId);
        $list->name = Craft::$app->request->getRequiredBodyParam('name');
        $list->handle = Craft::$app->request->getBodyParam('handle');

        if ($list->handle === null) {
            $list->handle = StringHelper::toCamelCase($list->name);
        }

        return $list;
    }

    /**
     * @param SubscriptionInterface|Subscription $subscription
     *
     * @return Element|null
     */
    public function getSubscriberOrItem(SubscriptionInterface $subscription)
    {
        if (is_numeric($subscription->itemId)) {
            /** @var Element $element */
            $element = Craft::$app->elements->getElementById($subscription->itemId);

            if ($element === null) {
                Craft::warning('Unable to find an Element with ID: '.$subscription->listId, __METHOD__);

                return null;
            }

            return $element;
        }

        return null;
    }
}
