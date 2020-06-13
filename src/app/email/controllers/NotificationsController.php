<?php

namespace barrelstrength\sproutbase\app\email\controllers;

use barrelstrength\sproutbase\app\email\base\Mailer;
use barrelstrength\sproutbase\app\email\base\NotificationEmailSenderInterface;
use barrelstrength\sproutbase\app\email\base\NotificationEvent;
use barrelstrength\sproutbase\app\email\elements\NotificationEmail;
use barrelstrength\sproutbase\app\email\emailtemplates\BasicTemplates;
use barrelstrength\sproutbase\app\email\mailers\DefaultMailer;
use barrelstrength\sproutbase\app\email\models\ModalResponse;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Plugin;
use craft\base\PluginInterface;
use craft\errors\MissingComponentException;
use craft\helpers\ElementHelper;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use InvalidArgumentException;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use yii\base\ExitException;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\Response;

class NotificationsController extends Controller
{
    private $isSproutEmailPro;

    public function init()
    {
        $this->isSproutEmailPro = SproutBase::$app->config->isEdition('sprout-email', Config::EDITION_PRO);
    }

    /**
     * @return Response
     * @throws ForbiddenHttpException
     */
    public function actionNotificationsIndexTemplate(): Response
    {
        $this->requirePermission('sprout:email:viewNotifications');

        return $this->renderTemplate('sprout/email/notifications/index', [
            'isPro' => $this->isSproutEmailPro
        ]);
    }

    /**
     * @param null                   $emailId
     * @param NotificationEmail|null $notificationEmail
     *
     * @return Response
     * @throws ForbiddenHttpException
     */
    public function actionEditNotificationEmailSettingsTemplate($emailId = null, NotificationEmail $notificationEmail = null): Response
    {
        $this->requireAdmin();

        $isNewNotificationEmail = $emailId !== null && $emailId === 'new';

        if (!$notificationEmail) {
            if ($isNewNotificationEmail) {
                $notificationEmail = new NotificationEmail();
            } else {
                $notificationEmail = Craft::$app->getElements()->getElementById($emailId, NotificationEmail::class);
            }
        }

        return $this->renderTemplate('sprout/email/notifications/_editFieldLayout', [
            'emailId' => $emailId,
            'notificationEmail' => $notificationEmail,
            'isNewNotificationEmail' => $isNewNotificationEmail,
            'isPro' => $this->isSproutEmailPro,
        ]);
    }

    /**
     * @param null                   $emailId
     * @param NotificationEmail|null $notificationEmail
     *
     * @return Response
     * @throws Exception
     * @throws Throwable
     * @throws InvalidConfigException
     * @throws ForbiddenHttpException
     */
    public function actionEditNotificationEmailTemplate($emailId = null, NotificationEmail $notificationEmail = null): Response
    {
        $this->requirePermission('sprout:email:editNotifications');

        $routeParams = Craft::$app->getUrlManager()->getRouteParams();

        // Immediately create a new Notification
        if ($emailId === 'new') {
            $notificationEmail = SproutBase::$app->notifications->createNewNotification();

            if ($notificationEmail) {
                $url = UrlHelper::cpUrl('sprout/notifications/edit/'.$notificationEmail->id);

                return $this->redirect($url);
            }

            throw new Exception('Unable to create Notification Email');
        }

        if (!$notificationEmail) {
            $notificationEmail = Craft::$app->getElements()->getElementById($emailId, NotificationEmail::class);
        }

        // Sort out Live Preview and Share button behaviors
        $showPreviewBtn = false;
        $shareUrl = null;

        $isMobileBrowser = Craft::$app->getRequest()->isMobileBrowser(true);

        if (!$isMobileBrowser) {
            $showPreviewBtn = true;

            $this->getView()->registerJs('Craft.LivePreview.init('.Json::encode([
                    'fields' => '#subjectLine-field, #defaultBody-field, #fields > div > div > .field',
                    'extraFields' => '#settings',
                    'previewUrl' => $notificationEmail->getUrl(),
                    'previewAction' => Craft::$app->getSecurity()->hashData('sprout/notifications/live-preview-notification-email'),
                    'previewParams' => [
                        'notificationId' => $notificationEmail->id,
                    ]
                ]).');');

            if ($notificationEmail->id && $notificationEmail->getUrl()) {
                $shareUrl = UrlHelper::actionUrl('sprout/notifications/share-notification-email', [
                    'notificationId' => $notificationEmail->id,
                ]);
            }
        }

        $events = SproutBase::$app->notificationEvents->getNotificationEmailEvents($notificationEmail);

        $defaultEmailTemplateId = BasicTemplates::class;

        // Set a default template if we don't have one set
        if (!$notificationEmail->emailTemplateId) {
            $notificationEmail->emailTemplateId = $defaultEmailTemplateId;
        }

        $tabs = [
            [
                'label' => 'Message',
                'url' => '#tab1',
                'class' => null,
            ]
        ];

        $tabs = $notificationEmail->getFieldLayoutTabs() ?: $tabs;

        return $this->renderTemplate('sprout/email/notifications/_edit', [
            'notificationEmail' => $notificationEmail,
            'events' => $events,
            'tabs' => $tabs,
            'showPreviewBtn' => $showPreviewBtn,
            'shareUrl' => $shareUrl,
            'isPro' => $this->isSproutEmailPro
        ]);
    }

