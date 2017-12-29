<?php

namespace barrelstrength\sproutbase\controllers;

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

	public function actionEditNotificationEmailSettingsTemplate($emailId = null, NotificationEmail $notificationEmail =
	null)
	{
		$currentUser = Craft::$app->getUser()->getIdentity();

		if (!$currentUser->can('editSproutEmailSettings'))
		{
			return $this->redirect('sprout-email');
		}

		$isNewNotificationEmail = isset($emailId) && $emailId == 'new' ? true : false;

		if (!$notificationEmail)
		{
			if (!$isNewNotificationEmail)
			{
				$notificationEmail = SproutBase::$app->notifications->getNotificationEmailById($emailId);
			}
			else
			{
				$notificationEmail = new NotificationEmail();
			}
		}

		return $this->renderTemplate('sprout-base/sproutemail/notifications/_setting', array(
			'emailId'                => $emailId,
			'notificationEmail'      => $notificationEmail,
			'isNewNotificationEmail' => $isNewNotificationEmail
		));
	}

	/**
	 * Save a Notification Email from the Notification Email Settings template
	 * @return null
	 * @throws \Exception
	 * @throws \Throwable
	 * @throws \yii\base\Exception
	 * @throws \yii\web\BadRequestHttpException
	 */
	public function actionSaveNotificationEmailSettings()
	{
		$this->requirePostRequest();

		$emailId                = Craft::$app->getRequest()->getBodyParam('emailId');
		$fields                 = Craft::$app->getRequest()->getBodyParam('sproutEmail');
		$isNewNotificationEmail = isset($emailId) && $emailId == 'new' ? true : false;

		if (!$isNewNotificationEmail)
		{
			$notificationEmail = Craft::$app->getElements()->getElementById($emailId);
		}
		else
		{
			$notificationEmail = new NotificationEmail();

			$notificationEmail->title = $fields['subjectLine'];

			$fields['slug']        = ElementHelper::createSlug($fields['name']);
		}

		$notificationEmail->setAttributes($fields, false);

		if ($notificationEmail->validate())
		{
			if (Craft::$app->getRequest()->getBodyParam('fieldLayout'))
			{
				// Set the field layout
				$fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();

				$fieldLayout->type = NotificationEmail::class;

				if (!Craft::$app->getFields()->saveLayout($fieldLayout)) {
					Craft::$app->getSession()->setError(Craft::t('sprout-email', 'Couldn’t save notification fields.'));

					return null;
				}

				if ($notificationEmail->fieldLayoutId != null)
				{
					// Remove previous field layout
					Craft::$app->getFields()->deleteLayoutById($notificationEmail->fieldLayoutId);
				}
			}

			// retain options attribute by the second parameter
			SproutBase::$app->notifications->saveNotification($notificationEmail, true);

			$this->redirectToPostedUrl($notificationEmail);
		}
		else
		{
			Craft::$app->getSession()->setError(Craft::t('sprout-email', 'Unable to save setting.'));

			return Craft::$app->getUrlManager()->setRouteParams(array(
				'notificationEmail' => $notificationEmail
			));
		}

		return null;
	}


	/**
	 * Renders the Edit Notification Email template
	 * @param null                   $notificationId
	 * @param NotificationEmail|null $notificationEmail
	 *
	 * @return \yii\web\Response
	 * @throws \yii\base\Exception
	 */
	public function actionEditNotificationEmailTemplate($notificationId = null, NotificationEmail $notificationEmail =
	null)
	{
		if (!$notificationEmail)
		{
			$notificationEmail = SproutBase::$app->notifications->getNotificationEmailById($notificationId);
		}

		$lists = array();

		$showPreviewBtn = false;
		$shareUrl       = null;

		$isMobileBrowser    = Craft::$app->getRequest()->isMobileBrowser(true);
		$siteTemplateExists = $this->doesSiteTemplateExist($notificationEmail->template);

		if (!$isMobileBrowser && $siteTemplateExists)
		{
			$showPreviewBtn = true;

			Craft::$app->getView()->registerJs(
				'Craft.LivePreview.init(' . Json::encode(
					array(
						'fields'        => '#subjectLine-field, #title-field, #fields > div > div > .field',
						'extraFields'   => '#settings',
						'previewUrl'    => $notificationEmail->getUrl(),
						'previewAction' => 'sprout-email/notification-emails/live-preview-notification-email',
						'previewParams' => array(
							'notificationId' => $notificationEmail->id,
						)
					)
				) . ');'
			);

			if ($notificationEmail->id && $notificationEmail->getUrl())
			{
				$shareUrl = UrlHelper::actionUrl('sprout-email/notification-emails/share-notification-email', array(
					'notificationId' => $notificationEmail->id,
				));
			}
		}

		$tabs = $this->getModelTabs($notificationEmail);

		$continueEditingUrl = 'sprout-base/notifications/edit/' . $notificationEmail->id;

		return $this->renderTemplate('sprout-base/sproutemail/notifications/_edit', array(
			'notificationEmail' => $notificationEmail,
			'lists'             => $lists,
			'mailer'            => $notificationEmail->getMailer(),
			'showPreviewBtn'    => $showPreviewBtn,
			'shareUrl'          => $shareUrl,
			'tabs'              => $tabs,
			'continueEditingUrl' => $continueEditingUrl
		));
	}

	/**
	 * Save a Notification Email from the Notification Email template
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

		if (isset($fields['id']))
		{
			$notificationId = $fields['id'];

			$notificationEmail = SproutBase::$app->notifications->getNotificationEmailById($notificationId);
		}

		$notificationEmail->clearErrors();

		$notificationEmail->setAttributes($fields, false);

		$this->notification = $notificationEmail;

		$this->validateAttribute('fromName', 'From Name', $fields['fromName']);

		$this->validateAttribute('fromEmail', 'From Email', $fields['fromEmail']);

		$this->validateAttribute('replyToEmail', 'Reply To', $fields['replyToEmail']);

		$notificationEmail = $this->notification;

		$notificationEmail->subjectLine  = Craft::$app->getRequest()->getBodyParam('subjectLine');
		$notificationEmail->slug         = Craft::$app->getRequest()->getBodyParam('slug');
		$notificationEmail->enabled      = Craft::$app->getRequest()->getBodyParam('enabled');
		$notificationEmail->listSettings = Craft::$app->getRequest()->getBodyParam('lists');

		if (empty($notificationEmail->slug))
		{
			$notificationEmail->slug = ElementHelper::createSlug($notificationEmail->subjectLine);
		}

		$fieldsLocation = Craft::$app->getRequest()->getBodyParam('fieldsLocation', 'fields');

		$notificationEmail->setFieldValuesFromRequest($fieldsLocation);

		// Do not clear errors to add additional validation
		if ($notificationEmail->validate(null, false) && $notificationEmail->hasErrors() == false)
		{
			$notificationEmail->title = $notificationEmail->subjectLine;

			if (SproutEmail::$app->notificationEmails->saveNotification($notificationEmail))
			{
				Craft::$app->getSession()->setNotice(Craft::t('sprout-email', 'Notification saved.'));

				return $this->redirectToPostedUrl();
			}
			else
			{
				Craft::$app->getSession()->setError(Craft::t('sprout-email','Unable to save notification.'));

				$errors = SproutEmail::$app->utilities->getErrors();

				$errorMessage = print_r($errors, true);

				Craft::error('sprout-email', $errorMessage);

				Craft::$app->getUrlManager()->setRouteParams(array(
					'notificationEmail' => $notificationEmail
				));
			}
		}
		else
		{
			Craft::$app->getSession()->setError(Craft::t('sprout-email', 'Unable to save notification email.'));

			Craft::$app->getUrlManager()->setRouteParams(array(
				'notificationEmail' => $notificationEmail
			));
		}

		return null;
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

		if (empty($value))
		{
			$this->notification->addError($attribute, Craft::t('sprout-email', "$label cannot be blank."));
		}

		if ($email == true && filter_var($value, FILTER_VALIDATE_EMAIL) === false)
		{
			$this->notification->addError($attribute, Craft::t('sprout-email', "$label is not a valid email address."));
		}
	}
}