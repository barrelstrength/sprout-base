<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\lists\web\twig\variables;

use barrelstrength\sproutbase\app\lists\elements\db\ListElementQuery;
use barrelstrength\sproutbase\app\lists\elements\db\SubscriberQuery;
use barrelstrength\sproutbase\app\lists\elements\ListElement;
use barrelstrength\sproutbase\app\lists\elements\Subscriber;
use Craft;

class ListsVariable
{
    /**
     * @param array $criteria
     *
     * @return ListElementQuery
     */
    public function lists(array $criteria = []): ListElementQuery
    {
        $query = ListElement::find();
        Craft::configure($query, $criteria);

        return $query;
    }

    /**
     * @param array $criteria
     *
     * @return SubscriberQuery
     */
    public function subscribers(array $criteria = []): SubscriberQuery
    {
        $query = Subscriber::find();
        Craft::configure($query, $criteria);

        return $query;
    }
}
