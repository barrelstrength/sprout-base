<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutcore\controllers;

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

		Craft::$app->content->fieldContext = $fieldContext;
		$field = Craft::$app->fields->getFieldByHandle($fieldHandle);
		Craft::$app->content->fieldContext = $oldFieldContext;

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

		Craft::$app->content->fieldContext = $fieldContext;
		$field = Craft::$app->fields->getFieldByHandle($fieldHandle);
		Craft::$app->content->fieldContext = $oldFieldContext;

		if (!SproutCore::$app->email->validate($value, $elementId, $field))
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
