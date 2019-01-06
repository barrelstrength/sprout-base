<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\import\base;

use barrelstrength\sproutbase\SproutBase;
use Craft;
use Faker\Factory;
use Faker\Generator;
use yii\base\Model;

/**
 * Class Importer
 *
 * @package Craft
 */
abstract class Importer
{
    /**
     * The model of the thing being imported: Element, Setting, Field etc.
     *
     * Examples:
     * - User
     * - FieldModel
     * - PlainTextFieldType
     *
     * @var mixed
     */
    public $model;

    /**
     * The model of the importer class.
     *
     * Examples:
     * - UserSproutImportElementImporter
     * - FieldSproutImportSettingsImporter
     * - PlainTextSproutImportFieldImporter
     *
     * @var null
     */
    protected $importerClass;

    /**
     * Any data an importer needs to store and access at another time such as
     * after something is saved and another action needs to be performed
     *
     * @var
     */
    public $rows;

    /**
     * @var $seedSettings []
     */
    public $seedSettings;

    /**
     * Any errors that have occurred that we want to store and access later
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Access to the Faker Service layer
     *
     * @var $fakerService Generator
     */
    protected $fakerService;

    /**
     * @var bool
     */
    public $isUpdated = false;

    /**
     * Importer constructor.
     *
     * @param array $rows
     * @param null  $fakerService
     *
     * @throws \Exception
     */
    public function __construct(array $rows = [], $fakerService = null)
    {
        $this->rows = $rows;

        $model = $this->getModel();

        if (count($rows)) {
            $this->setModel($model, $rows);
        }

        if ($fakerService == null) {
            $this->fakerService = Factory::create();
        } else {
            $this->fakerService = $fakerService;
        }

        $plugin = Craft::$app->plugins->getPlugin('sprout-base');

        if ($plugin) {
            $settings = $plugin->getSettings();

            $this->seedSettings = $settings['seedSettings'];
        }
    }

    /**
     * The Importer Class
     *
     * Examples:
     * - Craft\UserSproutImportElementImporter
     * - Craft\FieldSproutImportSettingsImporter
     * - Craft\PlainTextSproutImportFieldImporter
     *
     * @return string
     * @throws \ReflectionException
     */
    final public function getImporterClass()
    {
        $reflection = new \ReflectionClass($this);
        $importerClass = $reflection->getShortName();

        $this->importerClass = $importerClass;

        return $importerClass;
    }

    /**
     * The user-friendly name for the imported data type
     *
     * Examples:
     * - Users
     * - Fields
     * - Plain Text
     *
     * @return string
     */
    abstract public function getName(): string;

    /**
     * The primary model that the Importer supports
     *
     * Examples:
     * - \craft\elements\Entry::class
     * - \craft\elements\Category::class
     * - \craft\models\Section::class
     * - barrelstrength\sproutbase\app\import\importers\settings\Field::class
     *
     * @return string
     */
    abstract public function getModelName(): string;

    /**
     * @return bool
     */
    public function isElement(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isSettings(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isField(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function hasSeedGenerator(): bool
    {
        return false;
    }

    /**
     * @param Model $model
     * @param array $settings
     *
     * @return mixed
     */
    public function setModel($model, array $settings = [])
    {
        if (!empty($settings['attributes'])) {
            $model->setAttributes($settings['attributes'], false);
        }

        $this->model = $model;

        return $this->model;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        if (!$this->model) {
            $className = $this->getModelName();

            if (!class_exists($className)) {
                throw new \InvalidArgumentException(Craft::t('sprout-base', $className.' namespace on getModelName() method not found.'));
            }

            $this->model = new $className();
        }

        return $this->model;
    }

    /**
     * @return bool
     */
    public function resolveRelatedSettings()
    {
        return true;
    }

    /**
     * @param $model
     * @param $settings
     *
     * @return bool
     */
    public function resolveNestedSettings($model, $settings)
    {
        return true;
    }

    /**
     * @return string
     */
    public function getSeedCount()
    {
        $name = $this->getModelName();

        return SproutBase::$app->seed->getSeedCountByElementType($name);
    }

    /**
     * Define the keys available in $this->data
     *
     * @return array
     */
    public function getImporterDataKeys(): array
    {
        return [];
    }

    /**
     * Return any errors from the model of the thing being imported
     *
     * Examples:
     * - $userModel->getErrors()
     * - $fieldModel->getErrors()
     * - $plainTextFieldModel->getErrors()
     *
     * @return mixed
     */
    public function getModelErrors()
    {
        return $this->model->getErrors();
    }

    abstract public function save();
}
