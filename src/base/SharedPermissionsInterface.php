<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\base;

interface SharedPermissionsInterface
{
    /**
     * @return array
     */
    public function getSharedPermissions(): array;

    /**
     * This is the main plugin to store the shared settings
     * @return string
     */
    public function getProjectConfigHandle(): string;
}