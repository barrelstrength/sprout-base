<?php
namespace barrelstrength\sproutcore\controllers;

use Craft;
use craft\web\Controller as BaseController;
use yii\web\NotFoundHttpException;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\elements\Form as FormElement;
use barrelstrength\sproutforms\elements\Entry as EntryElement;

class StuffController extends BaseController
{
	// Form Action URL: http://craft3.dev/admin/actions/sprout-core/stuff/do-something
	public function actionDoSomething()
	{
		Craft::dd('O yea!');
	}

	public function actionSettings()
	{
		return $this->renderTemplate('sprout-core/_index', array());
	}
}
