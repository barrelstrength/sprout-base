<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\lists\base;

interface SubscriberInterface
{
    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return string|null
     */
    public function getEmail();
}