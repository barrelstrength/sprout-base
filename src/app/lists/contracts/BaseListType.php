<?php

namespace barrelstrength\sproutbase\app\lists\contracts;

use barrelstrength\sproutlists\elements\Lists;
use craft\base\Component;

abstract class BaseListType extends Component
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
     * @param $subscription
     *
     * @return mixed
     */
    abstract public function subscribe($subscription);

    /**
     * Unsubscribe a user from a list for this List Type
     *
     * @param $subscription
     *
     * @return mixed
     */
    abstract public function unsubscribe($subscription);

    /**
     * Check if a user is subscribed to a list
     *
     * @param $subscription
     *
     * @return mixed
     */
    abstract public function isSubscribed($subscription);

    /**
     * Return all lists for a given subscriber.
     *
     * @param $subscriber
     *
     * @return mixed
     */
    abstract public function getLists($subscriber);

    /**
     * Get subscribers on a given list.
     *
     * @param $list
     *
     * @return mixed
     * @internal param $criteria
     *
     */
    abstract public function getSubscribers($list);

    /**
     * Return total subscriptions for a given subscriber.
     *
     * @param null $subscriber
     *
     * @return mixed
     */
    abstract public function getListCount($subscriber = null);

    /**
     * @param $list
     *
     * @return mixed
     */
    abstract public function getSubscriberCount($list);

    /**
     * @param $listId
     *
     * @return mixed
     */
    abstract public function getListById($listId);

    /**
     * @param Lists $list
     *
     * @return mixed
     */
    abstract public function saveList(Lists $list);
}