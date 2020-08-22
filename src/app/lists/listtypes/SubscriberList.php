<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\lists\listtypes;

use barrelstrength\sproutbase\app\lists\base\BaseSubscriberList;
use barrelstrength\sproutbase\app\lists\base\ListInterface;
use barrelstrength\sproutbase\app\lists\base\ListTrait;
use barrelstrength\sproutbase\app\lists\base\SubscriberInterface;
use barrelstrength\sproutbase\app\lists\base\SubscriptionInterface;
use barrelstrength\sproutbase\app\lists\elements\ListElement;
use barrelstrength\sproutbase\app\lists\elements\Subscriber;
use barrelstrength\sproutbase\app\lists\models\Subscription;
use barrelstrength\sproutbase\app\lists\records\Subscriber as SubscriberRecord;
use barrelstrength\sproutbase\app\lists\records\Subscription as SubscriptionRecord;
use Craft;
use craft\errors\ElementNotFoundException;
use craft\helpers\StringHelper;
use craft\helpers\Template;
use Throwable;
use Twig\Markup;
use yii\base\Exception;
use yii\web\BadRequestHttpException;

/**
 *
 * @property string $name
 * @property array $listsWithSubscribers
 * @property string $handle
 */
class SubscriberList extends BaseSubscriberList
{
    use ListTrait;

    /**
     * @var bool
     */
    public $requireEmailForSubscription = true;

    /**
     * @return string
     */
    public static function displayName(): string
    {
        return Craft::t('sprout', 'Subscriber List');
    }

    /**
     * Prepare the Subscription model for the `add` and `remove` methods
     *
     * @return SubscriptionInterface
     */
    public function populateSubscriptionFromPost(): SubscriptionInterface
    {
        $subscription = new Subscription();
        $subscription->listType = get_class($this);
        $subscription->listId = Craft::$app->getRequest()->getBodyParam('list.id');
        $subscription->elementId = Craft::$app->getRequest()->getBodyParam('list.elementId');
        $subscription->listHandle = Craft::$app->getRequest()->getBodyParam('list.handle');
        $subscription->email = Craft::$app->getRequest()->getBodyParam('subscription.email');
        $subscription->firstName = Craft::$app->getRequest()->getBodyParam('subscription.firstName');
        $subscription->lastName = Craft::$app->getRequest()->getBodyParam('subscription.lastName');

        return $subscription;
    }

    /**
     * Prepare the ListElement for the `saveList` method
     *
     * @return ListInterface
     * @throws BadRequestHttpException
     */
    public function populateListFromPost(): ListInterface
    {
        $listId = Craft::$app->getRequest()->getBodyParam('listId');

        if ($listId) {
            $list = Craft::$app->elements->getElementById($listId);
        } else {
            $list = new ListElement();
        }

        $list->type = get_class($this);
        $list->elementId = Craft::$app->getRequest()->getBodyParam('elementId');
        $list->name = Craft::$app->request->getRequiredBodyParam('name');
        $list->handle = Craft::$app->request->getBodyParam('handle');

        if ($list->handle === null) {
            $list->handle = StringHelper::toCamelCase($list->name);
        }

        return $list;
    }

    /**
     * Get a Subscriber Element based on a subscription
     *
     * @param SubscriptionInterface $subscription
     *
     * @return SubscriptionInterface|Subscriber|null
     */
    public function getSubscriberOrItem(SubscriptionInterface $subscription)
    {
        /** @var Subscription $subscription */
        $subscriberId = $subscription->itemId;

        $query = Subscriber::find();

        if ($subscription->email) {
            $query->andWhere([
                'sprout_subscribers.email' => $subscription->email,
            ]);
        } else {
            $query->andWhere([
                'sprout_subscribers.id' => $subscriberId,
            ]);
            $query->orWhere([
                'sprout_subscribers.userId' => $subscriberId,
            ]);
        }

        /** @var Subscriber $subscriber */
        $subscriber = $query->one();

        // Only assign profile values when we add a Subscriber if we have values
        // Don't overwrite any profile attributes with empty values
        if (!empty($subscription->firstName)) {
            $subscriber->firstName = $subscription->firstName;
        }

        if (!empty($subscription->lastName)) {
            $subscriber->lastName = $subscription->lastName;
        }

        return $subscriber;
    }

