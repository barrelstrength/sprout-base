<?php

namespace barrelstrength\sproutbase\controllers;

use barrelstrength\sproutbase\web\assets\sproutemail\NotificationAsset;
use barrelstrength\sproutbase\base\TemplateTrait;
use barrelstrength\sproutbase\elements\sproutemail\NotificationEmail;
use barrelstrength\sproutbase\SproutBase;
use craft\helpers\ElementHelper;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use Craft;

/**
 * Class NotificationsController
 *
 * @package barrelstrength\sproutbase\controllers
 */
class NotificationsController extends Controller
{
    use TemplateTrait;

    private $notification;
    private $currentBase;

    public function init()
    {
        parent::init();

        $currentBase = Craft::$app->request->getSegment(1);

        $this->currentBase = $currentBase;
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
            return $this->redirect($this->currentBase);
        }

        $isNewNotificationEmail = isset($emailId) && $emailId == 'new' ? true : false;

        if (!$notificationEmail) {
            if (!$isNewNotificationEmail) {
                $notificationEmail = SproutBase::$app->notifications->getNotificationEmailById($emailId);
            } else {
                $notificationEmail = new NotificationEmail();
            }
        }

        return $this->renderTemplate('sprout-base/sproutemail/notifications/_setting', [
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
        $isNewNotificationEmail = isset($emailId) && $emailId == 'new' ? true : false;

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
            if (Craft::$app->getRequest()->getBodyParam('fieldLayout')) {
                // Set the field layout
                $fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();

                $fieldLayout->type = NotificationEmail::class;

                if (!Craft::$app->getFields()->saveLayout($fieldLayout)) {
                    $session->setError(Craft::t('sprout-base', 'Couldn’t save notification fields.'));

                    return null;
                }

                if ($notificationEmail->fieldLayoutId != null) {
                    // Remove previous field layout
                    Craft::$app->getFields()->deleteLayoutById($notificationEmail->fieldLayoutId);
                }
            }

            $currentBase = Craft::$app->request->getSegment(1);

            $eventObject = SproutBase::$app->notifications->getEventByBase($currentBase);

            if ($eventObject) {
                $namespace = get_class($eventObject);

                $notificationEmail->eventId = $namespace;
            }

            // retain options attribute by the second parameter
            SproutBase::$app->notifications->saveNotification($notificationEmail, true);

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
     * Renders the Edit Notification Email template
     *
     * @param null                   $emailId
     * @param NotificationEmail|null $notificationEmail
     *
     * @return \yii\web\Response
     * @throws \yii\base\Exception
     */
    public function actionEditNotificationEmailTemplate(
        $emailId = null, NotificationEmail $notificationEmail =
    null
    ) {
        Craft::$app->getView()->registerAssetBundle(NotificationAsset::class);

        if (!$notificationEmail) {
            $notificationEmail = SproutBase::$app->notifications->getNotificationEmailById($emailId);
        }

        $lists = [];

        $showPreviewBtn = false;
        $shareUrl = null;

        $isMobileBrowser = Craft::$app->getRequest()->isMobileBrowser(true);
        $siteTemplateExists = $this->doesSiteTemplateExist($notificationEmail->template);
        $isPluginActive = (Craft::$app->plugins->getPlugin('sprout-email'));

        if (!$isMobileBrowser && $siteTemplateExists && $isPluginActive) {
            $showPreviewBtn = true;

            Craft::$app->getView()->registerJs(
                'Craft.LivePreview.init('.Json::encode(
                    [
                        'fields' => '#subjectLine-field, #title-field, #fields > div > div > .field',
                        'extraFields' => '#settings',
                        'previewUrl' => $notificationEmail->getUrl(),
                        'previewAction' => 'sprout-email/notification-emails/live-preview-notification-email',
                        'previewParams' => [
                            'notificationId' => $notificationEmail->id,
                        ]
                    ]
                ).');'
            );

            if ($notificationEmail->id && $notificationEmail->getUrl()) {
                $shareUrl = UrlHelper::actionUrl('sprout-email/notification-emails/share-notification-email', [
                    'notificationId' => $notificationEmail->id,
                ]);
            }
        }

        $tabs = $this->getModelTabs($notificationEmail);

        return $this->renderTemplate('sprout-base/sproutemail/notifications/_edit', [
            'notificationEmail' => $notificationEmail,
            'lists' => $lists,
            'mailer' => $notificationEmail->getMailer(),
            'showPreviewBtn' => $showPreviewBtn,
            'shareUrl' => $shareUrl,
            'tabs' => $tabs
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
        $notificationEmail->listSettings = Craft::$app->getRequest()->getBodyParam('lists');

        if ($notificationEmail AND $notificationEmail->slug != null) {
            $notificationEmail->slug = ElementHelper::createSlug($notificationEmail->subjectLine);
        }

        $fieldsLocation = Craft::$app->getRequest()->getBodyParam('fieldsLocation', 'fields');

        $notificationEmail->setFieldValuesFromRequest($fieldsLocation);

        $session = Craft::$app->getSession();

        // Do not clear errors to add additional validation
        if ($session AND $notificationEmail->validate(null, false) && $notificationEmail->hasErrors() == false) {
            $notificationEmail->title = $notificationEmail->subjectLine;

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

            return $this->redirect($this->currentBase.'/notifications');
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
}