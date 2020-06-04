<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\sitemaps\services;

use barrelstrength\sproutbase\app\sitemaps\models\SitemapSection;
use barrelstrength\sproutbase\app\sitemaps\records\SitemapSection as SitemapSectionRecord;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\app\uris\base\UrlEnabledSectionType;
use barrelstrength\sproutbase\app\uris\models\UrlEnabledSection;
use barrelstrength\sproutbase\app\uris\sectiontypes\NoSection;
use Craft;
use craft\base\Element;
use craft\db\Query;
use craft\errors\SiteNotFoundException;
use Throwable;
use yii\base\Component;
use yii\db\Exception;
use yii\web\NotFoundHttpException;

class Sitemaps extends Component
{
    /**
     * @var array
     */
    public $urlEnabledSectionTypes;

    /**
     * @var SitemapSectionRecord
     */
    protected $sitemapsRecord;

    /**
     * Returns all Custom Sitemap Sections
     *
     * @param $siteId
     *
     * @return array
     */
    public function getCustomSitemapSections($siteId): array
    {
        $customSections = (new Query())
            ->select('*')
            ->from([SitemapSectionRecord::tableName()])
            ->where('[[siteId]]=:siteId', [':siteId' => $siteId])
            ->andWhere('type=:type', [':type' => NoSection::class])
            ->all();

        $sitemapSections = [];

        if ($customSections) {
            foreach ($customSections as $customSection) {
                $sitemapSections[] = new SitemapSection($customSection);
            }
        }

        return $sitemapSections;
    }

    /**
     * Get all Sitemap Sections related to this URL-Enabled Section Type
     *
     * Order the results by URL-Enabled Section ID: type-id
     * Example: entries-5, categories-12
     *
     * @param UrlEnabledSectionType $urlEnabledSectionType
     * @param null                  $siteId
     *
     * @return array
     * @throws SiteNotFoundException
     */
    public function getSitemapSections(UrlEnabledSectionType $urlEnabledSectionType, $siteId = null): array
    {
        $type = get_class($urlEnabledSectionType);
        $allSitemapSections = SproutBase::$app->sitemaps->getSitemapSectionsByType($type, $siteId);

        $sitemapSections = [];

        foreach ($allSitemapSections as $sitemapSection) {
            $urlEnabledSectionUniqueKey = $urlEnabledSectionType->getId().'-'.$sitemapSection['urlEnabledSectionId'];

            $sitemapSections[$urlEnabledSectionUniqueKey] = $sitemapSection;
        }

        return $sitemapSections;
    }

    /**
     * Get all the Sitemap Sections of a particular type
     *
     * @param $type
     *
     * @param $siteId
     *
     * @return array
     * @throws SiteNotFoundException
     */
    public function getSitemapSectionsByType($type, $siteId = null): array
    {
        if ($siteId === null) {
            throw new SiteNotFoundException('Unable to find site. $siteId must not be null');
        }

        $results = (new Query())
            ->select('*')
            ->from([SitemapSectionRecord::tableName()])
            ->where(['type' => $type, '[[siteId]]' => $siteId])
            ->all();

        $sitemapSections = [];

        if ($results) {
            foreach ($results as $result) {
                $sitemapSections[] = new SitemapSection($result);
            }
        }

        return $sitemapSections;
    }

    /**
     * Returns a Sitemap Section by ID
     *
     * @param $id
     *
     * @return SitemapSection|null
     */
    public function getSitemapSectionById($id)
    {
        $result = (new Query())
            ->select('*')
            ->from([SitemapSectionRecord::tableName()])
            ->where(['id' => $id])
            ->one();

        if ($result) {
            return new SitemapSection($result);
        }

        return null;
    }

