<?php

namespace barrelstrength\sproutbase\app\email\controllers;

use barrelstrength\sproutbase\app\email\base\EmailTemplates;
use barrelstrength\sproutbase\app\email\emailtemplates\BasicTemplates;
use barrelstrength\sproutbase\app\email\mailers\DefaultMailer;
use barrelstrength\sproutbase\app\email\models\Response;
use barrelstrength\sproutbase\app\email\elements\NotificationEmail;
use barrelstrength\sproutbase\SproutBase;
use craft\helpers\ElementHelper;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use Craft;
use craft\base\Plugin;

use yii\base\Exception;
use yii\web\HttpException;

/**
 * Class NotificationsController
 *
 * @package barrelstrength\sproutbase\controllers
 */
class NotificationsController extends Controller
{
    private $currentPluginHandle;

    public function init()
    {
        parent::init();

        $currentPluginHandle = Craft::$app->request->getSegment(1);

        $this->currentPluginHandle = $currentPluginHandle;
    }

    /**
     * @param null                   $emailId
     * @param NotificationEmail|null $notificationEmail
     *
     * @return \yii\web\Response
     */
    public function actionEditNotificationEmailSettingsTemplate($emailId = null, NotificationEmail $notificationEmail = null)
    {
        $currentUser = Craft::$app->getUser()->getIdentity();

        if (!$currentUser->can('editSproutEmailSettings')) {
            return $this->redirect($this->currentPluginHandle);
        }

        $isNewNotificationEmail = $emailId !== null && $emailId === 'new';

        if (!$notificationEmail) {
            if ($isNewNotificationEmail) {
                $notificationEmail = new NotificationEmail();
            } else {
                $notificationEmail = SproutBase::$app->notifications->getNotificationEmailById($emailId);
            }
        }

        return $this->renderTemplate('sprout-base-email/notifications/_editFieldLayout', [
            'emailId' => $emailId,
            'notificationEmail' => $notificationEmail,
            'isNewNotificationEmail' => $isNewNotificationEmail
        ]);
    }

    /**
     * @param null                                    $emailId
     * @param NotificationEmail|null $notificationEmail
     *
     * @return \yii\web\Response
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\base\Exception
     */
    public function actionEditNotificationEmailTemplate($emailId = null, NotificationEmail $notificationEmail = null)
    {
        $routeParams = Craft::$app->getUrlManager()->getRouteParams();

        // Our currentPluginHandle helps us allow notifications to be managed in other plugins
        $currentPluginHandle = Craft::$app->request->getSegment(1);

        // Immediately create a new Notification
        if (Craft::$app->request->getSegment(4) == 'new') {
            $notificationEmail = SproutBase::$app->notifications->createNewNotification();

            if ($notificationEmail) {
                $url = UrlHelper::cpUrl($currentPluginHandle.'/notifications/edit/'.$notificationEmail->id);
                return $this->redirect($url);
            } else {
                throw new Exception(Craft::t('sprout-base', 'Unable to create Notification Email'));
            }
        }

        if (!$notificationEmail) {
            $notificationEmail = SproutBase::$app->notifications->getNotificationEmailById($emailId);
        }

        // Sort out Live Preview and Share button behaviors
        $showPreviewBtn = false;
        $shareUrl = null;

        $isMobileBrowser = Craft::$app->getRequest()->isMobileBrowser(true);

        $isSproutEmailInstalled = Craft::$app->plugins->getPlugin('sprout-email');

        if (!$isMobileBrowser && $isSproutEmailInstalled) {
            $showPreviewBtn = true;

            Craft::$app->getView()->registerJs(
                'Craft.LivePreview.init('.Json::encode(
                    [
                        'fields' => '#subjectLine-field, #body-field, #title-field, #fields > div > .field',
                        'extraFields' => '#settings',
                        'previewUrl' => $notificationEmail->getUrl(),
                        'previewAction' => 'sprout-base/notifications/live-preview-notification-email',
                        'previewParams' => [
                            'notificationId' => $notificationEmail->id,
                        ]
                    ]
                ).');'
            );

            if ($notificationEmail->id && $notificationEmail->getUrl()) {
                $shareUrl = UrlHelper::actionUrl('sprout-base/notifications/share-notification-email', [
                    'notificationId' => $notificationEmail->id,
                ]);
            }
        }

        $events = SproutBase::$app->notificationEvents->getNotificationEmailEvents($notificationEmail);

        $defaultEmailTemplate = BasicTemplates::class;

        if ($currentPluginHandle !== 'sprout-email') {
            $events = SproutBase::$app->notificationEvents->getNotificationEmailEventsByPluginHandle($notificationEmail, $currentPluginHandle);

            if (new $routeParams['defaultEmailTemplate'] instanceof EmailTemplates)
            {
                $defaultEmailTemplate = $routeParams['defaultEmailTemplate'];
            }
        }

        // Set a default template if we don't have one set
        if (!$notificationEmail->emailTemplateId)
        {
            $notificationEmail->emailTemplateId = $defaultEmailTemplate;
        }

        $lists = [];

        return $this->renderTemplate('sprout-base-email/notifications/_edit', [
            'notificationEmail' => $notificationEmail,
            'events' => $events,
            'lists' => $lists,
            'tabs' => $notificationEmail->getFieldLayoutTabs(),
            'showPreviewBtn' => $showPreviewBtn,
            'shareUrl' => $shareUrl
        ]);
    }

