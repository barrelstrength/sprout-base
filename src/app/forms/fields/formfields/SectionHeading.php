<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\fields\formfields;

use barrelstrength\sproutbase\app\forms\base\FormField;
use barrelstrength\sproutbase\app\forms\elements\Entry;
use barrelstrength\sproutbase\web\assetbundles\quill\QuillAsset;
use Craft;
use craft\base\ElementInterface;
use craft\helpers\Template as TemplateHelper;
use ReflectionClass;
use ReflectionException;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Markup;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\db\Schema;

class SectionHeading extends FormField
{
    /**
     * @var string
     */
    public $cssClasses;

    /**
     * @var bool
     */
    public $allowRequired = false;

    /**
     * @var string
     */
    public $notes;

    /**
     * @var bool
     */
    public $hideLabel;

    /**
     * @var string
     */
    public $output;

    public static function displayName(): string
    {
        return Craft::t('sprout', 'Section Heading');
    }

    /**
     * @inheritDoc
     */
    public static function hasContentColumn(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isPlainInput(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function defineContentAttribute(): string
    {
        return Schema::TYPE_STRING;
    }

    /**
     * @inheritDoc
     */
    public function displayInstructionsField(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    public function getSvgIconPath(): string
    {
        return '@sproutbaseassets/icons/header.svg';
    }

    /**
     * @inheritDoc
     * @return string
     * @throws ReflectionException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function getSettingsHtml(): string
    {
        $reflect = new ReflectionClass($this);
        $name = $reflect->getShortName();

        $inputId = Craft::$app->getView()->formatInputId($name);
        $view = Craft::$app->getView();
        $namespaceInputId = $view->namespaceInputId($inputId);

        $view->registerAssetBundle(QuillAsset::class);

        $options = [
            'richText' => 'Rich Text',
            'markdown' => 'Markdown',
            'html' => 'HTML',
        ];

        return $view->renderTemplate('sprout/forms/_components/fields/formfields/sectionheading/settings',
            [
                'id' => $namespaceInputId,
                'name' => $name,
                'field' => $this,
                'outputOptions' => $options,
            ]
        );
    }

    /**
     * @inheritDoc
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
        $name = $this->handle;
        $inputId = Craft::$app->getView()->formatInputId($name);
        $namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);

        if ($this->notes === null) {
            $this->notes = '';
        }

        return Craft::$app->getView()->renderTemplate('sprout/fields/_components/fields/formfields/sectionheading/input',
            [
                'id' => $namespaceInputId,
                'name' => $name,
                'field' => $this,
            ]
        );
    }

    /**
     * @inheritDoc
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getExampleInputHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('sprout/forms/_components/fields/formfields/sectionheading/example',
            [
                'field' => $this,
            ]
        );
    }

    /**
     * @param mixed $value
     * @param Entry $entry
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
        $name = $this->handle;
        $namespaceInputId = $this->getNamespace().'-'.$name;

        if ($this->notes === null) {
            $this->notes = '';
        }

        $rendered = Craft::$app->getView()->renderTemplate('sectionheading/input',
            [
                'id' => $namespaceInputId,
                'field' => $this,
            ]
        );

        return TemplateHelper::raw($rendered);
    }
}
