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

class Forms extends BaseRelationField
{
    public static function displayName(): string
    {
        return Craft::t('sprout', 'Forms (Sprout Forms)');
    }

    public static function defaultSelectionLabel(): string
    {
        return Craft::t('sprout', 'Add a form');
    }

    public static function valueType(): string
    {
        return FormQuery::class;
    }

    protected static function elementType(): string
    {
        return FormElement::class;
    }
}