    /**
     * Save a Notification Email from the Notification Email template
     *
     * @return null|\yii\web\Response
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveNotificationEmail()
    {
        $this->requirePostRequest();

        $notificationEmail = new NotificationEmail();

        $notificationEmail->id = Craft::$app->getRequest()->getBodyParam('emailId');

        if ($notificationEmail->id) {
            $notificationEmail = SproutBase::$app->notifications->getNotificationEmailById($notificationEmail->id);
        }

        $notificationEmail->subjectLine = Craft::$app->getRequest()->getRequiredBodyParam('subjectLine');
        $notificationEmail->defaultBody = Craft::$app->getRequest()->getBodyParam('defaultBody');
        $notificationEmail->fromName = Craft::$app->getRequest()->getRequiredBodyParam('fromName');
        $notificationEmail->fromEmail = Craft::$app->getRequest()->getRequiredBodyParam('fromEmail');
        $notificationEmail->replyToEmail = Craft::$app->getRequest()->getBodyParam('replyToEmail');
        $notificationEmail->titleFormat = Craft::$app->getRequest()->getBodyParam('titleFormat');
        $notificationEmail->slug = Craft::$app->getRequest()->getBodyParam('slug');
        $notificationEmail->enableFileAttachments = Craft::$app->getRequest()->getBodyParam('enableFileAttachments');
        $notificationEmail->enabled = Craft::$app->getRequest()->getBodyParam('enabled');
        $notificationEmail->eventId = Craft::$app->getRequest()->getBodyParam('eventId');
        $notificationEmail->recipients = Craft::$app->getRequest()->getBodyParam('recipients');
        $notificationEmail->listSettings = Craft::$app->getRequest()->getBodyParam('lists');
        $notificationEmail->emailTemplateId = Craft::$app->getRequest()->getBodyParam('emailTemplateId');

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

        $event = SproutBase::$app->notificationEvents->getEventById($notificationEmail->eventId);

        if ($event) {

            $eventSettings = Craft::$app->getRequest()->getBodyParam('eventSettings');

            if (isset($eventSettings[$notificationEmail->eventId])) {
                $eventSettings = $eventSettings[$notificationEmail->eventId];

                $notificationEmail->settings = Json::encode($eventSettings);
            }

            /**
             * @var $plugin Plugin
             */
            $plugin = $event->getPlugin();

