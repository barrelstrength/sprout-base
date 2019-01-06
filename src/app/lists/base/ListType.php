<?php

namespace barrelstrength\sproutbase\app\lists\base;

use barrelstrength\sproutlists\elements\SubscriberList;
use barrelstrength\sproutlists\elements\Subscriber;
use barrelstrength\sproutlists\models\Subscription;
use craft\base\Component;

/**
 *
 * @property mixed $className
 */
abstract class ListType extends Component
{
    /**
     * Returns the class name of this List Type
     *
     * @return mixed
     */
    final public function getClassName()
    {
        return str_replace('Craft\\', '', get_class($this));
    }

    /**
     * Subscribe a user to a list for this List Type
     *
     * @param Subscription $subscription
     *
     * @return bool
     */
    abstract public function subscribe(Subscription $subscription): bool;

    /**
     * Unsubscribe a user from a list for this List Type
     *
     * @param Subscription $subscription
     *
     * @return bool
     */
    abstract public function unsubscribe(Subscription $subscription): bool;

    /**
     * Check if a user is subscribed to a list
     *
     * @param Subscription $subscription
     *
     * @return bool
     */
    abstract public function isSubscribed(Subscription $subscription): bool;

    /**
     * Return all lists for a given subscriber.
     *
     * @param Subscriber $subscriber
     *
     * @return array
     */
    abstract public function getLists(Subscriber $subscriber): array;

    /**
     * Get subscribers on a given list.
     *
     * @param SubscriberList $list
     *
     * @return mixed
     * @internal param $criteria
     *
     */
    abstract public function getSubscribers(SubscriberList $list);

    /**
     * Return total subscriptions for a given subscriber.
     *
     * @param Subscriber $subscriber
     *
     * @return int
     */
    abstract public function getListCount(Subscriber $subscriber = null): int;

    /**
     * @param SubscriberList $list
     *
     * @return mixed
     */
    abstract public function getSubscriberCount(SubscriberList $list);

    /**
     * @param int $listId
     *
     * @return mixed
     */
    abstract public function getListById(int $listId);

    /**
     * @param SubscriberList $list
     *
     * @return mixed
     */
    abstract public function saveList(SubscriberList $list);
}