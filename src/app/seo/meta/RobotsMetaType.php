<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\seo\meta;

use barrelstrength\sproutbase\app\seo\base\MetaType;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Field;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;

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

        return SproutBase::$app->optimizeMetadata->globals['robots'] ?? null;
    }

    public function setRobots($value)
    {
        $this->robots = SproutBase::$app->optimizeMetadata->prepareRobotsMetadataValue($value);
    }

    public function getHandle(): string
    {
        return 'robots';
    }

    public function getIconPath(): string
    {
        return '@sproutbaseassets/icons/search-minus.svg';
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
        $robots = SproutBase::$app->optimizeMetadata->prepareRobotsMetadataForSettings($this->robots);

        return Craft::$app->getView()->renderTemplate('sprout/seo/_components/fields/elementmetadata/blocks/robots', [
            'meta' => $this,
            'field' => $field,
            'robotsNamespace' => $robotsNamespace,
            'robots' => $robots,
        ]);
    }

    public function showMetaDetailsTab(): bool
    {
        return SproutBase::$app->optimizeMetadata->elementMetadataField->showRobots;
    }
}
