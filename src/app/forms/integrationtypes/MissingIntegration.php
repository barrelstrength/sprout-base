<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\integrationtypes;

use barrelstrength\sproutbase\app\forms\base\Integration;
use Craft;
use craft\base\MissingComponentInterface;
use craft\base\MissingComponentTrait;

class MissingIntegration extends Integration implements MissingComponentInterface
{
    use MissingComponentTrait;

    /**
     * @inheritDoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout', 'Missing Integration');
    }
}

