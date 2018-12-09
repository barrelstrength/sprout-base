<?php

namespace barrelstrength\sproutemail\controllers;

use barrelstrength\sproutbase\app\email\base\EmailElement;
use craft\web\Controller;
use Craft;
use yii\base\InvalidArgumentException;
use yii\web\Response;

class MailersController extends Controller
{
    /**
     * Provides a way for mailers to render content to perform actions inside a a modal window
     *
     * @return Response
     * @throws \Throwable
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionGetPrepareModal(): Response
    {
        $this->requirePostRequest();

        $emailId = Craft::$app->getRequest()->getBodyParam('emailId');

        /** @var EmailElement $email */
        $email = Craft::$app->getElements()->getElementById($emailId);

        if (!$email) {
            throw new InvalidArgumentException(Craft::t('sprout-base', 'No Email exists with the ID “{id}”.', [
                'id' => $emailId
            ]));
        }

        $mailer = $email->getMailer();
        $modal = $mailer->getPrepareModal($email);

        return $this->asJson($modal->getAttributes());
    }
}
