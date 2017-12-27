<?php

namespace barrelstrength\sproutbase\controllers;

use barrelstrength\sproutbase\elements\sproutemail\NotificationEmail;
use craft\web\Controller;
use Craft;

class NotificationsController extends Controller
{
	public function actionIndex()
	{
		$notifications = NotificationEmail::find()->where(['eventId' => 'barrelstrength\sproutemail\integrations\sproutemail\events\UsersSave'])->all();

	}
}