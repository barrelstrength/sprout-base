<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\sitemaps\services;

use barrelstrength\sproutbase\app\sitemaps\models\SitemapSection;
use barrelstrength\sproutbase\app\sitemaps\records\SitemapSection as SitemapSectionRecord;
use barrelstrength\sproutbase\app\uris\sectiontypes\Entry;
use barrelstrength\sproutbase\app\uris\sectiontypes\NoSection;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Element;
use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\errors\SiteNotFoundException;
use craft\helpers\UrlHelper;
use craft\models\Site;
use DateTime;
use yii\base\Component;
use yii\base\Exception;

/**
 *
 * @property \craft\models\Site[]|array $currentSitemapSites
 */
class XmlSitemap extends Component
{
    /**
     * Prepares sitemaps for a sitemapindex
     *
     * @param $siteId
     *
     * @return array
     * @throws Exception
     */
    public function getSitemapIndex($siteId = null): array
    {
        $sitemapIndexPages = [];
        $hasSingles = false;

        $totalElementsPerSitemap = $this->getTotalElementsPerSitemap();

        $urlEnabledSectionTypes = SproutBase::$app->sitemaps->getUrlEnabledSectionTypesForSitemaps($siteId);

        foreach ($urlEnabledSectionTypes as $urlEnabledSectionType) {
            $urlEnabledSectionTypeId = $urlEnabledSectionType->getElementIdColumnName();

            foreach ($urlEnabledSectionType->urlEnabledSections as $urlEnabledSection) {
                $sitemapSection = $urlEnabledSection->sitemapSection;

                if ($sitemapSection->enabled) {
                    $elementClassName = $urlEnabledSectionType->getElementType();
                    /** @var Element $element */
                    $element = new $elementClassName();
                    /** Get Total Elements for this URL-Enabled Section @var ElementQuery $query */
                    $query = $element::find();
                    $query->{$urlEnabledSectionTypeId}($urlEnabledSection->id);
                    $query->siteId($siteId);

                    $totalElements = $query->count();

                    // Is this a Singles Section?
                    $section = $urlEnabledSectionType->getById($urlEnabledSection->id);

                    if (isset($section->type) && $section->type === 'single') {
                        // only add this once
                        if ($hasSingles === false) {
                            $hasSingles = true;

                            // Add the singles at the beginning of our sitemap
                            array_unshift($sitemapIndexPages, UrlHelper::siteUrl().'sitemap-singles.xml');
                        }
                    } else {
                        $totalSitemaps = ceil($totalElements / $totalElementsPerSitemap);

                        $devMode = Craft::$app->config->getGeneral()->devMode;
                        $debugString = '';

                        if ($devMode) {
                            $debugString =
                                '?devMode=true'
                                .'&siteId='.$sitemapSection->siteId
                                .'&urlEnabledSectionId='.$sitemapSection->urlEnabledSectionId
                                .'&sitemapSectionId='.$sitemapSection->id
                                .'&type='.$sitemapSection->type
                                .'&handle='.$sitemapSection->handle;
                        }

                        // Build Sitemap Index URLs
                        for ($i = 1; $i <= $totalSitemaps; $i++) {

                            $sitemapIndexUrl = UrlHelper::siteUrl().'sitemap-'.$sitemapSection->uniqueKey.'-'.$i.'.xml'.$debugString;
                            $sitemapIndexPages[] = $sitemapIndexUrl;
                        }
                    }
                }
            }
        }

        // Fetching all Custom Sitemap defined in Sprout SEO
        $customSitemapSections = (new Query())
            ->select('id')
            ->from(SitemapSectionRecord::tableName())
            ->where(['enabled' => true])
            ->andWhere('type=:type', [':type' => NoSection::class])
            ->andWhere(['not', ['uri' => null]])
            ->count();

        if ($customSitemapSections > 0) {
            $sitemapIndexPages[] = UrlHelper::siteUrl('sitemap-custom-pages.xml');
        }

        return $sitemapIndexPages;
    }

