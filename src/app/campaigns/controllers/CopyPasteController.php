<?php

namespace barrelstrength\sproutbase\app\campaigns\controllers;

use barrelstrength\sproutbase\app\campaigns\elements\CampaignEmail;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\helpers\DateTimeHelper;
use craft\web\Controller;
use Throwable;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class CopyPasteController extends Controller
{
    /**
     * Updates a Copy/Paste Campaign Email to add a Date Sent
     *
     * @return Response
     * @throws \Exception
     * @throws Throwable
     * @throws Exception
     * @throws BadRequestHttpException
     */
    public function actionMarkSent(): Response
    {
        $this->requirePostRequest();

        $emailId = Craft::$app->getRequest()->getBodyParam('emailId');

        /** @var  $campaignEmail CampaignEmail */
        $campaignEmail = SproutBase::$app->campaignEmails->getCampaignEmailById($emailId);

        $campaignEmail->dateSent = DateTimeHelper::currentUTCDateTime();

        if (SproutBase::$app->campaignEmails->saveCampaignEmail($campaignEmail)) {
            $html = Craft::$app->getView()->renderTemplate('sprout/notifications/_modals/response', [
                'success' => true,
                'email' => $campaignEmail,
                'message' => Craft::t('sprout', 'Email marked as sent.')
            ]);

            return $this->asJson([
                'success' => true,
                'content' => $html
            ]);
        }

        $html = Craft::$app->getView()->renderTemplate('sprout/notifications/_modals/response', [
            'success' => true,
            'email' => $campaignEmail,
            'message' => Craft::t('sprout', 'Unable to mark email as sent.')
        ]);

        return $this->asJson([
            'success' => true,
            'content' => $html
        ]);
    }
}
