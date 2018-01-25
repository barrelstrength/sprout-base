<?php

namespace barrelstrength\sproutbase\contracts\sproutlists;

use craft\base\Component;

abstract class SproutListsBaseListType extends Component
{
    /**
     * Returns the class name of this List Type
     *
     * @return mixed
     */
    final public function getClassName()
    {
        $class = str_replace('Craft\\', '', get_class($this));

        return $class;
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

    abstract public function getListById($listId);
}