    /**
     * Prepares urls for a dynamic sitemap
     *
     * @param $sitemapKey
     * @param $pageNumber
     * @param $siteId
     *
     * @return array
     * @throws SiteNotFoundException
     * @throws Exception
     */
    public function getDynamicSitemapElements($sitemapKey, $pageNumber, $siteId): array
    {
        $urls = [];

        $totalElementsPerSitemap = $this->getTotalElementsPerSitemap();

        $currentSitemapSites = $this->getCurrentSitemapSites();

        // Our offset should be zero for the first page
        $offset = ($totalElementsPerSitemap * $pageNumber) - $totalElementsPerSitemap;

        $enabledSitemapSections = $this->getEnabledSitemapSections($sitemapKey, $siteId);

        foreach ($enabledSitemapSections as $sitemapSection) {

            $urlEnabledSectionType = SproutBase::$app->sitemaps->getUrlEnabledSectionTypeByType($sitemapSection->type);
            $sectionModel = $urlEnabledSectionType->getById($sitemapSection->urlEnabledSectionId);

            foreach ($currentSitemapSites as $site) {

                #$globalMetadata = SproutBase::$app->globalMetadata->getGlobalMetadata($site);

                $elementMetadataFieldHandle = null;
                $elements = [];

                if ($urlEnabledSectionType !== null) {

                    $elementClassName = $urlEnabledSectionType->getElementType();
                    /** @var Element $element */
                    $element = new $elementClassName();
                    /** Get Total Elements for this URL-Enabled Section @var ElementQuery $query */
                    $query = $element::find();

                    // Example: $query->sectionId(123)
                    $urlEnabledSectionColumnName = $urlEnabledSectionType->getElementIdColumnName();
                    $query->{$urlEnabledSectionColumnName}($sitemapSection->urlEnabledSectionId);

                    $query->offset($offset);
                    $query->limit($totalElementsPerSitemap);
                    $query->site($site);
                    $query->enabledForSite();

                    if ($urlEnabledSectionType->getElementLiveStatus()) {
                        $query->status($urlEnabledSectionType->getElementLiveStatus());
                    }

                    if ($sitemapKey === 'singles') {
                        if (isset($sectionModel->type) && $sectionModel->type === 'single') {
                            $elements = $query->all();
                        }
                    } else {
                        $elements = $query->all();
                    }
                }

                // Add each Element with a URL to the Sitemap
                foreach ($elements as $element) {
                    // @todo figure out how handle this code
                    /*
                    if ($elementMetadataFieldHandle === null) {
                        $elementMetadataFieldHandle = SproutBase::$app->elementMetadata->getElementMetadataFieldHandle($element);
                    }

                    $robots = null;

                    // If we have an Element Metadata field, allow it to override robots
                    if ($elementMetadataFieldHandle) {
                        $metadata = $element->{$elementMetadataFieldHandle};

                        if (isset($metadata['enableMetaDetailsRobots']) && !empty($metadata['enableMetaDetailsRobots'])) {
                            $robots = $metadata['robots'] ?? null;
                            $robots = OptimizeHelper::prepareRobotsMetadataForSettings($robots);
                        }
                    }

                    $noIndex = $robots['noindex'] ?? $globalMetadata['robots']['noindex'] ?? null;
                    $noFollow = $robots['nofollow'] ?? $globalMetadata['robots']['nofollow'] ?? null;

                    if ($noIndex == 1 OR $noFollow == 1) {
                        Craft::info('Element ID '.$element->id.' not added to sitemap. Element Metadata field `noindex` or `nofollow` settings are enabled.', __METHOD__);
                        continue;
                    }
                    * */

                    $canonicalOverride = $metadata['canonical'] ?? null;

                    if (!empty($canonicalOverride)) {
                        Craft::info('Element ID '.$element->id.' is using a canonical override and has not been included in the sitemap. Element URL: '.$element->getUrl().'. Canonical URL: '.$canonicalOverride.'.', __METHOD__);
                        continue;
                    }

                    if ($element->getUrl() === null) {
                        Craft::info('Element ID '.$element->id.' not added to sitemap. Element does not have a URL.', __METHOD__);
                        continue;
                    }

                    // Add each location indexed by its id
                    $urls[$element->id][] = [
                        'id' => $element->id,
                        'url' => $element->getUrl(),
                        'locale' => $site->language,
                        'modified' => $element->dateUpdated->format('Y-m-d\Th:i:s\Z'),
                        'priority' => $sitemapSection['priority'],
                        'changeFrequency' => $sitemapSection['changeFrequency'],
                    ];
                }

                // Reset our field handle for the next set of elements
                $elementMetadataFieldHandle = null;
            }
        }

        $urls = $this->getLocalizedSitemapStructure($urls);

        return $urls;
    }

    /**
     * Returns all sites to process for the current sitemap request
     *
     * @return array|Site[]
     * @throws SiteNotFoundException
     * @throws \ReflectionException
     */
    public function getCurrentSitemapSites(): array
    {
        $pluginSettings = SproutBase::$app->settings->getSettingsByKey('sitemaps');

        $currentSite = Craft::$app->sites->getCurrentSite();
        $isMultisite = Craft::$app->getIsMultiSite();
        $isMultilingualSitemap = $pluginSettings->enableMultilingualSitemaps;

        // For multi-lingual sitemaps, get all sites in the Current Site group
        if ($isMultisite && $isMultilingualSitemap && in_array($currentSite->groupId, $pluginSettings->groupSettings, false)) {
            return Craft::$app->getSites()->getSitesByGroupId($currentSite->groupId);
        }

        // For non-multi-lingual sitemaps, get the current site
        if (!$isMultilingualSitemap && in_array($currentSite->id, array_filter($pluginSettings->siteSettings), false)) {
            return [$currentSite];
        }

        return [];
    }

