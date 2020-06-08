<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\fields\fields;

use barrelstrength\sproutbase\app\fields\models\Name as NameModel;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;

/**
 *
 * @property mixed $settingsHtml
 */
class Name extends Field implements PreviewableFieldInterface
{
    /**
     * @var bool
     */
    public $displayMultipleFields;

    /**
     * @var bool
     */
    public $displayMiddleName;

    /**
     * @var bool
     */
    public $displayPrefix;

    /**
     * @var bool
     */
    public $displaySuffix;

    public static function displayName(): string
    {
        return Craft::t('sprout', 'Name (Sprout Fields)');
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
        return SproutBase::$app->nameField->getSettingsHtml($this);
    }

    /**
     * @inheritdoc
     *
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        return SproutBase::$app->nameField->getInputHtml($this, $value, $element);
    }

    /**
     * Prepare our Name for use as an NameModel
     *
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return NameModel|mixed
     *
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        return SproutBase::$app->nameField->normalizeValue($value);
    }

    /**
     * Prepare the field value for the database.
     *
     * @param NameModel             $value
     * @param ElementInterface|null $element
     *
     * @return array|bool|mixed|null|string
     *
     * We store the Name as JSON in the content column.
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        $value = SproutBase::$app->nameField->serializeValue($value);

        return parent::serializeValue($value, $element);
    }

    /**
     * @inheritdoc
     */
    public function getTableAttributeHtml($value, ElementInterface $element): string
    {
        $html = '';

        if ($value) {
            /** @var NameModel $value */
            $html = $value->getFullName();
        }

        return $html;
    }
}
