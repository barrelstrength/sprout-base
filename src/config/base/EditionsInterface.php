<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\base;

interface EditionsInterface
{
    /**
     * Return a CP Url to use for the "Upgrade to Pro" button on CP page headers
     *
     * @return string|null
     */
    public function getUpgradeUrl();
}