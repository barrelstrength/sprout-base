<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\fields\formfields;

use barrelstrength\sproutbase\app\forms\base\FormField;
use barrelstrength\sproutbase\app\forms\elements\Entry;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use craft\fields\PlainText as CraftPlainText;
use craft\helpers\Template as TemplateHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Markup;
use yii\base\Exception;
use yii\base\InvalidConfigException;


/**
 *
 * @property array  $elementValidationRules
 * @property string $svgIconPath
 * @property mixed  $settingsHtml
 * @property array  $compatibleCraftFieldTypes
 * @property mixed  $exampleInputHtml
 */
class RegularExpression extends FormField implements PreviewableFieldInterface
{
    /**
     * @var string
     */
    public $cssClasses;

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

    public static function displayName(): string
    {
        return Craft::t('sprout', 'Regex');
    }

    /**
     * @return string
     */
    public function getSvgIconPath(): string
    {
        return '@sproutbaseicons/puzzle-piece.svg';
    }

    /**
     * @return string|null
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getSettingsHtml()
    {
        return SproutBase::$app->regularExpressionField->getSettingsHtml($this);
    }

    /**
     * @param mixed                 $value
     * @param ElementInterface|null $element
     *
     * @return string
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws InvalidConfigException
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        return SproutBase::$app->regularExpressionField->getInputHtml($this, $value, $element);
    }

    /**
     * @inheritdoc
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getExampleInputHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('sprout/forms/_components/fields/formfields/regularexpression/example',
            [
                'field' => $this
            ]
        );
    }

    /**
     * @param mixed      $value
     * @param Entry      $entry
     * @param array|null $renderingOptions
     *
     * @return Markup
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getFrontEndInputHtml($value, Entry $entry, array $renderingOptions = null): Markup
    {
        $placeholder = $this->placeholder ?? '';

        $pattern = $this->customPattern;

        $rendered = Craft::$app->getView()->renderTemplate('regularexpression/input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'entry' => $entry,
                'pattern' => $pattern,
                'errorMessage' => $this->customPatternErrorMessage,
                'renderingOptions' => $renderingOptions,
                'placeholder' => $placeholder
            ]
        );

        return TemplateHelper::raw($rendered);
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
     * @param Element|ElementInterface $element
     *
     * @return void
     */
    public function validateRegularExpression(ElementInterface $element)
    {
        $value = $element->getFieldValue($this->handle);

        if (!SproutBase::$app->regularExpressionField->validate($value, $this)) {
            $element->addError($this->handle,
                SproutBase::$app->regularExpressionField->getErrorMessage($this)
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function getCompatibleCraftFieldTypes(): array
    {
        /** @noinspection ClassConstantCanBeUsedInspection */
        return [
            CraftPlainText::class,
            'barrelstrength\\sproutfields\\fields\\RegularExpression'
        ];
    }
}
