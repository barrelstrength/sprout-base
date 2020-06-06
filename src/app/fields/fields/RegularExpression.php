<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\fields;

use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;

/**
 *
 * @property array $elementValidationRules
 * @property mixed $settingsHtml
 */
class RegularExpression extends Field implements PreviewableFieldInterface
{
    /**
     * @var string
     */
    public $customPatternErrorMessage;

    /**
     * @var string
     */
    public $customPattern;

    /**
     * @var string
     */
    public $placeholder;

    /**
     * @return string
     */
    public static function displayName(): string
    {
        return Craft::t('sprout', 'Regular Expression (Sprout Fields)');
    }

    /**
     * @inher
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function getSettingsHtml()
    {
        return SproutBase::$app->regularExpressionField->getSettingsHtml($this);
    }

    /**
     * @inheritdoc
     *
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return string
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        return SproutBase::$app->regularExpressionField->getInputHtml($this, $value, $element);
    }

    /**
     * @inheritdoc
     */
    public function getElementValidationRules(): array
    {
        $rules = parent::getElementValidationRules();
        $rules[] = 'validateRegularExpression';

        return $rules;
    }

    /**
     * Validates our fields submitted value beyond the checks
     * that were assumed based on the content attribute.
     *
     *
     * @param Element|ElementInterface $element
     *
     * @return void
     */
    public function validateRegularExpression(ElementInterface $element)
    {
        $value = $element->getFieldValue($this->handle);

        if (!SproutBase::$app->regularExpressionField->validate($value, $this)) {
            $message = SproutBase::$app->regularExpressionField->getErrorMessage($this);
            $element->addError($this->handle, $message);
        }
    }

    /**
     * @inheritdoc
     */
    public function getTableAttributeHtml($value, ElementInterface $element): string
    {
        return $value;
    }
}