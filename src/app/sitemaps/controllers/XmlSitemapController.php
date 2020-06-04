<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\sitemaps\controllers;

use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\errors\SiteNotFoundException;
use craft\web\Controller;
use Exception;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class XmlSitemapController extends Controller
{
    /**
     * @inheritdoc
     */
    public $allowAnonymous = ['render-xml-sitemap'];

    /**
     * Generates an XML sitemapindex or sitemap
     *
     * @param null     $sitemapKey
     * @param int|null $pageNumber
     *
     * @return Response
     * @throws HttpException
     * @throws SiteNotFoundException
     * @throws \yii\base\Exception
     * @throws Exception
     */
    public function actionRenderXmlSitemap($sitemapKey = null, int $pageNumber = null): Response
    {
        $siteId = Craft::$app->sites->getCurrentSite()->id;
        $multiSiteSiteIds = [];
        $sitesInGroup = [];

        $settings = SproutBase::$app->settings->getSettingsByKey('sitemaps');

        if (!$settings->enableDynamicSitemaps) {
            throw new NotFoundHttpException('XML Sitemap not enabled.');
        }

        $isMultilingualSitemap = $settings->enableMultilingualSitemaps;

        if (Craft::$app->getIsMultiSite() && $isMultilingualSitemap) {
            $sitesInGroup = SproutBase::$app->xmlSitemap->getCurrentSitemapSites();
            $firstSiteInGroup = $sitesInGroup[0] ?? null;

            // Only render sitemaps for the primary site in a group
            if ($firstSiteInGroup === null || $siteId !== $firstSiteInGroup->id) {
                throw new HttpException(404);
            }

            foreach ($sitesInGroup as $siteInGroup) {
                $multiSiteSiteIds[] = (int)$siteInGroup->id;
            }
        }

        $sitemapIndexUrls = [];
        $elements = [];

        switch ($sitemapKey) {
            // Generate Sitemap Index
            case '':
                $sitemapIndexUrls = SproutBase::$app->xmlSitemap->getSitemapIndex($siteId);
                break;

            // Prepare Singles Sitemap
            case 'singles':
                $elements = SproutBase::$app->xmlSitemap->getDynamicSitemapElements('singles', $pageNumber, $siteId);
                break;

            // Prepare Custom Pages Sitemap
            case 'custom-pages':
                if (count($multiSiteSiteIds)) {
                    $elements = SproutBase::$app->xmlSitemap->getCustomSectionUrlsForMultipleIds($multiSiteSiteIds, $sitesInGroup);
                } else {
                    $elements = SproutBase::$app->xmlSitemap->getCustomSectionUrls($siteId);
                }

                break;

            // Prepare URL-Enabled Section Sitemap
            default:
                $elements = SproutBase::$app->xmlSitemap->getDynamicSitemapElements($sitemapKey, $pageNumber, $siteId);
        }

        $headers = Craft::$app->getResponse()->getHeaders();
        $headers->set('Content-Type', 'application/xml');

        $templatePath = Craft::getAlias('@sproutbase/app/sitemaps/templates/');
        Craft::$app->view->setTemplatesPath($templatePath);

        // Render a specific sitemap
        if ($sitemapKey) {
            return $this->renderTemplate('_components/sitemaps/sitemap', [
                'elements' => $elements
            ]);
        }

        // Render the sitemapindex if no specific sitemap is defined
        return $this->renderTemplate('_components/sitemaps/sitemapindex', [
            'sitemapIndexUrls' => $sitemapIndexUrls
        ]);
    }
}
