<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\base;

use craft\base\Model;
use craft\helpers\StringHelper;
use ReflectionClass;
use ReflectionException;

abstract class Settings extends Model implements SettingsInterface
{
    /**
     * @return string
     * @throws ReflectionException
     */
    public function getKey(): string
    {
        $class = new ReflectionClass($this);
        $baseName = preg_replace('/Settings$/', '', $class->getShortName());

        return StringHelper::toKebabCase($baseName);
    }

    public function getSettingsNavItem(): array
    {
        return [];
    }
}

