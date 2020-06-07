<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\fields\formfields;

use barrelstrength\sproutbase\app\forms\base\FormFieldTrait;
use barrelstrength\sproutbase\app\forms\elements\Entry;
use barrelstrength\sproutbase\app\forms\fields\formfields\base\BaseOptionsConditionalTrait;
use barrelstrength\sproutbase\app\forms\rules\conditions\ContainsCondition;
use barrelstrength\sproutbase\app\forms\rules\conditions\DoesNotContainCondition;
use barrelstrength\sproutbase\app\forms\rules\conditions\DoesNotEndWithCondition;
use barrelstrength\sproutbase\app\forms\rules\conditions\DoesNotStartWithCondition;
use barrelstrength\sproutbase\app\forms\rules\conditions\EndsWithCondition;
use barrelstrength\sproutbase\app\forms\rules\conditions\IsCondition;
use barrelstrength\sproutbase\app\forms\rules\conditions\IsNotCondition;
use barrelstrength\sproutbase\app\forms\rules\conditions\StartsWithCondition;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\fields\data\SingleOptionFieldData;
use craft\fields\Dropdown as CraftDropdownField;
use craft\fields\PlainText as CraftPlainText;
use craft\helpers\StringHelper;
use craft\helpers\Template as TemplateHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Markup;
use yii\base\Exception;
use yii\db\Schema;

/**
 *
 * @property array  $elementValidationRules
 * @property string $contentColumnType
 * @property string $svgIconPath
 * @property mixed  $settingsHtml
 * @property array  $compatibleCraftFields
 * @property array  $compatibleCraftFieldTypes
 * @property array  $compatibleConditions
 * @property mixed  $exampleInputHtml
 */
class EmailDropdown extends CraftDropdownField
{
    use FormFieldTrait;
    use BaseOptionsConditionalTrait;

    /**
     * @var string
     */
    public $cssClasses;

    public static function displayName(): string
    {
        return Craft::t('sprout', 'Email Dropdown');
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_STRING;
    }

    /**
     * @return string
     */
    public function getSvgIconPath(): string
    {
        return '@sproutbaseicons/share.svg';
    }

    public function normalizeValue($value, ElementInterface $element = null)
    {
        // Make the unobfuscated values available to email notifications
        if ($value && Craft::$app->request->getIsSiteRequest() && Craft::$app->getRequest()->getIsPost()) {
            // Swap our obfuscated number value (e.g. 1) with the email value
            $selectedOption = $this->options[$value];
            $value = $selectedOption['value'];
        }

        return parent::normalizeValue($value, $element);
    }

    public function serializeValue($value, ElementInterface $element = null)
    {
        if (Craft::$app->getRequest()->isSiteRequest && $value->selected) {
            // Default fist position.
            $pos = $value->value ?: 0;

            if (isset($this->options[$pos])) {
                return $this->options[$pos]['value'];
            }
        }

        return $value;
    }

    /**
     * @inheritdoc
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getSettingsHtml()
    {
        $options = $this->options;

        if (!$options) {
            $options = [['label' => '', 'value' => '']];
        }

        return Craft::$app->getView()->renderTemplateMacro('_includes/forms',
            'editableTableField',
            [
                [
                    'label' => $this->optionsSettingLabel(),
                    'instructions' => Craft::t('sprout', 'Define the available options.'),
                    'id' => 'options',
                    'name' => 'options',
                    'addRowLabel' => Craft::t('sprout', 'Add an option'),
                    'cols' => [
                        'label' => [
                            'heading' => Craft::t('sprout', 'Name'),
                            'type' => 'singleline',
                            'autopopulate' => 'value'
                        ],
                        'value' => [
                            'heading' => Craft::t('sprout', 'Email'),
                            'type' => 'singleline',
                            'class' => 'code'
                        ],
                        'default' => [
                            'heading' => Craft::t('sprout', 'Default?'),
                            'type' => 'checkbox',
                            'class' => 'thin'
                        ],
                    ],
                    'rows' => $options
                ]
            ]
        );
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
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        /** @var SingleOptionFieldData $value */
        $valueOptions = $value->getOptions();
        $anySelected = SproutBase::$app->utilities->isAnyOptionsSelected(
            $valueOptions,
            $value->value
        );

        $name = $this->handle;
        $value = $value->value;

        if ($anySelected === false) {
            $value = $this->defaultValue();
        }

        $options = $this->options;

        return Craft::$app->getView()->renderTemplate('sprout-base-fields/_components/fields/formfields/emaildropdown/input',
            [
                'name' => $name,
                'value' => $value,
                'options' => $options
            ]
        );
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
        return Craft::$app->getView()->renderTemplate('sprout-base-forms/_components/fields/formfields/emaildropdown/example',
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
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getFrontEndInputHtml($value, Entry $entry, array $renderingOptions = null): Markup
    {
        $selectedValue = $value->value ?? null;

        $options = $this->options;
        $options = SproutBase::$app->emailDropdownField->obfuscateEmailAddresses($options, $selectedValue);

        $rendered = Craft::$app->getView()->renderTemplate('emaildropdown/input',
            [
                'name' => $this->handle,
                'value' => $value,
                'options' => $options,
                'field' => $this,
                'entry' => $entry,
                'renderingOptions' => $renderingOptions
            ]
        );

        return TemplateHelper::raw($rendered);
    }

    /**
     * @inheritdoc
     */
    public function getTableAttributeHtml($value, ElementInterface $element): string
    {
        $html = '';

        if ($value) {
            $html = $value->label.': <a href="mailto:'.$value.'" target="_blank">'.$value.'</a>';
        }

        return $html;
    }

    /**
     * @inheritdoc
     */
    public function getElementValidationRules(): array
    {
        return ['validateEmailDropdown'];
    }

    /**
     * Validates our fields submitted value beyond the checks
     * that were assumed based on the content attribute.
     *
     * @param Element|ElementInterface $element
     *
     * @return void
     */
    public function validateEmailDropdown(ElementInterface $element)
    {
        $value = $element->getFieldValue($this->handle)->value;

        $invalidEmails = [];

        $emailString = $this->options[$value]->value ?? null;

        if ($emailString) {

            $emailAddresses = StringHelper::split($emailString);
            $emailAddresses = array_unique($emailAddresses);

            foreach ($emailAddresses as $emailAddress) {
                if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
                    $invalidEmails[] = Craft::t('sprout', 'Email does not validate: '.$emailAddress);
                }
            }
        }

        if (!empty($invalidEmails)) {
            foreach ($invalidEmails as $invalidEmail) {
                $element->addError($this->handle, $invalidEmail);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getCompatibleCraftFieldTypes(): array
    {
        return [
            CraftPlainText::class,
            CraftDropdownField::class
        ];
    }

    public function getCompatibleConditions(): array
    {
        return [
            new IsCondition(),
            new IsNotCondition(),
            new ContainsCondition(),
            new DoesNotContainCondition(),
            new StartsWithCondition(),
            new DoesNotStartWithCondition(),
            new EndsWithCondition(),
            new DoesNotEndWithCondition()
        ];
    }
}