            if ($plugin) {
                $notificationEmail->pluginHandle = $plugin->id;
            }
        }

        if (!SproutBase::$app->notifications->saveNotification($notificationEmail)) {

            Craft::$app->getSession()->setError(Craft::t('sprout-base', 'Unable to save notification.'));

            $errorMessage = SproutBase::$app->utilities->formatErrors();

            SproutBase::error($errorMessage);

            return Craft::$app->getUrlManager()->setRouteParams([
                'notificationEmail' => $notificationEmail
            ]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout-base', 'Notification saved.'));

        return $this->redirectToPostedUrl();
    }

    /**
     * Save a Notification Email from the Notification Email Settings template
     *
     * @return null
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveNotificationEmailSettings()
    {
        $this->requirePostRequest();

        $notificationEmail = new NotificationEmail();

        $notificationEmail->id = Craft::$app->getRequest()->getBodyParam('emailId');

        if ($notificationEmail->id) {
            $notificationEmail = SproutBase::$app->notifications->getNotificationEmailById($notificationEmail->id);
        }

        // Set the field layout
        $fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();
        $fieldLayout->type = NotificationEmail::class;
        
        $notificationEmail->setFieldLayout($fieldLayout);

        if (!SproutBase::$app->notifications->saveNotification($notificationEmail)) {

            Craft::$app->getSession()->setError(Craft::t('sprout-base', 'Unable to save notification.'));

            $errorMessage = SproutBase::$app->utilities->formatErrors();

            SproutBase::error($errorMessage);

            return Craft::$app->getUrlManager()->setRouteParams([
                'notificationEmail' => $notificationEmail
            ]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout-base', 'Notification saved.'));

        return $this->redirectToPostedUrl($notificationEmail);
    }

    /**
     * Delete a Notification Email
     *
     * @return bool|\yii\web\Response
     * @throws \Throwable
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDeleteNotificationEmail()
    {
        $this->requirePostRequest();

        $notificationEmailId = Craft::$app->getRequest()->getBodyParam('emailId');

        /** @var NotificationEmail $notificationEmail */
        $notificationEmail = SproutBase::$app->notifications->getNotificationEmailById($notificationEmailId);

        if (!$notificationEmail) {
            throw new \InvalidArgumentException(Craft::t('sprout-base', 'No Notification Email exists with the ID “{id}”.', [
                'id' => $notificationEmailId
            ]));
        }

        if (!SproutBase::$app->notifications->deleteNotificationEmailById($notificationEmailId)) {

            if (Craft::$app->getRequest()->getIsAjax()) {
                return $this->asJson(['success' => false]);
            }

            Craft::info(json_encode($notificationEmail->getErrors()));

            Craft::$app->getSession()->setNotice(Craft::t('sprout-base', 'Couldn’t delete notification.'));

            // Send the entry back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'notificationEmail' => $notificationEmail
            ]);

            return false;
        }

        if (Craft::$app->getRequest()->getIsAjax()) {
            return $this->asJson(['success' => true]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout-base', 'Notification deleted.'));

        return $this->redirect($this->currentPluginHandle.'/notifications');
    }

    /**
     * Send a notification email via a Mailer
     *
     * @return \yii\web\Response
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSendTestNotificationEmail()
    {
        $this->requirePostRequest();

        $notificationId = Craft::$app->getRequest()->getBodyParam('notificationId');
        $recipients = Craft::$app->getRequest()->getBodyParam('recipients');

        /** @var NotificationEmail $notificationEmail */
        $notificationEmail = Craft::$app->getElements()->getElementById($notificationId);
        $notificationEmail->setIsTest(true);

        if (empty(trim($recipients))) {
            return $this->asJson(
                Response::createErrorModalResponse('sprout-base-email/_modals/response', [
                    'email' => $notificationEmail,
                    'message' => Craft::t('sprout-base', 'Add at least one recipient.')
                ])
            );
        }

        $notificationEmail->recipients = $recipients;
        $notificationEmail->title = $notificationEmail->subjectLine;

        $event = SproutBase::$app->notificationEvents->getEvent($notificationEmail);
        $mailer = SproutBase::$app->mailers->getMailerByName(DefaultMailer::class);

        if (!$event) {
            return $this->asJson(
                Response::createErrorModalResponse('sprout-base-email/_modals/response', [
                    'email' => $notificationEmail,
                    'message' => Craft::t('sprout-base', 'Unable to find Notification Email event.')
                ])
            );
        }

        $notificationEmail->setEventObject($event->getMockEventObject());

        $recipientList = $mailer->getRecipientList($notificationEmail);

        if ($recipientList->getInvalidRecipients()) {
            $invalidEmails = [];
            foreach ($recipientList->getInvalidRecipients() as $invalidRecipient) {
                $invalidEmails[] = $invalidRecipient->email;
            }

            return $this->asJson(
                Response::createErrorModalResponse('sprout-base-email/_modals/response', [
                    'email' => $notificationEmail,
                    'message' => Craft::t('sprout-base', 'Recipient email addresses do not validate: {invalidEmails}', [
                        'invalidEmails' => implode(', ', $invalidEmails)
                    ])
                ])
            );
        }

        if (!$mailer->sendNotificationEmail($notificationEmail))
        {
            return $this->asJson(
                Response::createErrorModalResponse('sprout-base-email/_modals/response', [
                    'email' => $notificationEmail,
                    'message' => Craft::t('sprout-base', 'Unable to send Test Notification Email')
                ])
            );
        }

        return $this->asJson(
            Response::createModalResponse('sprout-base-email/_modals/response', [
                'email' => $notificationEmail,
                'message' => Craft::t('sprout-base', 'Test Notification Email sent.')
            ])
        );
    }

    /**
     * Provides a way for mailers to render content to perform actions inside a a modal window
     *
     * @return \yii\web\Response
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionGetPrepareModal()
    {
        $this->requirePostRequest();

        $notificationId = Craft::$app->getRequest()->getBodyParam('notificationId');

        $response = SproutBase::$app->notifications->getPrepareModal($notificationId);

        return $this->asJson($response->getAttributes());
    }

    /**
     * Prepares a Notification Email to be shared via token-based URL
     *
     * @param null $notificationId
     *
     * @return \yii\web\Response
     * @throws Exception
     * @throws HttpException
     */
    public function actionShareNotificationEmail($notificationId = null)
    {
        if ($notificationId) {
            $notificationEmail = SproutBase::$app->notifications->getNotificationEmailById($notificationId);

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
                'sprout-base/notifications/view-shared-notification-email',
                $params
            ]
        );

        $url = UrlHelper::urlWithToken($notificationEmail->getUrl(), $token);

        return $this->redirect($url);
    }

    /**
     * Renders a shared Notification Email
     * @param null $notificationId
     * @param null $type
     *
     * @throws Exception
     * @throws \Twig_Error_Loader
     * @throws \yii\base\ExitException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionViewSharedNotificationEmail($notificationId = null, $type = null)
    {
        $this->requireToken();

        SproutBase::$app->notifications->getPreviewNotificationEmailById($notificationId, $type);
    }


    /**
     * Renders a Notification Email for Live Preview
     * @throws Exception
     * @throws \Twig_Error_Loader
     * @throws \yii\base\ExitException
     */
    public function actionLivePreviewNotificationEmail()
    {
        $notificationId = Craft::$app->getRequest()->getBodyParam('notificationId');

        SproutBase::$app->notifications->getPreviewNotificationEmailById($notificationId);
    }
}