    /**
     * @param SitemapSection $sitemapSection
     *
     * @return bool
     * @throws Exception
     * @throws Throwable
     */
    public function saveSitemapSection(SitemapSection $sitemapSection): bool
    {
        $isNewSection = !$sitemapSection->id;

        if (!$isNewSection) {
            if (null === ($sitemapSectionRecord = SitemapSectionRecord::findOne($sitemapSection->id))) {
                throw new Exception('Unable to find Sitemap with ID: '.$sitemapSection->id);
            }
        } else {
            $sitemapSectionRecord = new SitemapSectionRecord();
            $sitemapSectionRecord->uniqueKey = $this->generateUniqueKey();
        }

        if ($sitemapSection->type === NoSection::class) {
            $sitemapSection->setScenario('customSection');

            if (!$sitemapSection->validate()) {
                return false;
            }
        }

        $sitemapSection->validate();

        if ($sitemapSection->getErrors()) {
            return false;
        }

        if ($sitemapSection->id) {
            $sitemapSectionRecord->id = $sitemapSection->id;
        }
        $sitemapSectionRecord->siteId = $sitemapSection->siteId;
        $sitemapSectionRecord->urlEnabledSectionId = $sitemapSection->urlEnabledSectionId;
        $sitemapSectionRecord->type = $sitemapSection->type;
        $sitemapSectionRecord->uri = $sitemapSection->uri;
        $sitemapSectionRecord->priority = $sitemapSection->priority;
        $sitemapSectionRecord->changeFrequency = $sitemapSection->changeFrequency;
        $sitemapSectionRecord->enabled = $sitemapSection->enabled ?? 0;

        $transaction = Craft::$app->db->beginTransaction();

        try {
            $sitemapSectionRecord->save(false);
            $transaction->commit();
        } catch (Throwable $e) {
            $sitemapSection->addErrors($sitemapSectionRecord->getErrors());
            $transaction->rollBack();
            throw $e;
        }

        // update id on model (for new records)
        $sitemapSection->id = $sitemapSectionRecord->id;

        $settings = SproutBase::$app->settings->getSettingsByKey('sitemaps');

        // Copy this site behavior to the whole group, for the Url-Enabled Sitemaps
        // Custom Sections will be allowed to be unique, even in Multi-Lingual Sitemaps
        if ($settings->enableMultilingualSitemaps && $sitemapSectionRecord->type !== NoSection::class) {
            $site = Craft::$app->getSites()->getSiteById($sitemapSectionRecord->siteId);

            if (!$site) {
                throw new NotFoundHttpException('Unable to find Site with ID: '.$sitemapSectionRecord->siteId);
            }

            $sitesInGroup = Craft::$app->getSites()->getSitesByGroupId($site->groupId);

            $siteIds = [];
            foreach ($sitesInGroup as $siteInGroup) {
                $siteIds[] = $siteInGroup->id;
            }

            // all sections saved for this site
            $sitemapSectionRecords = SitemapSectionRecord::find()
                ->where(['in', 'siteId', $siteIds])
                ->andWhere([
                    'urlEnabledSectionId' => $sitemapSectionRecord->urlEnabledSectionId
                ])
                ->indexBy('siteId')
                ->all();

            foreach ($sitesInGroup as $siteInGroup) {

                if (isset($sitemapSectionRecords[$siteInGroup->id])) {
                    $sitemapSectionRecord = $sitemapSectionRecords[$siteInGroup->id];
                } else {
                    $sitemapSectionRecord = new SitemapSectionRecord();
                    $sitemapSectionRecord->uniqueKey = $this->generateUniqueKey();
                }

                $sitemapSectionRecord->siteId = $siteInGroup->id;
                $sitemapSectionRecord->type = $sitemapSection->type;
                $sitemapSectionRecord->urlEnabledSectionId = $sitemapSection->urlEnabledSectionId;
                $sitemapSectionRecord->uri = $sitemapSection->uri;
                $sitemapSectionRecord->priority = $sitemapSection->priority;
                $sitemapSectionRecord->changeFrequency = $sitemapSection->changeFrequency;
                $sitemapSectionRecord->enabled = $sitemapSection->enabled;

                $sitemapSectionRecord->save();
            }
        }

        $sitemapSection->id = $sitemapSectionRecord->id;

        return true;
    }

    /**
     * Delete a Sitemap by ID
     *
     * @param null $id
     *
     * @return bool
     * @throws Exception
     */
    public function deleteSitemapSectionById($id = null): bool
    {
        $sitemapSectionRecord = SitemapSectionRecord::findOne($id);

        if (!$sitemapSectionRecord) {
            return false;
        }

        $affectedRows = Craft::$app->getDb()->createCommand()
            ->delete(SitemapSectionRecord::tableName(), [
                'id' => $id
            ])
            ->execute();

        return (bool)$affectedRows;
    }

    /**
     * @return string
     * @throws \yii\base\Exception
     */
    public function generateUniqueKey(): string
    {
        $key = Craft::$app->getSecurity()->generateRandomString(12);

        $result = (new Query())
            ->select('[[uniqueKey]]')
            ->from([SitemapSectionRecord::tableName()])
            ->where(['[[uniqueKey]]' => $key])
            ->scalar();

        if ($result) {
            // Try again until we have a unique key
            $this->generateUniqueKey();
        }

        return $key;
    }

