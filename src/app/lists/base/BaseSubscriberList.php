<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\lists\base;

abstract class BaseSubscriberList extends ListType
{
    /**
     * Prepare Subscriber for the `saveSubscriber` method
     *
     * @return SubscriberInterface
     */
    abstract public function populateSubscriberFromPost(): SubscriberInterface;

    /**
     * @param $subscriberId
     *
     * @return mixed
     * @todo - review if this works in the abstract sense
     *
     */
    abstract public function getSubscriberSettingsHtml($subscriberId);

    /**
     * @param SubscriberInterface $subscriber
     *
     * @return bool
     */
    abstract public function saveSubscriber(SubscriberInterface $subscriber): bool;

    /**
     * @param SubscriberInterface $subscriber
     *
     * @return bool
     */
    abstract public function deleteSubscriber(SubscriberInterface $subscriber): bool;
}