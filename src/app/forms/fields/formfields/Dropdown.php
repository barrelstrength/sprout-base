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
use craft\fields\Dropdown as CraftDropdownField;
use craft\fields\PlainText as CraftPlainText;
use craft\helpers\Template as TemplateHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Markup;
use yii\base\Exception;

/**
 * Class SproutFormsDropdownField
 *
 * @property string $modelName
 * @property string $svgIconPath
 * @property array $compatibleCraftFields
 * @property array $compatibleCraftFieldTypes
 * @property array $compatibleConditions
 * @property mixed $exampleInputHtml
 */
class Dropdown extends CraftDropdownField
{
    use FormFieldTrait;
    use BaseOptionsConditionalTrait;

    /**
     * @var string
     */
    public $cssClasses;

    /**
     * @return string
     */
    public function getModelName(): string
    {
        return CraftDropdownField::class;
    }

    /**
     * @return string
     */
    public function getSvgIconPath(): string
    {
        return '@sproutbaseassets/icons/chevron-circle-down.svg';
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
        return Craft::$app->getView()->renderTemplate('sprout/forms/_components/fields/formfields/dropdown/example',
            [
                'field' => $this
            ]
        );
    }

    /**
     * @param mixed $value
     * @param Entry $entry
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
        $rendered = Craft::$app->getView()->renderTemplate('dropdown/input',
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