    /**
     * Get all registered Element Groups
     *
     * @param null $siteId
     *
     * @return UrlEnabledSectionType[]
     * @throws SiteNotFoundException
     */
    public function getUrlEnabledSectionTypesForSitemaps($siteId = null): array
    {
        $this->prepareUrlEnabledSectionTypesForSitemaps($siteId);

        return $this->urlEnabledSectionTypes;
    }

    /**
     * Prepare the $this->urlEnabledSectionTypes variable for use in Sections and Sitemap pages
     *
     * @param null $siteId
     *
     * @return null
     * @throws SiteNotFoundException
     */
    public function prepareUrlEnabledSectionTypesForSitemaps($siteId = null)
    {
        // Have we already prepared our URL-Enabled Sections?
        if (!empty($this->urlEnabledSectionTypes)) {
            return null;
        }

        $registeredUrlEnabledSectionsTypes = SproutBase::$app->urlEnabledSections->getRegisteredUrlEnabledSectionsEvent();

        foreach ($registeredUrlEnabledSectionsTypes as $urlEnabledSectionType) {
            /**
             * @var UrlEnabledSectionType $urlEnabledSectionType
             */
            $urlEnabledSectionType = new $urlEnabledSectionType();
            $sitemapSections = SproutBase::$app->sitemaps->getSitemapSections($urlEnabledSectionType, $siteId);
            $allUrlEnabledSections = $urlEnabledSectionType->getAllUrlEnabledSections($siteId);

            // Prepare a list of all URL-Enabled Sections for this URL-Enabled Section Type
            // if we have an existing Sitemap, use it, otherwise fallback to a new model
            $urlEnabledSections = [];

            /**
             * @var UrlEnabledSection $urlEnabledSection
             */
            foreach ($allUrlEnabledSections as $urlEnabledSection) {
                $uniqueKey = $urlEnabledSectionType->getId().'-'.$urlEnabledSection->id;

                $model = new UrlEnabledSection();
                $sitemapSection = null;

                if (isset($sitemapSections[$uniqueKey])) {
                    // If an URL-Enabled Section exists as Sitemap, use it
                    $sitemapSection = $sitemapSections[$uniqueKey];
                    $sitemapSection->id = $sitemapSections[$uniqueKey]->id;
                } else {
                    // If no URL-Enabled Section exists, create a new one
                    $sitemapSection = new SitemapSection();
                    $sitemapSection->isNew = true;
                    $sitemapSection->urlEnabledSectionId = $urlEnabledSection->id;
                }

                $model->type = $urlEnabledSectionType;
                $model->id = $urlEnabledSection->id;

                $sitemapSection->name = $urlEnabledSection->name;
                $sitemapSection->handle = $urlEnabledSection->handle;
                $sitemapSection->uri = $model->getUrlFormat();

                $model->sitemapSection = $sitemapSection;

                $urlEnabledSections[$uniqueKey] = $model;
            }

            $urlEnabledSectionType->urlEnabledSections = $urlEnabledSections;

            $this->urlEnabledSectionTypes[$urlEnabledSectionType->getId()] = $urlEnabledSectionType;
        }

        return null;
    }

    /**
     * @param $context
     *
     * @return Element|null
     * @throws SiteNotFoundException
     */
    public function getElementViaContext($context)
    {
        $currentSite = Craft::$app->sites->getCurrentSite();

        $this->prepareUrlEnabledSectionTypesForSitemaps($currentSite->id);

        foreach ($this->urlEnabledSectionTypes as $urlEnabledSectionType) {
            $matchedElementVariable = $urlEnabledSectionType->getMatchedElementVariable();

            if (isset($context[$matchedElementVariable])) {
                return $context[$matchedElementVariable];
            }
        }

        return null;
    }

    /**
     * @param $type
     *
     * @return UrlEnabledSectionType|array
     * @throws SiteNotFoundException
     */
    public function getUrlEnabledSectionTypeByType($type)
    {
        $currentSite = Craft::$app->sites->getCurrentSite();

        $this->prepareUrlEnabledSectionTypesForSitemaps($currentSite->id);

        foreach ($this->urlEnabledSectionTypes as $urlEnabledSectionType) {
            if (get_class($urlEnabledSectionType) == $type) {
                return $urlEnabledSectionType;
            }
        }

        return [];
    }
}
