<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\lists\base;

interface SubscriptionInterface
{
    const SCENARIO_SUBSCRIBER = 'subscriber';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return ListType
     */
    public function getListType(): ListType;
}