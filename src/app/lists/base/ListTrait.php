<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\lists\base;

use barrelstrength\sproutbase\app\lists\elements\ListElement;
use barrelstrength\sproutbase\app\lists\elements\Subscriber;
use barrelstrength\sproutbase\app\lists\models\Subscription;
use barrelstrength\sproutbase\app\lists\records\ListElement as ListElementRecord;
use barrelstrength\sproutbase\app\lists\records\Subscription as SubscriptionRecord;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Element;
use craft\errors\ElementNotFoundException;
use Exception;
use Throwable;
use yii\db\StaleObjectException;

/**
 * Trait ListTrait
 *
 * @package barrelstrength\sproutbase\app\lists\base
 */
trait ListTrait
{
    /**
     * @param SubscriptionInterface|Subscription $subscription
     *
     * @return bool
     * @throws Throwable
     */
    public function add(SubscriptionInterface $subscription): bool
    {
        if ($this->requireEmailForSubscription === true) {
            $subscription->setScenario(Subscription::SCENARIO_SUBSCRIBER);
        }

        if (!$subscription->validate()) {
            return false;
        }

        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            /** @var Element $item */
            $item = $this->getSubscriberOrItem($subscription);

            // If our Subscriber doesn't exist, create a Subscriber Element
            if ($item === null) {
                $item = new Subscriber();
                $item->userId = $subscription->itemId;
                $item->email = $subscription->email;
                $item->firstName = $subscription->firstName ?? null;
                $item->lastName = $subscription->lastName ?? null;
            }

            // Save or resave the subscriber. Make sure we have an ID and run User Sync.
            $this->saveSubscriber($item);
            $subscription->itemId = $item->id;

            $list = $this->getList($subscription);

            // If our List doesn't exist, create a List Element
            if ($list === null) {
                $list = new ListElement();

                if ($this->settings->enableAutoList) {
                    $list->type = __CLASS__;
                    $list->elementId = $subscription->elementId;
                    $list->name = $subscription->listHandle ?? 'list:'.$subscription->listId;
                    $list->handle = $subscription->listHandle ?? 'list:'.$subscription->listId;

                    $this->saveList($list);
                    $subscription->listId = $list->id;
                } else {
                    $subscription->addErrors([
                        'listId' => [
                            Craft::t('sprout', 'List does not exist.'),
                            Craft::t('sprout', 'User not permitted to create List.'),
                        ],
                    ]);

                    return false;
                }
            }

            if (!$item->validate() || !$list->validate()) {
                $subscription->addErrors($item->getErrors());
                $subscription->addErrors($list->getErrors());

                return false;
            }

            $subscriptionRecord = new SubscriptionRecord();
            $subscriptionRecord->listId = $list->id;
            $subscriptionRecord->itemId = $item->id;

            if ($subscriptionRecord->save()) {
                $this->updateCount($subscriptionRecord->listId);
            } else {
                Craft::warning('List Item '.$item->id.' already exists on List ID '.$list->id.'.', __METHOD__);
            }

            $transaction->commit();

            return true;
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @param SubscriptionInterface $subscription
     *
     * @return bool
     */
    public function remove(SubscriptionInterface $subscription): bool
    {
        $list = $this->getList($subscription);

        if (!$list) {
            return false;
        }

        $item = $this->getSubscriberOrItem($subscription);

        if (!$item) {
            return false;
        }

        // Delete the subscription that matches the List and Subscriber IDs
        $subscriptions = SubscriptionRecord::deleteAll([
            '[[listId]]' => $list->id,
            '[[itemId]]' => $item->id,
        ]);

        if ($subscriptions !== null) {
            $this->updateCount();

            return true;
        }

        return false;
    }

    // ListElement
    // =========================================================================

    /**
     * @param SubscriptionInterface|Subscription $subscription
     *
     * @return ListElement|null
     */
    public function getList(SubscriptionInterface $subscription)
    {
        $query = ListElement::find()
            ->where([
                'sproutlists_lists.type' => $subscription->listType,
            ]);

        if ($subscription->listId) {
            $query->andWhere([
                'sproutlists_lists.id' => $subscription->listId,
            ]);

            /** @noinspection PhpIncompatibleReturnTypeInspection */
            return $query->one();
        }

        if ($subscription->elementId && $subscription->listHandle) {
            $query->andWhere([
                'and',
                ['sproutlists_lists.elementId' => $subscription->elementId],
                ['sproutlists_lists.handle' => $subscription->listHandle],
            ]);
        } else {
            // Give the user what we can, but this result may not be unique in all cases
            $query->andWhere([
                'or',
                ['sproutlists_lists.elementId' => $subscription->elementId],
                ['sproutlists_lists.handle' => $subscription->listHandle],
            ]);
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $query->one();
    }

    /**
     * Get all Lists for a given List Type
     *
     * @return ListElement[]
     */
    public function getLists(): array
    {
        return ListElement::find()
            ->where([
                'sproutlists_lists.type' => get_class($this),
            ])->all();
    }

    /**
     * Saves a list.
     *
     * @param ListInterface|ListElement $list
     *
     * @return bool
     * @throws Throwable
     * @throws ElementNotFoundException
     * @throws \yii\base\Exception
     */
    public function saveList(ListInterface $list): bool
    {
        /** @var ListElement $list */
        return Craft::$app->elements->saveElement($list);
    }

    /**
     * Deletes a list.
     *
     * @param ListInterface|ListElement $list
     *
     * @return bool
     * @throws Exception
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function deleteList(ListInterface $list): bool
    {
        $listRecord = ListElementRecord::findOne($list->id);

        if ($listRecord === null) {
            return false;
        }

        if ($listRecord && $listRecord->delete()) {
            $subscriptions = SubscriptionRecord::find()->where([
                'listId' => $list->id,
            ]);

            if ($subscriptions != null) {
                SubscriptionRecord::deleteAll('[[listId]] = :listId', [
                    ':listId' => $list->id,
                ]);
            }

            return true;
        }

        return false;
    }

    /**
     * @param ListInterface|ListElement $list
     *
     * @return array|mixed
     * @throws Exception
     */
    public function getSubscriptions(ListInterface $list)
    {
        return SubscriptionRecord::find()
            ->where(['listId' => $list->id])
            ->all();
    }

    /**
     * @param array $criteria
     *
     * @return SubscriptionInterface
     */
    public function populateSubscriptionFromCriteria(array $criteria = []): SubscriptionInterface
    {
        $subscription = new Subscription();
        $subscription->listType = get_class($this);
        $subscription->listId = $criteria['listId'] ?? null;
        $subscription->listHandle = $criteria['listHandle'] ?? null;
        $subscription->itemId = $criteria['itemId'] ?? null;
        $subscription->email = $criteria['email'] ?? null;

        return $subscription;
    }

    /**=
     * @param SubscriptionInterface $subscription
     *
     * @return bool
     */
    public function isSubscribed(SubscriptionInterface $subscription): bool
    {
        $list = $this->getList($subscription);

        // If we don't find a matching list, no subscription exists
        if ($list === null) {
            return false;
        }

        // Make sure we set all the values we can
        if (!empty($subscription->listId)) {
            $subscription->listId = $list->id;
        }

        if (!empty($subscription->listHandle)) {
            $subscription->listHandle = $list->handle;
        }

        $item = $this->getSubscriberOrItem($subscription);

        if ($item === null) {
            return false;
        }

        return SubscriptionRecord::find()->where([
            'listId' => $list->id,
            'itemId' => $item->id,
        ])->exists();
    }

    /**
     * @param ListInterface $list
     *
     * @return int
     * @throws Exception
     */
    public function getCount(ListInterface $list): int
    {
        $items = $this->getSubscriptions($list);

        return count($items);
    }

    /**
     * Updates the count column in the db
     *
     * @param null $listId
     *
     * @return bool
     * @todo - delegate this to the queue
     *
     */
    public function updateCount($listId = null): bool
    {
        if ($listId === null) {
            $lists = ListElement::find()->all();
        } else {
            $list = ListElement::findOne($listId);

            $lists = [$list];
        }

        if (!count($lists)) {
            return false;
        }

        /** @var ListElement[] $lists */
        foreach ($lists as $list) {

            if (!$list) {
                continue;
            }

            $listType = SproutBase::$app->lists->getListTypeById($list->id);

            $count = $listType->getCount($list);
            $list->count = $count;

            $listType->saveList($list);
        }

        return true;
    }
}
