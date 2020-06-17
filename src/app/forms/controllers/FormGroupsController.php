<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\controllers;

use barrelstrength\sproutbase\app\forms\models\FormGroup as FormGroupModel;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\errors\MissingComponentException;
use craft\web\Controller as BaseController;
use yii\db\Exception;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class FormGroupsController extends BaseController
{
    /**
     * Save a group.
     *
     * @return Response
     * @throws \yii\base\Exception
     * @throws BadRequestHttpException
     */
    public function actionSaveGroup(): Response
    {
        $this->requirePostRequest();
        $this->requireAdmin(false);

        $request = Craft::$app->getRequest();

        $group = new FormGroupModel();
        $group->id = $request->getBodyParam('id');
        $group->name = $request->getRequiredBodyParam('name');

        $isNewGroup = (null === $group->id);

        if (SproutBase::$app->groups->saveGroup($group)) {
            if ($isNewGroup) {
                Craft::$app->getSession()->setNotice(Craft::t('sprout', 'Group added.'));
            }

            return $this->asJson([
                'success' => true,
                'group' => $group->getAttributes(),
            ]);
        }

        return $this->asJson([
            'errors' => $group->getErrors(),
        ]);
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws MissingComponentException
     * @throws Exception
     */
    public function actionDeleteGroup(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $this->requireAdmin(false);

        $request = Craft::$app->getRequest();

        $groupId = $request->getRequiredBodyParam('id');
        $success = SproutBase::$app->groups->deleteGroupById($groupId);

        Craft::$app->getSession()->setNotice(Craft::t('sprout', 'Group deleted.'));

        return $this->asJson([
            'success' => $success,
        ]);
    }
}
