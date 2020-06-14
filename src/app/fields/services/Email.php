<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\fields\services;

use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\FieldInterface;
use craft\db\Query;
use craft\db\Table;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Component;
use yii\base\Exception;

class Email extends Component
{
    /**
     * @param $field
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     * @throws Exception
     */
    public function getSettingsHtml($field): string
    {
        return Craft::$app->getView()->renderTemplate('sprout/fields/_components/fields/formfields/email/settings',
            [
                'field' => $field
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
        $inputId = Craft::$app->getView()->formatInputId($name);
        $namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);

        $fieldContext = SproutBase::$app->fieldUtilities->getFieldContext($field, $element);

        return Craft::$app->getView()->renderTemplate('sprout/fields/_components/fields/formfields/email/input',
            [
                'namespaceInputId' => $namespaceInputId,
                'id' => $inputId,
                'name' => $name,
                'value' => $value,
                'fieldContext' => $fieldContext,
                'placeholder' => $field->placeholder,
                'element' => $element
            ]);
    }

    /**
     * @param                $value
     * @param FieldInterface $field
     *
     * @return bool
     */
    public function validateEmail($value, FieldInterface $field = null): bool
    {
        if ($field) {
            /** @var Field $field */
            $customPattern = $field->customPattern;
            $checkPattern = $field->customPatternToggle;

            if ($checkPattern) {
                // Use backtick as delimiters as they are invalid characters for emails
                $customPattern = '`'.$customPattern.'`';

                if (!preg_match($customPattern, $value)) {
                    return false;
                }
            }
        }

        return !(filter_var($value, FILTER_VALIDATE_EMAIL) === false);
    }

    /**
     * Validates that an email address is unique to a particular field type
     *
     * @param                  $value
     * @param FieldInterface $field
     * @param ElementInterface $element
     *
     * @return bool
     */
    public function validateUniqueEmail($value, FieldInterface $field, ElementInterface $element): bool
    {
        /** @var Field $field */
        $fieldHandle = $element->fieldColumnPrefix.$field->handle;
        $contentTable = $element->contentTable;

        $query = (new Query())
            ->select($fieldHandle)
            ->from($contentTable)
            ->innerJoin(Table::ELEMENTS.' elements', '[[elements.id]] = '.$contentTable.'.`elementId`')
            ->where([$fieldHandle => $value])
            ->andWhere(['elements.draftId' => null])
            ->andWhere(['elements.revisionId' => null])
            ->andWhere(['elements.dateDeleted' => null]);

        if ($element->getSourceId()) {
            // Exclude current element or source element (if draft) from our results
            $query->andWhere(['not in', 'elementId', $element->getSourceId()]);
        }

        $emailExists = $query->scalar();

        if ($emailExists) {
            return false;
        }

        return true;
    }

    /**
     * @param FieldInterface $field
     *
     * @return string
     */
    public function getErrorMessage(FieldInterface $field): string
    {
        /** @var Field $field */
        if ($field->customPatternToggle && $field->customPatternErrorMessage) {
            return Craft::t('sprout', $field->customPatternErrorMessage);
        }

        return Craft::t('sprout', $field->name.' must be a valid email.');
    }
}
