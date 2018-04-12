<?php

namespace barrelstrength\sproutbase\controllers;

use barrelstrength\sproutbase\models\sproutbase\Response;
use barrelstrength\sproutbase\web\assets\sproutemail\NotificationAsset;
use barrelstrength\sproutbase\base\TemplateTrait;
use barrelstrength\sproutbase\elements\sproutemail\NotificationEmail;
use barrelstrength\sproutbase\SproutBase;
use craft\helpers\ElementHelper;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use Craft;
use craft\web\View;

/**
 * Class NotificationsController
 *
 * @package barrelstrength\sproutbase\controllers
 */
class NotificationsController extends Controller
{
    use TemplateTrait;

    private $notification;
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
    public function actionEditNotificationEmailSettingsTemplate(
        $emailId = null, NotificationEmail $notificationEmail =
    null
    ) {
        $currentUser = Craft::$app->getUser()->getIdentity();

        if (!$currentUser->can('editSproutEmailSettings')) {
            return $this->redirect($this->currentPluginHandle);
        }

        $isNewNotificationEmail = ($emailId !== null && $emailId === 'new') ? true : false;

        if (!$notificationEmail) {
            if (!$isNewNotificationEmail) {
                $notificationEmail = SproutBase::$app->notifications->getNotificationEmailById($emailId);
            } else {
                $notificationEmail = new NotificationEmail();
            }
        }

        return $this->renderTemplate('sprout-base/sproutemail/notifications/_fieldlayout', [
            'emailId' => $emailId,
            'notificationEmail' => $notificationEmail,
            'isNewNotificationEmail' => $isNewNotificationEmail
        ]);
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

        $emailId = Craft::$app->getRequest()->getBodyParam('emailId');
        $fields = Craft::$app->getRequest()->getBodyParam('sproutEmail');
        $isNewNotificationEmail = ($emailId !== null && $emailId === 'new') ? true : false;

        if (!$isNewNotificationEmail) {
            $notificationEmail = Craft::$app->getElements()->getElementById($emailId);
        } else {
            $notificationEmail = new NotificationEmail();

            $notificationEmail->title = $fields['subjectLine'];

            $fields['slug'] = ElementHelper::createSlug($fields['name']);
        }

        $notificationEmail->setAttributes($fields, false);

        $session = Craft::$app->getSession();

        if ($session AND $notificationEmail->validate()) {
            // Set the field layout
            $fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();
            $fieldLayout->type = NotificationEmail::class;

            $notificationEmail->setFieldLayout($fieldLayout);

            $currentPluginHandle = Craft::$app->request->getSegment(1);

            $eventObject = SproutBase::$app->notifications->getEventByBase($currentPluginHandle);

            if ($eventObject) {
                $namespace = get_class($eventObject);

                $notificationEmail->eventId = $namespace;
            }

            // retain options attribute by the second parameter
            SproutBase::$app->notifications->saveNotification($notificationEmail);

            return $this->redirectToPostedUrl($notificationEmail);
        }

        if ($session) {
            $session->setError(Craft::t('sprout-base', 'Unable to save setting.'));
        }

        return Craft::$app->getUrlManager()->setRouteParams([
            'notificationEmail' => $notificationEmail
        ]);
    }

