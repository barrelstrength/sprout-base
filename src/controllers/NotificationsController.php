<?php

namespace barrelstrength\sproutbase\controllers;

use barrelstrength\sproutbase\base\TemplateTrait;
use barrelstrength\sproutbase\elements\sproutemail\NotificationEmail;
use barrelstrength\sproutbase\SproutBase;
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
			$notificationEmail = SproutBase::$app->notificationEmails->getNotificationEmailById($notificationId);
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

		$continueEditingUrl = 'sprout-email/notifications/edit/' . $notificationEmail->id;

		return $this->renderTemplate('sprout-email/notifications/_edit', array(
			'notificationEmail' => $notificationEmail,
			'lists'             => $lists,
			'mailer'            => $notificationEmail->getMailer(),
			'showPreviewBtn'    => $showPreviewBtn,
			'shareUrl'          => $shareUrl,
			'tabs'              => $tabs,
			'continueEditingUrl' => $continueEditingUrl
		));
	}
}