<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\fields\services;

use barrelstrength\sproutbase\app\fields\fields\RegularExpression as RegularExpressionField;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\web\assetbundles\regularexpressionfield\RegularExpressionFieldAsset;
use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\FieldInterface;
use craft\helpers\Html;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidConfigException;

class RegularExpression extends Component
{
    /**
     * @param Field $field
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     * @throws Exception
     */
    public function getSettingsHtml(Field $field): string
    {
        return Craft::$app->getView()->renderTemplate('sprout/fields/_components/fields/formfields/regularexpression/settings', [
            'field' => $field,
        ]);
    }

    /**
     * @param Field $field
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws InvalidConfigException
     * @throws Exception
     * @throws Exception
     */
    public function getInputHtml(Field $field, $value, ElementInterface $element = null): string
    {
        $view = Craft::$app->getView();
        $view->registerAssetBundle(RegularExpressionFieldAsset::class);

        $name = $field->handle;
        $inputId = Html::id($name);
        $namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);

        $fieldContext = SproutBase::$app->fieldUtilities->getFieldContext($field, $element);

        return Craft::$app->getView()->renderTemplate('sprout/fields/_components/fields/formfields/regularexpression/input', [
            'id' => $namespaceInputId,
            'field' => $field,
            'name' => $name,
            'value' => $value,
            'fieldContext' => $fieldContext,
        ]);
    }

    /**
     * @param                                       $value
     * @param FieldInterface|RegularExpressionField $field
     *
     * @return bool
     */
    public function validate($value, FieldInterface $field): bool
    {
        $customPattern = $field->customPattern;

        if (!empty($customPattern)) {
            // Use backtick as delimiters
            $customPattern = '`'.$customPattern.'`';

            if (!preg_match($customPattern, $value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return error message
     *
     * @param mixed $field
     *
     * @return string
     */
    public function getErrorMessage($field): string
    {
        if ($field->customPattern && $field->customPatternErrorMessage) {
            return Craft::t('sprout', $field->customPatternErrorMessage);
        }

        return Craft::t('sprout', $field->name.' must be a valid pattern.');
    }

}