    /**
     * Returns all Custom Section URLs
     *
     * @param $siteId
     *
     * @return array
     * @throws Exception
     * @throws \Exception
     */
    public function getCustomSectionUrls($siteId): array
    {
        $urls = [];

        // Fetch all Custom Sitemap defined in Sprout SEO
        $customSitemapSections = (new Query())
            ->select('uri, priority, [[changeFrequency]], [[dateUpdated]]')
            ->from(SitemapSectionRecord::tableName())
            ->where(['enabled' => true])
            ->andWhere('[[siteId]] = :siteId', [':siteId' => $siteId])
            ->andWhere('type=:type', [':type' => NoSection::class])
            ->all();

        foreach ($customSitemapSections as $customSitemapSection) {
            $customSitemapSection['url'] = null;
            // Adding each custom location indexed by its URL
            if (!UrlHelper::isAbsoluteUrl($customSitemapSection['uri'])) {
                $customSitemapSection['url'] = UrlHelper::siteUrl($customSitemapSection['uri']);
            }

            $modified = new DateTime($customSitemapSection['dateUpdated']);
            $customSitemapSection['modified'] = $modified->format('Y-m-d\Th:i:s\Z');

            $urls[$customSitemapSection['uri']] = $customSitemapSection;
        }

        $urls = $this->getLocalizedSitemapStructure($urls);

        return $urls;
    }

    /**
     * Process Custom Pages Sitemaps for Multi-Lingual Sitemaps that can have custom pages from multiple sections
     *
     * @param $siteIds
     * @param $sitesInGroup
     *
     * @return array
     * @throws \Exception
     */
    public function getCustomSectionUrlsForMultipleIds($siteIds, $sitesInGroup): array
    {
        $urls = [];

        $customSitemapSections = (new Query())
            ->select('[[siteId]], uri, priority, [[changeFrequency]], [[dateUpdated]]')
            ->from(SitemapSectionRecord::tableName())
            ->where(['enabled' => true])
            ->andWhere(['[[siteId]]' => $siteIds])
            ->andWhere('type=:type', [':type' => NoSection::class])
            ->indexBy('[[siteId]]')
            ->all();

        foreach ($sitesInGroup as $siteInGroup) {
            foreach ($customSitemapSections as $customSitemapSection) {
                if ($siteInGroup->id !== $customSitemapSection['siteId']) {
                    continue;
                }

                $customSitemapSection['url'] = null;
                // Adding each custom location indexed by its URL

                $url = Craft::getAlias($siteInGroup->baseUrl).$customSitemapSection['uri'];
                $customSitemapSection['url'] = $url;

                $modified = new DateTime($customSitemapSection['dateUpdated']);
                $customSitemapSection['modified'] = $modified->format('Y-m-d\Th:i:s\Z');

                $urls[$customSitemapSection['uri']] = $customSitemapSection;
            }
        }

        $urls = $this->getLocalizedSitemapStructure($urls);

        return $urls;
    }

    /**
     * Returns the value for the totalElementsPerSitemap setting. Default is 500.
     *
     * @param int $total
     *
     * @return int
     * @throws \ReflectionException
     */
    public function getTotalElementsPerSitemap($total = 500): int
    {
        $settings = SproutBase::$app->settings->getSettingsByKey('sitemaps');

        return $settings->totalElementsPerSitemap ?? $total;
    }

    /**
     * Remove Slash from URI
     *
     * @param string $uri
     *
     * @return string
     */
    public function removeSlash($uri): string
    {
        $slash = '/';

        if (isset($uri[0]) && $uri[0] == $slash) {
            $uri = ltrim($uri, $slash);
        }

        return $uri;
    }

    /**
     * @param $sitemapKey
     * @param $siteId
     *
     * @return array
     */
    protected function getEnabledSitemapSections($sitemapKey, $siteId): array
    {
        $query = (new Query())
            ->select('*')
            ->from(SitemapSectionRecord::tableName())
            ->where('enabled = true and [[urlEnabledSectionId]] is not null')
            ->andWhere('[[siteId]] = :siteId', [':siteId' => $siteId]);

        if ($sitemapKey == 'singles') {
            $query->andWhere('type = :type', [':type' => Entry::class]);
        } else {
            $query->andWhere('[[uniqueKey]] = :uniqueKey', [':uniqueKey' => $sitemapKey]);
        }

        $results = $query->all();

        $sitemapSections = [];
        foreach ($results as $result) {
            $sitemapSections[] = new SitemapSection($result);
        }

        return $sitemapSections;
    }

    /**
     * Returns an array of localized entries for a sitemap from a set of URLs indexed by id
     *
     * The returned structure is compliant with multiple locale google sitemap spec
     *
     * @param array $stack
     *
     * @return array
     */
    protected function getLocalizedSitemapStructure(array $stack): array
    {
        // Defining the containing structure
        $structure = [];

        /**
         * Looping through all entries indexed by id
         */
        foreach ($stack as $id => $locations) {
            if (is_string($id)) {
                // Adding a custom location indexed by its URL
                $structure[] = $locations;
            } else {
                // Looping through each element and adding it as primary and creating its alternates
                foreach ($locations as $index => $location) {
                    // Add secondary locations as alternatives to primary
                    if (count($locations) > 1) {
                        $structure[] = array_merge($location, ['alternates' => $locations]);
                    } else {
                        $structure[] = $location;
                    }
                }
            }
        }

        return $structure;
    }
}
