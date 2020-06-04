<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\sitemaps\controllers;

use barrelstrength\sproutbase\app\sitemaps\models\SitemapSection;
use barrelstrength\sproutbase\app\uris\sectiontypes\NoSection;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\errors\SiteNotFoundException;
use craft\web\Controller;
use Throwable;
use yii\db\Exception;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class SitemapsController extends Controller
{
    /**
     * Renders the Sitemap Index Page
     *
     * @param string|null $siteHandle
     *
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws SiteNotFoundException
     * @throws \ReflectionException
     */
    public function actionSitemapIndexTemplate(string $siteHandle = null): Response
    {
        $this->requirePermission('sprout:sitemaps:editSitemaps');

        $settings = SproutBase::$app->settings->getSettingsByKey('sitemaps');
        $enableMultilingualSitemaps = Craft::$app->getIsMultiSite() && $settings->enableMultilingualSitemaps;

        // Get Enabled Site IDs. Remove any disabled IDS.
        $enabledSiteIds = array_filter($settings->siteSettings);
        $enabledSiteGroupIds = array_filter($settings->groupSettings);

        if (!$enableMultilingualSitemaps && empty($enabledSiteIds)) {
            throw new NotFoundHttpException('No Sites are enabled for your Sitemap. Check your Craft Sites settings and Sprout SEO Sitemap Settings to enable a Site for your Sitemap.');
        }

        if ($enableMultilingualSitemaps && empty($enabledSiteGroupIds)) {
            throw new NotFoundHttpException('No Site Groups are enabled for your Sitemap. Check your Craft Sites settings and Sprout SEO Sitemap Settings to enable a Site Group for your Sitemap.');
        }

        // Get all Editable Sites for this user that also have editable Sitemaps
        $editableSiteIds = Craft::$app->getSites()->getEditableSiteIds();

        // For per-site sitemaps, only display the Sites enabled in the Sprout SEO settings
        if ($enableMultilingualSitemaps === false) {
            $editableSiteIds = array_intersect($enabledSiteIds, $editableSiteIds);
        } else {
            $siteIdsFromEditableGroups = [];

            foreach ($enabledSiteGroupIds as $enabledSiteGroupId) {
                $enabledSitesInGroup = Craft::$app->sites->getSitesByGroupId($enabledSiteGroupId);
                foreach ($enabledSitesInGroup as $enabledSites) {
                    $siteIdsFromEditableGroups[] = (int)$enabledSites->id;
                }
            }

            $editableSiteIds = array_intersect($siteIdsFromEditableGroups, $editableSiteIds);
        }

        $currentSite = null;
        $currentSiteGroup = null;
        $firstSiteInGroup = null;

        if (Craft::$app->getIsMultiSite()) {
            // Form Multi-Site we have to figure out which Site and Site Group matter
            if ($siteHandle !== null) {

                // If we have a handle, the Current Site and First Site in Group may be different
                $currentSite = Craft::$app->getSites()->getSiteByHandle($siteHandle);

                if (!$currentSite) {
                    throw new NotFoundHttpException('Invalid site handle: '.$siteHandle);
                }

                $currentSiteGroup = Craft::$app->sites->getGroupById($currentSite->groupId);
                $sitesInCurrentSiteGroup = Craft::$app->sites->getSitesByGroupId($currentSiteGroup->id);
                $firstSiteInGroup = $sitesInCurrentSiteGroup[0];
            } else {
                // If we don't have a handle, we'll load the first site in the first group
                // We'll assume that we have at least one site group and the Current Site will be the same as the First Site
                $allSiteGroups = Craft::$app->sites->getAllGroups();
                $currentSiteGroup = $allSiteGroups[0];
                $sitesInCurrentSiteGroup = Craft::$app->sites->getSitesByGroupId($currentSiteGroup->id);
                $firstSiteInGroup = $sitesInCurrentSiteGroup[0];
                $currentSite = $firstSiteInGroup;
            }
        } else {
            // For a single site, the primary site ID will do
            $currentSite = Craft::$app->getSites()->getPrimarySite();
            $firstSiteInGroup = $currentSite;
        }

        $urlEnabledSectionTypes = SproutBase::$app->sitemaps->getUrlEnabledSectionTypesForSitemaps($currentSite->id);

        $customSections = SproutBase::$app->sitemaps->getCustomSitemapSections($currentSite->id);

        return $this->renderTemplate('sprout-base-sitemaps/sitemaps', [
            'currentSite' => $currentSite,
            'firstSiteInGroup' => $firstSiteInGroup,
            'editableSiteIds' => $editableSiteIds,
            'enableMultilingualSitemaps' => $enableMultilingualSitemaps,
            'urlEnabledSectionTypes' => $urlEnabledSectionTypes,
            'customSections' => $customSections,
            'pluginSettings' => $settings
        ]);
    }

    /**
     * Renders a Sitemap Edit Page
     *
     * @param int|null            $sitemapSectionId
     * @param string|null         $siteHandle
     * @param SitemapSection|null $sitemapSection
     *
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionSitemapEditTemplate(int $sitemapSectionId = null, string $siteHandle = null, SitemapSection $sitemapSection = null): Response
    {
        $this->requirePermission('sprout:sitemaps:editSitemaps');

        if ($siteHandle === null) {
            throw new NotFoundHttpException('Unable to find site with handle: '.$siteHandle);
        }

        $currentSite = Craft::$app->getSites()->getSiteByHandle($siteHandle);

        $editableSiteIds = Craft::$app->getSites()->getEditableSiteIds();

        // Make sure the user has permission to edit that site
        if ($currentSite !== null && !in_array($currentSite->id, $editableSiteIds, false)) {
            throw new ForbiddenHttpException('User not permitted to edit content for this site.');
        }

        if (!$sitemapSection) {
            if ($sitemapSectionId) {
                $sitemapSection = SproutBase::$app->sitemaps->getSitemapSectionById($sitemapSectionId);
            } else {
                $sitemapSection = new SitemapSection();
                $sitemapSection->siteId = $currentSite->id;
                $sitemapSection->type = NoSection::class;
            }
        }

        $continueEditingUrl = 'sprout/sitemaps/edit/{id}/'.$currentSite->handle;

        $tabs = [
            [
                'label' => 'Custom Page',
                'url' => '#tab1',
                'class' => null,
            ]
        ];

        return $this->renderTemplate('sprout-base-sitemaps/sitemaps/_edit', [
            'currentSite' => $currentSite,
            'sitemapSection' => $sitemapSection,
            'continueEditingUrl' => $continueEditingUrl,
            'tabs' => $tabs
        ]);
    }

    /**
     * Saves a Sitemap Section
     *
     * @return null|Response
     * @throws Throwable
     * @throws Exception
     * @throws BadRequestHttpException
     */
    public function actionSaveSitemapSection()
    {
        $this->requirePostRequest();
        $this->requirePermission('sprout:sitemaps:editSitemaps');

        $sitemapSection = new SitemapSection();
        $sitemapSection->id = Craft::$app->getRequest()->getBodyParam('id');
        $sitemapSection->siteId = Craft::$app->getRequest()->getBodyParam('siteId');
        $sitemapSection->urlEnabledSectionId = Craft::$app->getRequest()->getBodyParam('urlEnabledSectionId');
        $sitemapSection->uri = Craft::$app->getRequest()->getBodyParam('uri');
        $sitemapSection->type = Craft::$app->getRequest()->getBodyParam('type');
        $sitemapSection->priority = Craft::$app->getRequest()->getBodyParam('priority');
        $sitemapSection->changeFrequency = Craft::$app->getRequest()->getBodyParam('changeFrequency');
        $sitemapSection->enabled = Craft::$app->getRequest()->getBodyParam('enabled');

        if (!SproutBase::$app->sitemaps->saveSitemapSection($sitemapSection)) {
            if (Craft::$app->request->getAcceptsJson()) {
                return $this->asJson([
                    'errors' => $sitemapSection->getErrors(),
                ]);
            }
            Craft::$app->getSession()->setError(Craft::t('sprout', "Couldn't save the Sitemap."));

            Craft::$app->getUrlManager()->setRouteParams([
                'sitemapSection' => $sitemapSection
            ]);

            return null;
        }

        if (Craft::$app->request->getAcceptsJson()) {
            return $this->asJson([
                'success' => true,
                'sitemapSection' => $sitemapSection
            ]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout', 'Sitemap saved.'));

        return $this->redirectToPostedUrl($sitemapSection);
    }

    /**
     * Deletes a Sitemap Section
     *
     * @return Response
     * @throws ForbiddenHttpException
     * @throws Exception
     * @throws BadRequestHttpException
     */
    public function actionDeleteSitemapById(): Response
    {
        $this->requirePostRequest();
        $this->requirePermission('sprout:sitemaps:editSitemaps');

        $sitemapSectionId = Craft::$app->getRequest()->getRequiredBodyParam('id');

        $result = SproutBase::$app->sitemaps->deleteSitemapSectionById($sitemapSectionId);

        if (Craft::$app->request->getAcceptsJson()) {
            return $this->asJson([
                'success' => $result >= 0
            ]);
        }

        return $this->redirectToPostedUrl();
    }
}
