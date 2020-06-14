<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\uris\services;

use barrelstrength\sproutbase\app\uris\base\UrlEnabledSectionType;
use barrelstrength\sproutbase\app\uris\events\RegisterUrlEnabledSectionTypesEvent;
use barrelstrength\sproutbase\app\uris\models\UrlEnabledSection;
use barrelstrength\sproutbase\app\uris\sectiontypes\Category;
use barrelstrength\sproutbase\app\uris\sectiontypes\Entry;
use barrelstrength\sproutbase\app\uris\sectiontypes\NoSection;
use barrelstrength\sproutbase\app\uris\sectiontypes\Product;
use Craft;
use craft\errors\SiteNotFoundException;
use yii\base\Component;

/**
 *
 * @property array $matchedElementVariables
 * @property UrlEnabledSectionType[] $registeredUrlEnabledSectionsEvent
 */
class UrlEnabledSections extends Component
{
    const EVENT_REGISTER_URL_ENABLED_SECTION_TYPES = 'registerUrlEnabledSectionTypesEvent';

    /**
     * @var UrlEnabledSectionType[]
     */
    public $urlEnabledSectionTypes;

    /**
     * Returns all registered Url-Enabled Section Types
     *
     * @return UrlEnabledSectionType[]
     */
    public function getRegisteredUrlEnabledSectionsEvent(): array
    {
        $urlEnabledSectionTypes = [
            Entry::class,
            Category::class,
            NoSection::class,
        ];

        if (Craft::$app->getPlugins()->getPlugin('commerce')) {
            $urlEnabledSectionTypes[] = Product::class;
        }

        $event = new RegisterUrlEnabledSectionTypesEvent([
            'urlEnabledSectionTypes' => $urlEnabledSectionTypes
        ]);

        $this->trigger(self::EVENT_REGISTER_URL_ENABLED_SECTION_TYPES, $event);

        return $event->urlEnabledSectionTypes;
    }

    /**
     * @return array
     */
    public function getUrlEnabledSectionTypes(): array
    {
        $urlEnabledSectionTypes = $this->getRegisteredUrlEnabledSectionsEvent();

        $urlEnabledSections = [];

        foreach ($urlEnabledSectionTypes as $urlEnabledSectionType) {
            $urlEnabledSections[] = new $urlEnabledSectionType();
        }

        uasort($urlEnabledSections, static function($a, $b) {
            /**
             * @var $a UrlEnabledSectionType
             * @var $b UrlEnabledSectionType
             */
            return $a->getName() <=> $b->getName();
        });

        return $urlEnabledSections;
    }

    /**
     * @return array
     */
    public function getMatchedElementVariables(): array
    {
        $urlEnabledSections = $this->getUrlEnabledSectionTypes();

        $matchedElementVariables = [];

        foreach ($urlEnabledSections as $urlEnabledSection) {
            $matchedElementVariables[] = $urlEnabledSection->getMatchedElementVariable();
        }

        return array_filter($matchedElementVariables);
    }

    /**
     * Get the active URL-Enabled Section Type via the Element Type
     *
     * @param $elementType
     *
     * @return UrlEnabledSectionType|null
     * @throws SiteNotFoundException
     */
    public function getUrlEnabledSectionTypeByElementType($elementType)
    {
        $currentSite = Craft::$app->sites->getCurrentSite();
        $this->prepareUrlEnabledSectionTypesForMetadataField($currentSite->id);

        foreach ($this->urlEnabledSectionTypes as $urlEnabledSectionType) {

            if ($urlEnabledSectionType->getElementType() == $elementType) {
                return $urlEnabledSectionType;
            }
        }

        return null;
    }

    /**
     * @param $siteId
     */
    public function prepareUrlEnabledSectionTypesForMetadataField($siteId)
    {
        $registeredUrlEnabledSectionsTypes = $this->getRegisteredUrlEnabledSectionsEvent();

        foreach ($registeredUrlEnabledSectionsTypes as $urlEnabledSectionType) {
            /**
             * @var UrlEnabledSectionType $urlEnabledSectionType
             */
            $urlEnabledSectionType = new $urlEnabledSectionType();
            $allUrlEnabledSections = $urlEnabledSectionType->getAllUrlEnabledSections($siteId);
            $urlEnabledSections = [];
            /**
             * @var UrlEnabledSection $urlEnabledSection
             */
            foreach ($allUrlEnabledSections as $urlEnabledSection) {
                $uniqueKey = $urlEnabledSectionType->getId().'-'.$urlEnabledSection->id;
                $model = new UrlEnabledSection();

                $model->type = $urlEnabledSectionType;
                $model->id = $urlEnabledSection->id;
                $urlEnabledSections[$uniqueKey] = $model;
            }
            $urlEnabledSectionType->urlEnabledSections = $urlEnabledSections;
            $this->urlEnabledSectionTypes[$urlEnabledSectionType->getId()] = $urlEnabledSectionType;
        }
    }
}