    /**
     * @param null                   $emailId
     * @param NotificationEmail|null $notificationEmail
     *
     * @return \yii\web\Response
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionEditNotificationEmailTemplate(
        $emailId = null, NotificationEmail $notificationEmail =
    null
    ) {
        $currentPluginHandle = Craft::$app->request->getSegment(1);

        // Immediately create a new Notification
        if (Craft::$app->request->getSegment(4) == 'new') {
            $notification = SproutBase::$app->notifications->createNewNotification();

            if ($notification) {
                $url = UrlHelper::cpUrl($currentPluginHandle.'/notifications/edit/'.$notification->id);
                return $this->redirect($url);
            } else {
                throw new \Exception(Craft::t('sprout-base', 'Error creating Notification'));
            }
        }

        Craft::$app->getView()->registerAssetBundle(NotificationAsset::class);

        if (!$notificationEmail) {
            $notificationEmail = SproutBase::$app->notifications->getNotificationEmailById($emailId);
        }

        $lists = [];

        $showPreviewBtn = false;
        $shareUrl = null;

        $isMobileBrowser = Craft::$app->getRequest()->isMobileBrowser(true);

        $isPluginActive = Craft::$app->plugins->getPlugin('sprout-email');

        if (!$isMobileBrowser && $isPluginActive) {
            $showPreviewBtn = true;

            Craft::$app->getView()->registerJs(
                'Craft.LivePreview.init('.Json::encode(
                    [
                        'fields' => '#subjectLine-field, #body-field, #title-field, #fields > div > div > .field',
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

        $tabs = $this->getModelTabs($notificationEmail);

        $currentPluginHandle = Craft::$app->request->getSegment(1);

        $events = SproutBase::$app->notifications->getAvailableEvents();

        if ($currentPluginHandle != 'sprout-email') {
            $eventObject = SproutBase::$app->notifications->getEventByBase($currentPluginHandle);

            $events = [
                get_class($eventObject) => $eventObject
            ];
        }

        return $this->renderTemplate('sprout-base/sproutemail/notifications/_edit', [
            'notificationEmail' => $notificationEmail,
            'lists' => $lists,
            'mailer' => $notificationEmail->getMailer(),
            'showPreviewBtn' => $showPreviewBtn,
            'shareUrl' => $shareUrl,
            'tabs' => $tabs,
            'events' => $events
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

        $fields = Craft::$app->getRequest()->getBodyParam('sproutEmail');

        $notificationEmail = new NotificationEmail();

        if (isset($fields['id'])) {
            $notificationId = $fields['id'];

            $notificationEmail = SproutBase::$app->notifications->getNotificationEmailById($notificationId);
        }

        $notificationEmail->clearErrors();

        $notificationEmail->setAttributes($fields, false);

        $this->notification = $notificationEmail;

        $this->validateAttribute('fromName', 'From Name', $fields['fromName']);

        $this->validateAttribute('fromEmail', 'From Email', $fields['fromEmail'], true);

        $this->validateAttribute('replyToEmail', 'Reply To', $fields['replyToEmail'], true);

        $notificationEmail = $this->notification;

        $notificationEmail->subjectLine = Craft::$app->getRequest()->getBodyParam('subjectLine');
        $notificationEmail->slug = Craft::$app->getRequest()->getBodyParam('slug');
        $notificationEmail->enabled = Craft::$app->getRequest()->getBodyParam('enabled');
        $notificationEmail->defaultBody = Craft::$app->getRequest()->getBodyParam('defaultBody');

        $notificationEmail->listSettings = Craft::$app->getRequest()->getBodyParam('lists');

        if ($notificationEmail AND $notificationEmail->slug != null) {
            $notificationEmail->slug = ElementHelper::createSlug($notificationEmail->subjectLine);
        }

        $fieldsLocation = Craft::$app->getRequest()->getBodyParam('fieldsLocation', 'fields');

        $notificationEmail->setFieldValuesFromRequest($fieldsLocation);

        $notificationEmail->title = $notificationEmail->subjectLine;

        if ($notificationEmail->titleFormat) {
            $notificationEmail->title = Craft::$app->getView()->renderObjectTemplate($notificationEmail->titleFormat, $notificationEmail);
        }

        $session = Craft::$app->getSession();

        // Do not clear errors to add additional validation
        if ($session AND $notificationEmail->validate(null, false) && $notificationEmail->hasErrors() == false) {

            if (SproutBase::$app->notifications->saveNotification($notificationEmail)) {
                $session->setNotice(Craft::t('sprout-base', 'Notification saved.'));

                return $this->redirectToPostedUrl();
            }

            $session->setError(Craft::t('sprout-base', 'Unable to save notification.'));

            $errorMessage = SproutBase::$app->utilities->formatErrors();

            Craft::error('sprout-base', $errorMessage);

            return Craft::$app->getUrlManager()->setRouteParams([
                'notificationEmail' => $notificationEmail
            ]);
        }

        $session->setError(Craft::t('sprout-base', 'Unable to save notification email.'));

        return Craft::$app->getUrlManager()->setRouteParams([
            'notificationEmail' => $notificationEmail
        ]);
    }

    /**
     * Validate a Notification Email attribute and add errors to the model
     *
     * @param      $attribute
     * @param      $label
     * @param      $value
     * @param bool $email
     */
    private function validateAttribute($attribute, $label, $value, $email = false)
    {
        // Fix the &#8203 bug to test try the @asdf emails
        $value = filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);

        /**
         * @var $notification NotificationEmail
         */
        $notification = $this->notification;

        if (empty($value)) {
            $notification->addError($attribute, Craft::t('sprout-base', "$label cannot be blank."));
        }