    /**
     * @return SubscriberInterface
     */
    public function populateSubscriberFromPost(): SubscriberInterface
    {
        $subscriberId = Craft::$app->getRequest()->getBodyParam('subscriberId');

        if ($subscriberId) {
            $subscriber = Craft::$app->elements->getElementById($subscriberId);
        } else {
            $subscriber = new Subscriber();
        }

        $subscriber->email = Craft::$app->getRequest()->getBodyParam('email');
        $subscriber->firstName = Craft::$app->getRequest()->getBodyParam('firstName');
        $subscriber->lastName = Craft::$app->getRequest()->getBodyParam('lastName');
        $subscriber->listElements = Craft::$app->getRequest()->getBodyParam('subscriberList.listElements');

        return $subscriber;
    }

    /**
     * Gets the HTML output for the lists sidebar on the Subscriber edit page.
     *
     * @param $subscriberId
     *
     * @return string|Markup
     * @throws Exception
     * @throws Throwable
     */
    public function getSubscriberSettingsHtml($subscriberId)
    {
        $subscriber = null;
        $listIds = [];

        if ($subscriberId !== null) {
            $subscription = new Subscription();
            $subscription->itemId = $subscriberId;

            $subscriber = $this->getSubscriberOrItem($subscription);

            if ($subscriber) {
                $listIds = $subscriber->getLists()->column();
            }
        }

        /** @var ListElement[] $lists */
        $lists = ListElement::find()->where([
            'sprout_lists.type' => __CLASS__,
        ])->all();

        $options = [];

        if (count($lists)) {
            foreach ($lists as $list) {
                $options[] = [
                    'label' => sprintf('%s', $list->name),
                    'value' => $list->id,
                ];
            }
        }

        // Return a blank template if we have no lists
        if (empty($options)) {
            return '';
        }

        $html = Craft::$app->getView()->renderTemplate('sprout/lists/subscribers/_subscriberlists', [
            'options' => $options,
            'values' => $listIds,
        ]);

        return Template::raw($html);
    }

    /**
     * Saves a subscriber
     *
     * @param SubscriberInterface $subscriber
     *
     * @return bool
     * @throws Throwable
     * @throws ElementNotFoundException
     * @throws Exception
     */
    public function saveSubscriber(SubscriberInterface $subscriber): bool
    {
        /** @var Subscriber $subscriber */
        $subscriber = $this->updateSubscriberForUserSync($subscriber);

        if (Craft::$app->getElements()->saveElement($subscriber)) {
            $this->updateCount();

            return true;
        }

        return false;
    }

    /**
     * Deletes a subscriber.
     *
     * @param SubscriberInterface|Subscriber $subscriber
     *
     * @return bool
     * @throws ElementNotFoundException
     * @throws Throwable
     */
    public function deleteSubscriber(SubscriberInterface $subscriber): bool
    {
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            Craft::$app->getElements()->deleteElementById($subscriber->id);

            // Clean up everything else that relates to this subscriber
            SubscriberRecord::deleteAll('[[id]] = :subscriberId', [
                ':subscriberId' => $subscriber->id,
            ]);
            SubscriptionRecord::deleteAll('[[listId]] = :listId', [
                ':listId' => $subscriber->id,
            ]);

            $this->updateCount();

            $transaction->commit();

            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw new ElementNotFoundException(Craft::t('sprout', 'Unable to delete Subscriber.'));
        }
    }

    /**
     * If enable user sync is on look for user element and assign it to userId column
     *
     * @param SubscriberInterface|Subscriber $subscriber
     *
     * @return SubscriberInterface $subscriber
     */
    public function updateSubscriberForUserSync(SubscriberInterface $subscriber): SubscriberInterface
    {
        if (!$this->settings->enableUserSync) {
            $subscriber->userId = null;

            return $subscriber;
        }

        $user = null;

        if ($subscriber->email) {
            $user = Craft::$app->getUsers()->getUserByUsernameOrEmail($subscriber->email);
        }

        $subscriber->userId = $user->id ?? null;

        // Assign First and Last name again and values from user profile as fallbacks
        $subscriber->firstName = $subscriber->firstName ?? $user->firstName ?? null;
        $subscriber->lastName = $subscriber->lastName ?? $user->lastName ?? null;

        return $subscriber;
    }
}
