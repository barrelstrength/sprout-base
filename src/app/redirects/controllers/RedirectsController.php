<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\redirects\controllers;

use barrelstrength\sproutbase\app\redirects\elements\Redirect;
use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\errors\SiteNotFoundException;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use Throwable;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class RedirectsController extends Controller
{
    /**
     * @param null $siteHandle
     *
     * @return Response
     * @throws SiteNotFoundException
     * @throws ForbiddenHttpException
     */
    public function actionRedirectsIndexTemplate($siteHandle = null): Response
    {
        $this->requirePermission('sprout:redirects:editRedirects');

        if ($siteHandle === null) {
            $primarySite = Craft::$app->getSites()->getPrimarySite();
            $siteHandle = $primarySite->handle;
        }

        $currentSite = Craft::$app->getSites()->getSiteByHandle($siteHandle);

        if (!$currentSite) {
            throw new ForbiddenHttpException('Unable to find site');
        }

        $isPro = SproutBase::$app->config->isEdition('redirects', Config::EDITION_PRO);
        $canCreateRedirects = SproutBase::$app->redirects->canCreateRedirects();

        return $this->renderTemplate('sprout/redirects/redirects/index', [
            'currentSite' => $currentSite,
            'isPro' => $isPro,
            'canCreateRedirects' => $canCreateRedirects
        ]);
    }

    /**
     * Edit a Redirect
     *
     * @param null          $redirectId
     * @param null          $siteHandle
     * @param Redirect|null $redirect
     *
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws SiteNotFoundException
     */
    public function actionEditRedirectTemplate($redirectId = null, $siteHandle = null, Redirect $redirect = null): Response
    {
        $this->requirePermission('sprout:redirects:editRedirects');

        if ($siteHandle === null) {
            $primarySite = Craft::$app->getSites()->getPrimarySite();
            $siteHandle = $primarySite->handle;
        }

        $currentSite = Craft::$app->getSites()->getSiteByHandle($siteHandle);

        if (!$currentSite) {
            throw new ForbiddenHttpException('Unable to identify current site.');
        }

        $methodOptions = SproutBase::$app->redirects->getMethods();

        // Now let's set up the actual redirect
        if ($redirect === null) {
            if ($redirectId !== null) {

                $redirect = Craft::$app->getElements()->getElementById($redirectId, Redirect::class, $currentSite->id);

                if (!$redirect) {
                    throw new NotFoundHttpException('Unable to find a Redirect with the given ID '.$redirectId);
                }

                if (!$redirect) {
                    $redirect = new Redirect();
                    $redirect->id = $redirectId;
                }

                $redirect->siteId = $currentSite->id;
            } else {
                $redirect = new Redirect();
                $redirect->siteId = $currentSite->id;
            }
        }

        $redirect->newUrl = $redirect->newUrl ?? '';

        $continueEditingUrl = 'sprout/redirects/edit/{id}/'.$currentSite->handle;
        $saveAsNewUrl = 'sprout/redirects/new/'.$currentSite->handle;

        $crumbs = [
            [
                'label' => Craft::t('sprout', 'Redirects'),
                'url' => UrlHelper::cpUrl('redirects')
            ]
        ];

        $tabs = [
            [
                'label' => 'Redirect',
                'url' => '#tab1',
                'class' => null,
            ]
        ];

        $isPro = SproutBase::$app->config->isEdition('redirects', Config::EDITION_PRO);

        return $this->renderTemplate('sprout/redirects/redirects/_edit', [
            'currentSite' => $currentSite,
            'redirect' => $redirect,
            'methodOptions' => $methodOptions,
            'crumbs' => $crumbs,
            'tabs' => $tabs,
            'continueEditingUrl' => $continueEditingUrl,
            'saveAsNewUrl' => $saveAsNewUrl,
            'isPro' => $isPro
        ]);
    }

    /**
     * Saves a Redirect
     *
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws Throwable
     */
    public function actionSaveRedirect()
    {
        $this->requirePostRequest();
        $this->requirePermission('sprout:redirects:editRedirects');

        $redirectId = Craft::$app->getRequest()->getBodyParam('redirectId');
        $siteId = Craft::$app->getRequest()->getBodyParam('siteId');
        $oldUrl = Craft::$app->getRequest()->getRequiredBodyParam('oldUrl');
        $newUrl = Craft::$app->getRequest()->getBodyParam('newUrl');

        if ($redirectId) {
            $redirect = Craft::$app->getElements()->getElementById($redirectId, Redirect::class, $siteId);

            if (!$redirect) {
                throw new Exception('No redirect exists with the ID :'.$redirectId);
            }

            if (!$redirect) {

                $redirect = new Redirect();
                $redirect->id = $redirectId;
            }
        } else {
            $redirect = new Redirect();
        }

        $defaultSiteId = Craft::$app->getSites()->getPrimarySite()->id;

        // Set the event attributes, defaulting to the existing values for
        // whatever is missing from the post data
        $redirect->siteId = $siteId ?? $defaultSiteId;
        $redirect->oldUrl = $oldUrl;
        $redirect->newUrl = $newUrl;
        $redirect->method = Craft::$app->getRequest()->getRequiredBodyParam('method');
        $redirect->matchStrategy = Craft::$app->getRequest()->getBodyParam('matchStrategy', 'exactMatch');

        $redirect->enabled = Craft::$app->getRequest()->getBodyParam('enabled');

        SproutBase::$app->redirects->remove404RedirectIfExists($redirect);

        if (!Craft::$app->elements->saveElement($redirect)) {
            Craft::$app->getSession()->setError(Craft::t('sprout', 'Couldn’t save redirect.'));

            // Send the event back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'redirect' => $redirect
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout', 'Redirect saved.'));

        return $this->redirectToPostedUrl($redirect);
    }

    /**
     * Deletes a Redirect
     *
     * @throws BadRequestHttpException
     * @throws Throwable
     */
    public function actionDeleteRedirect()
    {
        $this->requirePostRequest();
        $this->requirePermission('sprout:redirects:editRedirects');

        $redirectId = Craft::$app->getRequest()->getRequiredBodyParam('redirectId');
        $siteId = Craft::$app->getRequest()->getRequiredBodyParam('siteId');

        $element = Craft::$app->elements->getElementById($redirectId, Redirect::class, $siteId);

        if ($element && Craft::$app->elements->deleteElement($element, true)) {
            Craft::$app->getSession()->setNotice(Craft::t('sprout', 'Redirect deleted.'));

            return $this->redirectToPostedUrl();
        }

        Craft::$app->getSession()->setError(Craft::t('sprout', 'Couldn’t delete redirect.'));

        Craft::$app->getUrlManager()->setRouteParams([
            'redirect' => $element
        ]);

        return null;
    }
}
