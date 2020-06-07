<?php

namespace barrelstrength\sproutbase\app\campaigns\controllers;

use barrelstrength\sproutbase\app\campaigns\elements\CampaignEmail;
use barrelstrength\sproutbase\app\campaigns\models\CampaignType;
use barrelstrength\sproutbase\app\email\base\Mailer;
use barrelstrength\sproutbase\app\email\mailers\DefaultMailer;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\errors\MissingComponentException;
use craft\errors\SiteNotFoundException;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use yii\base\InvalidArgumentException;
use yii\db\Exception;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class CampaignTypeController extends Controller
{
    /**
     * Renders a Campaign Type settings template
     *
     * @param                   $campaignTypeId
     * @param CampaignType|null $campaignType
     *
     * @return Response
     * @throws \Exception
     */
    public function actionEditCampaignType($campaignTypeId, CampaignType $campaignType = null): Response
    {
        if ($campaignTypeId && $campaignType === null) {

            if ($campaignTypeId == 'new') {
                $campaignType = new CampaignType();
            } else {
                $campaignType = SproutBase::$app->campaignTypes->getCampaignTypeById($campaignTypeId);

                if ($campaignType->id === null) {
                    throw new InvalidArgumentException('Invalid campaign type id');
                }
            }
        }

        $mailerOptions = [];

        $mailers = SproutBase::$app->mailers->getRegisteredMailers();

        if (!empty($mailers)) {
            foreach ($mailers as $key => $mailer) {
                /**
                 * @var $mailer Mailer
                 */
                $mailerOptions[$key]['value'] = get_class($mailer);
                $mailerOptions[$key]['label'] = $mailer::displayName();
            }
        }

        // Disable default mailer on campaign emails
        unset($mailerOptions[DefaultMailer::class]);

        // Load our template
        return $this->renderTemplate('sprout-base-campaigns/settings/campaign-types/_edit', [
            'mailers' => $mailerOptions,
            'campaignTypeId' => $campaignTypeId,
            'campaignType' => $campaignType
        ]);
    }

    /**
     * Saves a Campaign Type
     *
     * @throws \Exception
     * @throws SiteNotFoundException
     * @throws \yii\base\Exception
     * @throws Exception
     * @throws BadRequestHttpException
     */
    public function actionSaveCampaignType(): Response
    {
        $this->requirePostRequest();

        $campaignTypeId = Craft::$app->getRequest()->getBodyParam('campaignTypeId');
        $campaignType = SproutBase::$app->campaignTypes->getCampaignTypeById($campaignTypeId);

        $campaignType->setAttributes(Craft::$app->getRequest()->getBodyParam('sproutCampaign'), false);

        // Set the field layout
        $fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();

        $fieldLayout->type = CampaignEmail::class;

        $campaignType->setFieldLayout($fieldLayout);

        if (!SproutBase::$app->campaignTypes->saveCampaignType($campaignType)) {
            Craft::$app->getSession()->setError(Craft::t('sprout', 'Unable to save campaign.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'campaignType' => $campaignType
            ]);

            $this->redirectToPostedUrl();
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout', 'Campaign saved.'));

        $url = UrlHelper::cpUrl('sprout/settings/campaigns/campaigntypes/edit/'.$campaignType->id);

        return $this->redirect($url);
    }

    /**
     * Deletes a Campaign Type
     *
     * @return Response
     * @throws MissingComponentException
     * @throws BadRequestHttpException
     */
    public function actionDeleteCampaignType(): Response
    {
        $this->requirePostRequest();

        $campaignTypeId = Craft::$app->getRequest()->getBodyParam('id');

        $session = Craft::$app->getSession();

        if ($session and $result = SproutBase::$app->campaignTypes->deleteCampaignType($campaignTypeId)) {
            $session->setNotice(Craft::t('sprout', 'Campaign Type deleted.'));

            return $this->asJson([
                'success' => true
            ]);
        }

        $session->setError(Craft::t('sprout', "Couldn't delete Campaign."));

        return $this->asJson([
            'success' => false
        ]);
    }
}
