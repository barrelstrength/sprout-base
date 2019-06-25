<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\base;

interface SproutEditionsInterface
{
    /**
     * Return a CP Url to use for the "Upgrade to Pro" button on CP page headers
     *
     * @return string|null
     */
    public function getUpgradeUrl();
}