    /**
     * @param null $emailId
     *
     * @return Response
     * @throws ForbiddenHttpException
     */
    public function actionPreview($emailId = null): Response
    {
        $this->requirePermission('sprout:email:viewNotifications');

        $email = Craft::$app->getElements()->getElementById($emailId, NotificationEmail::class);

        return $this->renderTemplate('sprout/email/_preview/preview-body', [
            'email' => $email,
            'emailId' => $emailId,
            'htmlBody' => null,
            'body' => null
        ]);
    }

    /**
     * Save a Notification Email from the Notification Email template
     *
     * @return Response|null
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws ForbiddenHttpException
     * @throws MissingComponentException
     * @throws Throwable
     */
    public function actionSaveNotificationEmail()
    {
        $this->requirePostRequest();
        $this->requirePermission('sprout:email:editNotifications');

        $notificationEmail = new NotificationEmail();

        $notificationEmail->id = Craft::$app->getRequest()->getBodyParam('emailId');

        if ($notificationEmail->id) {
            $notificationEmail = Craft::$app->getElements()->getElementById($notificationEmail->id, NotificationEmail::class);
        }

        $notificationEmail->subjectLine = Craft::$app->getRequest()->getRequiredBodyParam('subjectLine');
        $notificationEmail->defaultBody = Craft::$app->getRequest()->getBodyParam('defaultBody');
        $notificationEmail->fromName = Craft::$app->getRequest()->getRequiredBodyParam('fromName');
        $notificationEmail->fromEmail = Craft::$app->getRequest()->getRequiredBodyParam('fromEmail');
        $notificationEmail->replyToEmail = Craft::$app->getRequest()->getBodyParam('replyToEmail');
        $notificationEmail->titleFormat = Craft::$app->getRequest()->getBodyParam('titleFormat');
        $notificationEmail->slug = Craft::$app->getRequest()->getBodyParam('slug');
        $notificationEmail->sendMethod = Craft::$app->getRequest()->getBodyParam('sendMethod');
        $notificationEmail->enableFileAttachments = Craft::$app->getRequest()->getBodyParam('enableFileAttachments');
        $notificationEmail->enabled = Craft::$app->getRequest()->getBodyParam('enabled');
        $notificationEmail->eventId = Craft::$app->getRequest()->getBodyParam('eventId');
        $notificationEmail->recipients = Craft::$app->getRequest()->getBodyParam('recipients');
        $notificationEmail->cc = Craft::$app->getRequest()->getBodyParam('cc');
        $notificationEmail->bcc = Craft::$app->getRequest()->getBodyParam('bcc');
        $notificationEmail->listSettings = Craft::$app->getRequest()->getBodyParam('lists');
        $notificationEmail->emailTemplateId = Craft::$app->getRequest()->getBodyParam('emailTemplateId');
        $notificationEmail->sendRule = Craft::$app->getRequest()->getRequiredBodyParam('sendRule');

        if (!$notificationEmail->replyToEmail) {
            $notificationEmail->replyToEmail = $notificationEmail->fromEmail;
        }

        if ($notificationEmail->slug === null) {
            $notificationEmail->slug = ElementHelper::createSlug($notificationEmail->subjectLine);
        }

        $fieldsLocation = Craft::$app->getRequest()->getBodyParam('fieldsLocation', 'fields');

        $notificationEmail->setFieldValuesFromRequest($fieldsLocation);

        $notificationEmail->title = $notificationEmail->subjectLine;

        if ($notificationEmail->titleFormat) {
            $notificationEmail->title = Craft::$app->getView()->renderObjectTemplate($notificationEmail->titleFormat, $notificationEmail);
        }

        $event = null;

        if ($notificationEmail->eventId) {
            /** @var NotificationEvent $event */
            $event = SproutBase::$app->notificationEvents->getEventById($notificationEmail->eventId);
        }

        if ($event) {
            $eventSettings = Craft::$app->getRequest()->getBodyParam('eventSettings');

            if (isset($eventSettings[$notificationEmail->eventId])) {
                $eventSettings = $eventSettings[$notificationEmail->eventId];

                $notificationEmail->settings = Json::encode($eventSettings);
            }

            $notificationEmail->setEventObject($event->getMockEventObject());

            if ($event->getSettingsHtml() === null || $event->getSettingsHtml() == '') {
                $notificationEmail->settings = null;
            }
        }

        // Get cp path cause template validation change current template path
        $oldPath = Craft::$app->getView()->getTemplatesPath();

        // @todo - disable template validations due to errors on clean installations
        //  - Should we block the save action if templates don't validate? Can we know for sure?
        // $validateTemplate = $this->validateTemplate($notificationEmail);

        if (!SproutBase::$app->notifications->saveNotification($notificationEmail)) {

            Craft::$app->getSession()->setError(Craft::t('sprout', 'Unable to save notification.'));

            // Set the previous cp path to avoid not found template when showing errors
            Craft::$app->getView()->setTemplatesPath($oldPath);

            Craft::$app->getUrlManager()->setRouteParams([
                'notificationEmail' => $notificationEmail
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout', 'Notification saved.'));

        return $this->redirectToPostedUrl();
    }

    /**
     * Save a Notification Email from the Notification Email Settings template
     *
     * @return null
     * @throws \Exception
     * @throws Throwable
     * @throws Exception
     * @throws BadRequestHttpException
     */
    public function actionSaveNotificationEmailSettings()
    {
        $this->requirePostRequest();
        $this->requireAdmin();

        $notificationEmail = new NotificationEmail();

        $notificationEmail->id = Craft::$app->getRequest()->getBodyParam('emailId');

        if ($notificationEmail->id) {
            $notificationEmail = Craft::$app->getElements()->getElementById($notificationEmail->id, NotificationEmail::class);
        }

        // Set the field layout
        $fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();
        $fieldLayout->type = NotificationEmail::class;

        $notificationEmail->setFieldLayout($fieldLayout);

        if (!SproutBase::$app->notifications->saveNotification($notificationEmail)) {

            Craft::$app->getSession()->setError(Craft::t('sprout', 'Unable to save notification.'));

//            $errorMessage = $this->formatErrors();
//            Craft::error($errorMessage, __METHOD__);

            Craft::$app->getUrlManager()->setRouteParams([
                'notificationEmail' => $notificationEmail
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout', 'Notification saved.'));

        return $this->redirectToPostedUrl($notificationEmail);
    }

    /**
     * Delete a Notification Email
     *
     * @return bool|Response
     * @throws Throwable
     * @throws BadRequestHttpException
     */
    public function actionDeleteNotificationEmail()
    {
        $this->requirePostRequest();
        $this->requirePermission('sprout:email:editNotifications');

        $notificationEmailId = Craft::$app->getRequest()->getBodyParam('emailId');

        /** @var NotificationEmail $notificationEmail */
        $notificationEmail = Craft::$app->getElements()->getElementById($notificationEmailId, NotificationEmail::class);

        if (!$notificationEmail) {
            throw new InvalidArgumentException('No Notification Email exists with the ID: '.$notificationEmailId);
        }

        if (!SproutBase::$app->notifications->deleteNotificationEmailById($notificationEmailId)) {

            if (Craft::$app->getRequest()->getIsAjax()) {
                return $this->asJson(['success' => false]);
            }

            Craft::info(Json::encode($notificationEmail->getErrors()), __METHOD__);

            Craft::$app->getSession()->setNotice(Craft::t('sprout', 'Couldn’t delete notification.'));

            // Send the entry back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'notificationEmail' => $notificationEmail
            ]);

            return false;
        }

        if (Craft::$app->getRequest()->getIsAjax()) {
            return $this->asJson(['success' => true]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout', 'Notification deleted.'));

        return $this->redirectToPostedUrl();
    }

    /**
     * Send a notification email via a Mailer
     *
     * @return Response
     * @throws Exception
     * @throws Throwable
     * @throws BadRequestHttpException
     */
    public function actionSendTestNotificationEmail(): Response
    {
        $this->requirePostRequest();
        $this->requirePermission('sprout:email:editNotifications');

        $notificationId = Craft::$app->getRequest()->getBodyParam('notificationId');
        $recipients = Craft::$app->getRequest()->getBodyParam('recipients');

        /** @var NotificationEmail $notificationEmail */
        $notificationEmail = Craft::$app->getElements()->getElementById($notificationId, NotificationEmail::class);
        $notificationEmail->setIsTest();

        if (empty(trim($recipients))) {
            return $this->asJson(
                ModalResponse::createErrorModalResponse('sprout/email/_modals/response', [
                    'email' => $notificationEmail,
                    'message' => Craft::t('sprout', 'Add at least one recipient.')
                ])
            );
        }

        $notificationEmail->title = $notificationEmail->subjectLine;

        $event = SproutBase::$app->notificationEvents->getEvent($notificationEmail);

        /** @var Mailer|NotificationEmailSenderInterface $mailer */
        $mailer = SproutBase::$app->mailers->getMailerByName(DefaultMailer::class);

        if (!$event) {
            return $this->asJson(
                ModalResponse::createErrorModalResponse('sprout/email/_modals/response', [
                    'email' => $notificationEmail,
                    'message' => Craft::t('sprout', 'Unable to find Notification Email event.')
                ])
            );
        }

        $notificationEmail->setEventObject($event->getMockEventObject());

        // We need to set recipients but it will be overridden with the
        // onTheFlyRecipients. In this test use case, they are both the same.
        $notificationEmail->recipients = $recipients;
        $onTheFlyRecipients = array_map('trim', explode(',', $recipients));
        $mailer->setOnTheFlyRecipients($onTheFlyRecipients);
        $recipientList = $mailer->getRecipientList($notificationEmail);

        if ($recipientList->getInvalidRecipients()) {
            $invalidEmails = [];
            foreach ($recipientList->getInvalidRecipients() as $invalidRecipient) {
                $invalidEmails[] = $invalidRecipient->email;
            }

            return $this->asJson(
                ModalResponse::createErrorModalResponse('sprout/email/_modals/response', [
                    'email' => $notificationEmail,
                    'message' => Craft::t('sprout', 'Recipient email addresses do not validate: {invalidEmails}', [
                        'invalidEmails' => implode(', ', $invalidEmails)
                    ])
                ])
            );
        }

        try {
            if (!$mailer->sendNotificationEmail($notificationEmail)) {
                return $this->asJson(
                    ModalResponse::createErrorModalResponse('sprout/email/_modals/response', [
                        'email' => $notificationEmail,
                        'message' => Craft::t('sprout', 'Unable to send Test Notification Email')
                    ])
                );
            }
        } catch (\Exception $exception) {
            return $this->asJson(
                ModalResponse::createErrorModalResponse('sprout/email/_modals/response', [
                    'email' => $notificationEmail,
                    'message' => $exception->getMessage()
                ])
            );
        }


        return $this->asJson(
            ModalResponse::createModalResponse('sprout/email/_modals/response', [
                'email' => $notificationEmail,
                'message' => Craft::t('sprout', 'Test Notification Email sent.')
            ])
        );
    }

    /**
     * Prepares a Notification Email to be shared via token-based URL
     *
     * @param null $notificationId
     *
     * @return Response
     * @throws Exception
     * @throws HttpException
     */
    public function actionShareNotificationEmail($notificationId = null): Response
    {
        if ($notificationId) {
            $notificationEmail = Craft::$app->getElements()->getElementById($notificationId);

            if (!$notificationEmail) {
                throw new HttpException(404);
            }

            $type = Craft::$app->getRequest()->getQueryParam('type');

            $params = [
                'notificationId' => $notificationId,
                'type' => $type
            ];
        } else {
            throw new HttpException(404);
        }

        // Create the token and redirect to the entry URL with the token in place
        $token = Craft::$app->getTokens()->createToken([
                'sprout/notifications/view-shared-notification-email',
                $params
            ]
        );

        $url = UrlHelper::urlWithToken($notificationEmail->getUrl(), $token);

        return $this->redirect($url);
    }

    /**
     * Renders a shared Notification Email
     *
     * @param null $notificationId
     * @param null $type
     *
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws ExitException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function actionViewSharedNotificationEmail($notificationId = null, $type = null)
    {
        $this->requireToken();

        SproutBase::$app->notifications->getPreviewNotificationEmailById($notificationId, $type);
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
        $notificationId = Craft::$app->getRequest()->getBodyParam('notificationId');

        SproutBase::$app->notifications->getPreviewNotificationEmailById($notificationId);
    }

    /**
     * @return string
     */
    public function formatErrors(): string
    {
        $errors = $this->getErrors();

        $text = '';
        if (!empty($errors)) {
            $text .= '<ul>';
            foreach ($errors as $key => $error) {
                if (is_array($error)) {
                    foreach ($error as $desc) {
                        $text .= '<li>'.$desc.'</li>';
                    }
                }
            }
            $text .= '</ul>';
        }

        return $text;
    }

    /**
     * @param NotificationEmail $notificationEmail
     *
     * @return bool
     */
    private function validateTemplate(NotificationEmail $notificationEmail): bool
    {
        try {
            $notificationEmail->getEmailTemplates()->getTextBody();
            $notificationEmail->getEmailTemplates()->getHtmlBody();
        } catch (\Exception $e) {
            $errorMessage = 'Dynamic variables on your template does not exist. '.$e->getMessage();
            $notificationEmail->addError('emailTemplateId', $errorMessage);

            // @todo add template errors to notificationEmail model
            // Don't use utilities class
            // SproutBase::$app->utilities->addError('template', $errorMessage);

            return false;
        }

        return true;
    }
}
