<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\metadata\meta;

use barrelstrength\sproutbase\app\metadata\base\MetaType;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Field;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;

/**
 * Implements all attributes used in robots metadata
 */
class RobotsMetaType extends MetaType
{
    /**
     * @var string|null
     */
    protected $canonical;

    /**
     * @var string|null
     */
    protected $robots;

    public static function displayName(): string
    {
        return Craft::t('sprout', 'Robots');
    }

    public function attributes(): array
    {
        $attributes = parent::attributes();
        $attributes[] = 'canonical';
        $attributes[] = 'robots';

        return $attributes;
    }

    /**
     * @return string|null
     * @throws Exception
     */
    public function getCanonical()
    {
        if ($this->canonical || $this->metadata->getRawDataOnly()) {
            return $this->canonical;
        }

        return $this->metadata->getCanonical();
    }

    /**
     * @param $value
     */
    public function setCanonical($value)
    {
        $this->canonical = $value;
    }

    public function getRobots()
    {
        if ($this->robots || $this->metadata->getRawDataOnly()) {
            return $this->robots;
        }

        return SproutBase::$app->optimize->globals['robots'] ?? null;
    }

    public function setRobots($value)
    {
        $this->robots = SproutBase::$app->optimize->prepareRobotsMetadataValue($value);
    }

    public function getHandle(): string
    {
        return 'robots';
    }

    public function getIconPath(): string
    {
        return '@sproutbaseicons/search-minus.svg';
    }

    /**
     * @param Field $field
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getSettingsHtml(Field $field): string
    {
        $robotsNamespace = $field->handle.'[metadata][robots]';
        $robots = SproutBase::$app->optimize->prepareRobotsMetadataForSettings($this->robots);

        return Craft::$app->getView()->renderTemplate('sprout/metadata/_components/fields/elementmetadata/blocks/robots', [
            'meta' => $this,
            'field' => $field,
            'robotsNamespace' => $robotsNamespace,
            'robots' => $robots
        ]);
    }

    public function showMetaDetailsTab(): bool
    {
        return SproutBase::$app->optimize->elementMetadataField->showRobots;
    }
}
