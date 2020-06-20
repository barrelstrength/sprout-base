<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\fields;

use barrelstrength\sproutbase\app\forms\elements\Entry as EntryElement;
use Craft;
use craft\fields\BaseRelationField;

class Entries extends BaseRelationField
{
    public static function displayName(): string
    {
        return Craft::t('sprout', 'Entries (Sprout Forms)');
    }

    public static function defaultSelectionLabel(): string
    {
        return Craft::t('sprout', 'Add an entry');
    }

    protected static function elementType(): string
    {
        return EntryElement::class;
    }
}
