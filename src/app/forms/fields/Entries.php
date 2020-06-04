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

/**
 * Entries represents an Entries field.
 */
class Entries extends BaseRelationField
{

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout', 'Entries (Sprout Forms)');
    }

    /**
     * @inheritdoc
     */
    public static function defaultSelectionLabel(): string
    {
        return Craft::t('sprout', 'Add an entry');
    }

    /**
     * @inheritdoc
     */
    protected static function elementType(): string
    {
        return EntryElement::class;
    }
}
