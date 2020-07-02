<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\fields\services;

use barrelstrength\sproutbase\app\fields\models\Name as NameModel;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Component;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\FieldInterface;
use craft\helpers\Html;
use craft\helpers\Json;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;

class Name extends Component
{
    /**
     * @param $value
     *
     * @return array|NameModel
     */
    public function normalizeValue($value)
    {
        $nameModel = new NameModel();

        // String value when retrieved from db
        if (is_string($value)) {
            $nameArray = Json::decode($value);
            $nameModel->setAttributes($nameArray, false);

            return $nameModel;
        }

        // Array value from post data
        if (is_array($value) && isset($value['name'])) {

            $nameModel->setAttributes($value['name'], false);

            if ($fullNameShort = $value['name']['fullNameShort'] ?? null) {
                $nameArray = explode(' ', trim($fullNameShort));
                $nameModel->firstName = $nameArray[0] ?? $fullNameShort;
                unset($nameArray[0]);

                $nameModel->lastName = implode(' ', $nameArray);
            }

            return $nameModel;
        }

        return $value;
    }

    /**
     * @param $value
     *
     * @return string|null
     */
    public function serializeValue($value)
    {
        if ($value === null) {
            return null;
        }

        // Submitting an Element to be saved
        if ($value instanceof NameModel) {
            if (!$value->getFullNameExtended()) {
                return null;
            }

            return Json::encode($value->getAttributes());
        }

        return $value;
    }

    /**
     * @param FieldInterface $field
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     * @throws Exception
     */
    public function getSettingsHtml(FieldInterface $field): string
    {
        return Craft::$app->getView()->renderTemplate(
            'sprout/fields/_components/fields/formfields/name/settings',
            [
                'field' => $field,
            ]);
    }

    /**
     * @param FieldInterface $field
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     * @throws Exception
     */
    public function getInputHtml(FieldInterface $field, $value, ElementInterface $element = null): string
    {
        /** @var Field $field */
        $name = $field->handle;
        $inputId = Html::id($name);
        $namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);

        $fieldContext = SproutBase::$app->fieldUtilities->getFieldContext($field, $element);

        return Craft::$app->getView()->renderTemplate(
            'sprout/fields/_components/fields/formfields/name/input',
            [
                'namespaceInputId' => $namespaceInputId,
                'id' => $inputId,
                'name' => $name,
                'field' => $field,
                'value' => $value,
                'fieldContext' => $fieldContext,
            ]);
    }
}