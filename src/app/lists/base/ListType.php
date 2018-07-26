<?php

namespace barrelstrength\sproutbase\app\lists\base;

use barrelstrength\sproutlists\elements\Lists;
use barrelstrength\sproutlists\elements\Subscribers;
use barrelstrength\sproutlists\models\Subscription;
use craft\base\Component;

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
     * @return mixed
     */
    abstract public function subscribe(Subscription $subscription);

    /**
     * Unsubscribe a user from a list for this List Type
     *
     * @param Subscription $subscription
     *
     * @return mixed
     */
    abstract public function unsubscribe(Subscription $subscription);

    /**
     * Check if a user is subscribed to a list
     *
     * @param Subscription $subscription
     *
     * @return mixed
     */
    abstract public function isSubscribed(Subscription $subscription);

    /**
     * Return all lists for a given subscriber.
     *
     * @param Subscribers $subscriber
     *
     * @return mixed
     */
    abstract public function getLists(Subscribers $subscriber);

    /**
     * Get subscribers on a given list.
     *
     * @param $list
     *
     * @return mixed
     * @internal param $criteria
     *
     */
    abstract public function getSubscribers(Lists $list);

    /**
     * Return total subscriptions for a given subscriber.
     *
     * @param Subscribers $subscriber
     *
     * @return mixed
     */
    abstract public function getListCount(Subscribers $subscriber = null);

    /**
     * @param $list
     *
     * @return mixed
     */
    abstract public function getSubscriberCount(Lists $list);

    /**
     * @param int $listId
     *
     * @return mixed
     */
    abstract public function getListById(int $listId);

    /**
     * @param Lists $list
     *
     * @return mixed
     */
    abstract public function saveList(Lists $list);
}