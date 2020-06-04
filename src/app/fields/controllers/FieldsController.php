<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\fields\controllers;

use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Field;
use craft\base\FieldInterface;
use craft\web\Controller as BaseController;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 *
 * @property \craft\base\FieldInterface|null|\craft\base\Field $fieldModel
 */
class FieldsController extends BaseController
{
    /**
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionValidateEmail(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $value = Craft::$app->getRequest()->getParam('value');
        $field = $this->getFieldModel();

        $isValid = SproutBase::$app->emailField->validateEmail($value, $field);

        return $this->asJson(['success' => $isValid]);
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionValidateUrl(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $value = Craft::$app->getRequest()->getParam('value');

        /** @var Field $field */
        $field = $this->getFieldModel();
        $isValid = SproutBase::$app->urlField->validate($value, $field);

        return $this->asJson(['success' => $isValid]);
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionValidatePhone(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $value = Craft::$app->getRequest()->getParam('value');

        $isValid = SproutBase::$app->phoneField->validate($value);

        return $this->asJson(['success' => $isValid]);
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionValidateRegularExpression(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $value = Craft::$app->getRequest()->getParam('value');
        $field = $this->getFieldModel();

        if (!$field) {
            return $this->asJson(['success' => false]);
        }

        $isValid = SproutBase::$app->regularExpressionField->validate($value, $field);

        return $this->asJson(['success' => $isValid]);
    }

    /**
     * @return FieldInterface|null
     */
    protected function getFieldModel()
    {
        $oldFieldContext = Craft::$app->content->fieldContext;
        $fieldContext = Craft::$app->getRequest()->getParam('fieldContext');
        $fieldHandle = Craft::$app->getRequest()->getParam('fieldHandle');

        // Retrieve an Email Field, wherever it may be
        Craft::$app->content->fieldContext = $fieldContext;

        /** @var Field $field */
        $field = Craft::$app->fields->getFieldByHandle($fieldHandle);
        Craft::$app->content->fieldContext = $oldFieldContext;

        return $field;
    }
}
