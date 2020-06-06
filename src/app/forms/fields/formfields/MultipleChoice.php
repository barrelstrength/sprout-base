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
use Craft;
use craft\fields\RadioButtons as CraftRadioButtons;
use craft\helpers\Template as TemplateHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Markup;
use yii\base\Exception;

/**
 * Class SproutFormsRadioButtonsField
 *
 *
 * @property string $svgIconPath
 * @property array  $compatibleCraftFields
 * @property array  $compatibleCraftFieldTypes
 * @property array  $compatibleConditions
 * @property mixed  $exampleInputHtml
 */
class MultipleChoice extends CraftRadioButtons
{
    use FormFieldTrait;
    use BaseOptionsConditionalTrait;

    /**
     * @var string
     */
    public $cssClasses;

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout', 'Multiple Choice');
    }

    /**
     * @return bool
     */
    public function hasMultipleLabels(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    public function getSvgIconPath(): string
    {
        return '@sproutbaseicons/dot-circle-o.svg';
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
        return Craft::$app->getView()->renderTemplate('sprout-base-forms/_components/fields/formfields/multiplechoice/example',
            [
                'field' => $this
            ]
        );
    }

    /**
     * @inheritdoc
     *
     * @param            $value
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
        $rendered = Craft::$app->getView()->renderTemplate('multiplechoice/input',
            [
                'name' => $this->handle,
                'value' => $value,
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
    public function getCompatibleCraftFieldTypes(): array
    {
        return [
            CraftRadioButtons::class
        ];
    }

    /**
     * @inheritdoc
     */
    public function getCompatibleConditions()
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

    /**
     * @inheritdoc
     */
    protected function optionsSettingLabel(): string
    {
        return Craft::t('sprout', 'Multiple Choice Options');
    }
}