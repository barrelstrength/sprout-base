<?php

namespace barrelstrength\sproutbase\app\email\controllers;

use barrelstrength\sproutbase\app\email\base\EmailElement;
use barrelstrength\sproutbase\app\email\elements\NotificationEmail;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\errors\ElementNotFoundException;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use yii\base\ExitException;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\Response;

class PreviewController extends Controller
{
    /**
     * @param int $emailId
     *
     * @return Response
     * @throws HttpException
     */
    public function actionPreview(int $emailId = null): Response
    {
        if (!$emailId) {
            throw new HttpException(404);
        }

        $email = Craft::$app->getElements()->getElementById($emailId);

        if (!$email) {
            throw new HttpException(404);
        }

        $this->requirePermission($email->getPreviewPermission());

        $previewTemplate = 'sprout/notifications/_preview/preview-'.$email->getPreviewType();

        return $this->renderTemplate($previewTemplate, [
            'email' => $email,
            'emailId' => $emailId,
        ]);
    }

    /**
     * Prepares a Notification Email to be shared via token-based URL
     *
     * @param int|null $emailId
     *
     * @return Response
     * @throws HttpException
     */
    public function actionShareEmail(int $emailId): Response
    {
        $email = Craft::$app->getElements()->getElementById($emailId);

        if (!$email) {
            throw new HttpException(404);
        }

        $type = Craft::$app->getRequest()->getQueryParam('type');

        // Create the token and redirect to the entry URL with the token in place
        $token = Craft::$app->getTokens()->createToken([
            'sprout/email-preview/view-shared-email', [
                'emailId' => $emailId,
                'type' => $type,
            ],
        ]);

        $url = UrlHelper::urlWithToken($email->getUrl(), $token);

        return $this->redirect($url);
    }

    /**
     * Renders a shared Notification Email
     *
     * @param null $emailId
     * @param null $type
     *
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws ExitException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function actionViewSharedEmail($emailId = null, $type = null)
    {
        $this->requireToken();

        $this->getPreviewEmailById($emailId, $type);
    }

    /**
     * Renders a Notification Email for Live Preview
     *
     * @throws Exception
     * @throws ExitException
     * @throws LoaderError
     * @throws SyntaxError
     * @throws RuntimeError
     */
    public function actionLivePreviewNotificationEmail()
    {
        $emailId = Craft::$app->getRequest()->getBodyParam('emailId');

        $this->getPreviewEmailById($emailId);
    }

    /**
     * Retrieves a rendered Notification Email to be shared or for Live Preview
     *
     * @param      $emailId
     * @param null $type
     *
     * @throws ExitException
     * @throws LoaderError
     * @throws SyntaxError
     * @throws Exception
     * @throws RuntimeError
     */
    protected function getPreviewEmailById($emailId, $type = null)
    {
        $email = Craft::$app->getElements()->getElementById($emailId);

        if (!$email instanceof EmailElement) {
            throw new ElementNotFoundException('Email not found using id '.$emailId);
        }

        $email->preparePreviewEmailElement($email);

        // The getBodyParam is for livePreviewNotification to update on change
        $subjectLine = Craft::$app->getRequest()->getBodyParam('subjectLine');
        $defaultBody = Craft::$app->getRequest()->getBodyParam('defaultBody');

        if ($subjectLine) {
            $email->subjectLine = $subjectLine;
        }

        if ($defaultBody) {
            $email->defaultBody = $defaultBody;
        }

        $fieldsLocation = Craft::$app->getRequest()->getParam('fieldsLocation', 'fields');

        $email->setFieldValuesFromRequest($fieldsLocation);

        $fileExtension = $type === 'text' ? 'txt' : 'html';

        $this->showPreviewEmail($email, $fileExtension);
    }

    /**
     * @param EmailElement $email
     * @param string $fileExtension
     *
     * @throws ExitException
     * @throws LoaderError
     * @throws SyntaxError
     * @throws Exception
     * @throws RuntimeError
     */
    protected function showPreviewEmail(EmailElement $email, $fileExtension = 'html')
    {
        if ($email instanceof NotificationEmail &&
            $event = SproutBase::$app->notificationEvents->getEvent($email)) {
            $email->setEventObject($event->getMockEventObject());
        }

        if ($fileExtension == 'txt') {
            $output = $email->getEmailTemplates()->getTextBody();
        } else {
            $output = $email->getEmailTemplates()->getHtmlBody();
        }

        // Output it into a buffer, in case TasksService
        // wants to close the connection prematurely
        ob_start();
        echo $output;
        Craft::$app->end();
    }
}