        if ($email == true && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $notification->addError($attribute, Craft::t('sprout-base', "$label is not a valid email address."));
        }
    }

    /**
     * Delete a Notification Email
     *
     * @return null|\yii\web\Response
     * @throws \InvalidArgumentException
     * @throws \Throwable
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDeleteNotificationEmail()
    {
        $this->requirePostRequest();

        $notificationEmailId = Craft::$app->getRequest()->getBodyParam('sproutEmail.id');

        /**
         * @var $notificationEmail NotificationEmail
         */
        $notificationEmail = SproutBase::$app->notifications->getNotificationEmailById($notificationEmailId);

        if (!$notificationEmail) {
            throw new \InvalidArgumentException(Craft::t('sprout-base', 'No Notification Email exists with the ID “{id}”.', [
                'id' => $notificationEmailId
            ]));
        }

        $session = Craft::$app->getSession();

        if ($session AND SproutBase::$app->notifications->deleteNotificationEmailById($notificationEmailId)) {
            if (Craft::$app->getRequest()->getIsAjax()) {
                return $this->asJson(['success' => true]);
            }

            $session->setNotice(Craft::t('sprout-base', 'Notification deleted.'));

            return $this->redirect($this->currentPluginHandle.'/notifications');
        }

        if (Craft::$app->getRequest()->getIsAjax()) {
            return $this->asJson(['success' => false]);
        }

        Craft::info(json_encode($notificationEmail->getErrors()));

        $session->setNotice(Craft::t('sprout-base', 'Couldn’t delete notification.'));

        // Send the entry back to the template
        Craft::$app->getUrlManager()->setRouteParams([
            'notificationEmail' => $notificationEmail
        ]);

        return null;
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
        /**
         * @var $notificationEmail NotificationEmail
         */
        $notificationEmail = Craft::$app->getElements()->getElementById($notificationId);

        $errorMsg = '';

        $recipients = Craft::$app->getRequest()->getBodyParam('recipients');

        if ($recipients == null) {
            $errorMsg = Craft::t('sprout-base', 'Empty recipients.');
        }

        $result = $this->getValidAndInvalidRecipients($recipients);

        $invalidRecipients = $result['invalid'];

        if (!empty($invalidRecipients)) {
            $invalidEmails = implode('<br />', $invalidRecipients);

            $errorMsg = Craft::t('sprout-base', 'Recipient email addresses do not validate: <br /> {invalidEmails}', [
                'invalidEmails' => $invalidEmails
            ]);
        }

        if (!empty($errorMsg)) {
            Craft::$app->getView()->setTemplateMode(View::TEMPLATE_MODE_CP);
            return $this->asJson(
                Response::createErrorModalResponse('sprout-base/sproutemail/_modals/response', [
                    'email' => $notificationEmail,
                    'message' => $errorMsg
                ])
            );
        }

        if ($notificationEmail) {
            $notificationEmail->recipients = $recipients;
            $notificationEmail->title = $notificationEmail->subjectLine;

            try {
                $response = SproutBase::$app->notifications->sendTestNotificationEmail($notificationEmail);

                $errors = SproutBase::$app->common->getErrors();

                if ($response instanceof Response AND empty($errors)) {
                    return $this->asJson($response);
                }

                $errorMessage = SproutBase::$app->common->formatErrors();

                if (!$response) {
                    $errorMessage = Craft::t('sprout-base', 'Unable to send email.');
                }

                return $this->asJson(
                    Response::createErrorModalResponse('sprout-base/sproutemail/_modals/response', [
                        'email' => $notificationEmail,
                        'message' => $errorMessage
                    ])
                );
            } catch (\Exception $e) {

                return $this->asJson(
                    Response::createErrorModalResponse('sprout-base/sproutemail/_modals/response', [
                        'email' => $notificationEmail,
                        'message' => $e->getMessage()
                    ])
                );
            }
        }

        return $this->asJson(
            Response::createErrorModalResponse('sprout-base/sproutemail/_modals/response', [
                'email' => $notificationEmail,
                'campaign' => null,
                'message' => Craft::t('sprout-base', 'The notification email you are trying to send is missing.'),
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
     * @throws \HttpException
     */
    public function actionShareNotificationEmail($notificationId = null)
    {
        if ($notificationId) {
            $notificationEmail = SproutBase::$app->notifications->getNotificationEmailById($notificationId);

            if (!$notificationEmail) {
                throw new \HttpException(404);
            }

            $type = Craft::$app->getRequest()->getQueryParam('type');

            $params = [
                'notificationId' => $notificationId,
                'type' => $type
            ];
        } else {
            throw new \HttpException(404);
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
     *
     * @param null $notificationId
     * @param null $type
     *
     * @throws \yii\base\Exception
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
     *
     * @throws \yii\base\Exception
     * @throws \yii\base\ExitException
     */
    public function actionLivePreviewNotificationEmail()
    {
        $notificationId = Craft::$app->getRequest()->getBodyParam('notificationId');

        SproutBase::$app->notifications->getPreviewNotificationEmailById($notificationId);
    }
}
