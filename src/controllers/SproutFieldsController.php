<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutcore\controllers;

use barrelstrength\sproutfields\fields\Email;
use barrelstrength\sproutfields\fields\Link;
use Craft;
use craft\web\Controller as BaseController;

use barrelstrength\sproutcore\SproutCore;

class SproutFieldsController extends BaseController
{
	protected $allowAnonymous = ['actionSproutAddress'];

	public function actionLinkValidate()
	{
		$this->requirePostRequest();
		$this->requireAcceptsJson();
		$value           = Craft::$app->getRequest()->getParam('value');
		$oldFieldContext = Craft::$app->content->fieldContext;
		$fieldContext    = Craft::$app->getRequest()->getParam('fieldContext');
		$fieldHandle     = Craft::$app->getRequest()->getParam('fieldHandle');

		// Retrieve a Link Field, wherever it may be
		Craft::$app->content->fieldContext = $fieldContext;
		$field = Craft::$app->fields->getFieldByHandle($fieldHandle);
		Craft::$app->content->fieldContext = $oldFieldContext;

		// If we don't find a Link Field, return a new Link Field model
		if (!$field)
		{
			$field = new Link();
		}

		if (!SproutCore::$app->link->validate($value, $field))
		{
			return $this->asJson(false);
		}

		return $this->asJson(true);
	}

	public function actionRegularExpressionValidate()
	{
		$this->requirePostRequest();
		$this->requireAcceptsJson();

		$value           = Craft::$app->getRequest()->getParam('value');
		$oldFieldContext = Craft::$app->content->fieldContext;
		$fieldContext    = Craft::$app->getRequest()->getParam('fieldContext');
		$fieldHandle     = Craft::$app->getRequest()->getParam('fieldHandle');

		Craft::$app->content->fieldContext = $fieldContext;
		$field           = Craft::$app->fields->getFieldByHandle($fieldHandle);
		Craft::$app->content->fieldContext = $oldFieldContext;

		if (!SproutCore::$app->regularExpression->validate($value, $field))
		{
			return $this->asJson(false);
		}

		return $this->asJson(true);
	}

	public function actionEmailValidate()
	{
		$this->requirePostRequest();
		$this->requireAcceptsJson();

		$value           = Craft::$app->getRequest()->getParam('value');
		$oldFieldContext = Craft::$app->content->fieldContext;
		$elementId       = Craft::$app->getRequest()->getParam('elementId');
		$fieldContext    = Craft::$app->getRequest()->getParam('fieldContext');
		$fieldHandle     = Craft::$app->getRequest()->getParam('fieldHandle');

		// Retrieve an Email Field, wherever it may be
		Craft::$app->content->fieldContext = $fieldContext;
		$field = Craft::$app->fields->getFieldByHandle($fieldHandle);
		Craft::$app->content->fieldContext = $oldFieldContext;

		// If we don't find a Link Field, return a new Link Field model
		if (!$field)
		{
			$field = new Email();
		}

		if (!SproutCore::$app->email->validate($value, $field, $elementId))
		{
			return $this->asJson(false);
		}

		return $this->asJson(true);
	}

	public function actionPhoneValidate()
	{
		$this->requirePostRequest();
		$this->requireAcceptsJson();

		$value = Craft::$app->getRequest()->getParam('value');
		$mask  = Craft::$app->getRequest()->getParam('mask');

		if (!SproutCore::$app->phone->validate($value, $mask))
		{
			return $this->asJson(false);
		}

		return $this->asJson(true);
	}
}
