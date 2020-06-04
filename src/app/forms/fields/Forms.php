<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\fields;

use barrelstrength\sproutbase\app\forms\elements\db\FormQuery;
use barrelstrength\sproutbase\app\forms\elements\Form as FormElement;
use Craft;
use craft\fields\BaseRelationField;

/**
 * Forms represents a Forms field.
 */
class Forms extends BaseRelationField
{
    /**
     * @inheritDoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout', 'Forms (Sprout Forms)');
    }

    /**
     * @inheritDoc
     */
    public static function defaultSelectionLabel(): string
    {
        return Craft::t('sprout', 'Add a form');
    }

    /**
     * @inheritDoc
     */
    public static function valueType(): string
    {
        return FormQuery::class;
    }

    /**
     * @inheritDoc
     */
    protected static function elementType(): string
    {
        return FormElement::class;
    }
}
