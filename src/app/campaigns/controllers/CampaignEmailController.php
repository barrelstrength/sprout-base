<?php

namespace barrelstrength\sproutbase\app\campaigns\controllers;

use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\app\email\base\Mailer;
use barrelstrength\sproutbase\app\email\mailers\DefaultMailer;
use barrelstrength\sproutbase\app\email\models\ModalResponse;
use barrelstrength\sproutbase\app\campaigns\base\CampaignEmailSenderInterface;
use barrelstrength\sproutbase\app\campaigns\elements\CampaignEmail;
use barrelstrength\sproutbase\app\campaigns\models\CampaignType;
use Craft;
use craft\base\ElementInterface;
use craft\helpers\ElementHelper;
use craft\helpers\UrlHelper;
use craft\web\assets\cp\CpAsset;
use craft\web\Controller;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use yii\base\ExitException;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 *
 * @property null|ElementInterface|CampaignEmail $campaignEmailModel
 */
class CampaignEmailController extends Controller
{
    /**
     * @var CampaignType
     */
    protected $campaignType;

    /**
     * @return Response
     */
    public function actionCampaignEmailIndexTemplate(): Response
    {
//        $this->requirePermission('sprout:sentEmail:viewSentEmail');

        return $this->renderTemplate('sprout-base-campaigns/campaigns/index', [
//            'isPro' => SproutBase::$app->settings->isPro()
        ]);
    }

    /**
     * Renders a Campaign Email Edit Page
     *
     * @param null               $campaignTypeId
     * @param CampaignEmail|null $campaignEmail
     * @param null               $emailId
     *
     * @return Response
     */
    public function actionEditCampaignEmail($campaignTypeId = null, CampaignEmail $campaignEmail = null, $emailId = null): Response
    {
        // Check if we already have an Campaign Email route variable
        // If so it's probably due to a bad form submission and has an error object
        // that we don't want to overwrite.
        if (!$campaignEmail) {
            if (is_numeric($emailId)) {
                /** @noinspection CallableParameterUseCaseInTypeContextInspection */
                $campaignEmail = SproutBase::$app->campaignEmails->getCampaignEmailById($emailId);
            } else {
                $campaignEmail = new CampaignEmail();
            }
        }

        if ($campaignTypeId == null) {
            $campaignTypeId = $campaignEmail->campaignTypeId;
        } else {
            $campaignEmail->campaignTypeId = $campaignTypeId;
        }

        $campaignType = SproutBase::$app->campaignTypes->getCampaignTypeById($campaignTypeId);

        $campaignEmail->fieldLayoutId = $campaignType->fieldLayoutId;

        $showPreviewBtn = false;

        // Should we show the Share button too?
        if ($campaignEmail->id && $campaignEmail->getUrl()) {
            $showPreviewBtn = true;
        }

        $tabs = [
            [
                'label' => 'Message',
                'url' => '#tab1',
                'class' => null,
            ]
        ];

        $tabs = count($campaignEmail->getFieldLayoutTabs()) ? $campaignEmail->getFieldLayoutTabs() : $tabs;

        return $this->renderTemplate('sprout-base-campaigns/campaigns/_edit', [
            'campaignEmail' => $campaignEmail,
            'emailId' => $emailId,
            'campaignTypeId' => $campaignTypeId,
            'campaignType' => $campaignType,
            'showPreviewBtn' => $showPreviewBtn,
            'tabs' => $tabs
        ]);
    }

    /**
     * Saves a Campaign Email
     *
     * @return null|Response
     * @throws Exception
     * @throws Throwable
     * @throws BadRequestHttpException
     */
    public function actionSaveCampaignEmail()
    {
        $this->requirePostRequest();

        $campaignTypeId = Craft::$app->getRequest()->getBodyParam('campaignTypeId');

        $this->campaignType = SproutBase::$app->campaignTypes->getCampaignTypeById($campaignTypeId);

        if (!$this->campaignType) {
            throw new Exception(Craft::t('sprout', 'No Campaign exists with the id “{id}”', [
                'id' => $campaignTypeId
            ]));
        }

        $campaignEmail = $this->getCampaignEmailModel();

        if (isset($campaignEmail)) {
            $campaignEmail = $this->populateCampaignEmailModel($campaignEmail);
        }

        if (Craft::$app->getRequest()->getBodyParam('saveAsNew')) {
            $campaignEmail->saveAsNew = true;
            $campaignEmail->id = null;
        }

        $session = Craft::$app->getSession();

        if ($session and SproutBase::$app->campaignEmails->saveCampaignEmail($campaignEmail)) {
            $session->setNotice(Craft::t('sprout', 'Campaign Email saved.'));
        } else {
            $session->setError(Craft::t('sprout', 'Could not save Campaign Email.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'campaignEmail' => $campaignEmail
            ]);

            return null;
        }

        return $this->redirectToPostedUrl($campaignEmail);
    }

    /**
     * Sends a Campaign Email
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function actionSendCampaignEmail(): Response
    {
        $this->requirePostRequest();

        $emailId = Craft::$app->getRequest()->getParam('emailId');
        $campaignType = null;
        /**
         * @var $campaignEmail CampaignEmail
         */
        $campaignEmail = SproutBase::$app->campaignEmails->getCampaignEmailById($emailId);

        if ($campaignEmail) {
            try {
                $response = SproutBase::$app->campaignEmails->sendCampaignEmail($campaignEmail);

                if ($response instanceof ModalResponse) {
                    return $this->asJson($response);
                }

                $errorMessage = Craft::t('sprout', 'Mailer did not return a valid response model after sending Campaign Email.');

                if (!$response) {
                    $errorMessage = Craft::t('sprout', 'Unable to send email.');
                }

                return $this->asJson(
                    ModalResponse::createErrorModalResponse('sprout-base-email/_modals/response', [
                        'email' => $campaignEmail,
                        'campaign' => $campaignType,
                        'message' => Craft::t('sprout', $errorMessage),
                    ])
                );
            } catch (\Exception $e) {

                return $this->asJson(
                    ModalResponse::createErrorModalResponse('sprout-base-email/_modals/response', [
                        'email' => $campaignEmail,
                        'campaign' => $campaignType,
                        'message' => Craft::t('sprout', $e->getMessage()),
                    ])
                );
            }
        }

        return $this->asJson(
            ModalResponse::createErrorModalResponse('sprout-base-email/_modals/response', [
                'email' => $campaignEmail,
                'campaign' => $campaignType,
                'message' => Craft::t('sprout', 'The campaign email you are trying to send is missing.'),
            ])
        );
    }

    /**
     * Renders the Send Test Campaign Email Modal
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function actionPrepareTestCampaignEmailModal(): Response
    {
        $this->requirePostRequest();

        $emailId = Craft::$app->getRequest()->getBodyParam('emailId');
        $campaignEmail = SproutBase::$app->campaignEmails->getCampaignEmailById($emailId);

        $campaignType = null;

        if ($campaignEmail != null) {
            $campaignType = SproutBase::$app->campaignTypes->getCampaignTypeById($campaignEmail->campaignTypeId);
        }

        $html = Craft::$app->getView()->renderTemplate('sprout-base-campaigns/_modals/campaigns/prepare-test-email', [
            'campaignEmail' => $campaignEmail,
            'campaignType' => $campaignType
        ]);

        return $this->asJson([
            'success' => true,
            'content' => $html
        ]);
    }

    /**
     * Renders the Schedule Campaign Email Modal
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function actionPrepareScheduleCampaignEmail(): Response
    {
        $this->requirePostRequest();

        $emailId = Craft::$app->getRequest()->getBodyParam('emailId');

        $campaignType = null;

        $campaignEmail = SproutBase::$app->campaignEmails->getCampaignEmailById($emailId);

        if ($campaignEmail != null) {
            $campaignType = SproutBase::$app->campaignTypes->getCampaignTypeById($campaignEmail->campaignTypeId);
        }

        $html = Craft::$app->getView()->renderTemplate('sprout-base-campaigns/_modals/campaigns/prepare-scheduled-email', [
            'campaignEmail' => $campaignEmail,
            'campaignType' => $campaignType
        ]);

        return $this->asJson([
            'success' => true,
            'content' => $html
        ]);
    }

    /**
     * Renders the Shared Campaign Email
     *
     * @param null $emailId
     * @param null $type
     *
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws ExitException
     * @throws LoaderError
     * @throws NotFoundHttpException
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function actionViewSharedCampaignEmail($emailId = null, $type = null)
    {
        $this->requireToken();

        $campaignEmail = SproutBase::$app->campaignEmails->getCampaignEmailById($emailId);

        if (!$campaignEmail) {
            throw new NotFoundHttpException(Craft::t('sprout', 'No Campaign Email with id {id} was found.', [
                'id' => $emailId
            ]));
        }

        $campaignType = SproutBase::$app->campaignTypes->getCampaignTypeById($campaignEmail->campaignTypeId);

        $params = [
            'email' => $campaignEmail,
            'campaignType' => $campaignType
        ];

        $extension = ($type != null && $type == 'text') ? 'txt' : 'html';

        $campaignEmail->setEventObject($params);

        $htmlBody = $campaignEmail->getEmailTemplates()->getHtmlBody();
        $body = $campaignEmail->getEmailTemplates()->getTextBody();

        SproutBase::$app->campaignEmails->showCampaignEmail($htmlBody, $body, $extension);
    }

    /**
     * Sends a Test Campaign Email
     *
     * Test Emails do not trigger an onSendEmail event and do not get marked as Sent.
     *
     * @return Response
     * @throws Exception
     * @throws Throwable
     * @throws BadRequestHttpException
     * @todo - update to use getIsTest() syntax
     *
     */
    public function actionSendTestCampaignEmail()
    {
        $this->requirePostRequest();

        $emailId = Craft::$app->getRequest()->getBodyParam('emailId');

        $campaignEmail = SproutBase::$app->campaignEmails->getCampaignEmailById($emailId);

        if (!$campaignEmail) {
            throw new InvalidArgumentException(Craft::t('sprout', 'Unable to find Campaign Email with id {id}', [
                'id' => $emailId
            ]));
        }

        $mailer = $campaignEmail->getMailer();

        $recipients = Craft::$app->getRequest()->getBodyParam('recipients');

        $campaignEmail->recipients = $recipients;

        $recipientList = $mailer->getRecipientList($campaignEmail);

        if ($recipientList->getInvalidRecipients()) {
            $invalidEmails = [];
            foreach ($recipientList->getInvalidRecipients() as $invalidRecipient) {
                $invalidEmails[] = $invalidRecipient->email;
            }

            return $this->asJson(
                ModalResponse::createErrorModalResponse('sprout-base-email/_modals/response', [
                    'email' => $campaignEmail,
                    'message' => Craft::t('sprout', 'Recipient email addresses do not validate: {invalidEmails}', [
                        'invalidEmails' => implode(', ', $invalidEmails)
                    ])
                ])
            );
        }

        try {
            /** @var Mailer|CampaignEmailSenderInterface $mailer */
            $mailer = SproutBase::$app->mailers->getMailerByName(DefaultMailer::class);
            $campaignEmail->setIsTest();

            if (!$mailer->sendTestCampaignEmail($campaignEmail)) {
                return $this->asJson(
                    ModalResponse::createErrorModalResponse('sprout-base-email/_modals/response', [
                        'email' => $campaignEmail,
                        'message' => Craft::t('sprout', 'Unable to send Test Campaign Email')
                    ])
                );
            }

            return $this->asJson(
                ModalResponse::createModalResponse('sprout-base-email/_modals/response', [
                    'email' => $campaignEmail,
                    'message' => Craft::t('sprout', 'Test Campaign Email sent.')
                ])
            );
        } catch (\Exception $e) {
            return $this->asJson(
                ModalResponse::createErrorModalResponse('sprout-base-email/_modals/response', [
                    'email' => $campaignEmail,
                    'campaign' => $campaignEmail->getCampaignType(),
                    'message' => Craft::t('sprout', $e->getMessage()),
                ])
            );
        }
    }

    /**
     * @param null   $emailId
     * @param string $type
     *
     * @return Response
     * @throws HttpException
     */
    public function actionShareCampaignEmail($emailId = null, $type = 'html'): Response
    {
        if ($emailId) {
            $campaignEmail = SproutBase::$app->campaignEmails->getCampaignEmailById($emailId);

            if (!$campaignEmail) {
                throw new HttpException(404);
            }
        } else {
            throw new HttpException(404);
        }

        $params = [
            'emailId' => $emailId,
            'type' => $type
        ];

        // Create the token and redirect to the entry URL with the token in place
        $token = Craft::$app->getTokens()->createToken(['sprout-base-campaigns/campaign-email/view-shared-campaign-email', $params]);

        $emailUrl = '';
        if ($campaignEmail->getUrl() !== null) {
            $emailUrl = $campaignEmail->getUrl();
        }

        $url = UrlHelper::urlWithToken($emailUrl, $token);

        return $this->redirect($url);
    }

    /**
     * @param null $emailType
     * @param null $emailId
     *
     * @return Response
     * @throws InvalidConfigException
     */
    public function actionPreviewCampaignEmail($emailType = null, $emailId = null): Response
    {
        Craft::$app->getView()->registerAssetBundle(CpAsset::class);

        return $this->renderTemplate('sprout-base-email/_special/preview', [
            'emailType' => $emailType,
            'emailId' => $emailId
        ]);
    }

    /**
     * Returns a Campaign Email Model
     *
     * @return CampaignEmail|ElementInterface|null
     * @throws \Exception
     */
    protected function getCampaignEmailModel()
    {
        $emailId = Craft::$app->getRequest()->getBodyParam('emailId');
        $saveAsNew = Craft::$app->getRequest()->getBodyParam('saveAsNew');

        if ($emailId && !$saveAsNew && $emailId !== 'new') {
            $campaignEmail = SproutBase::$app->campaignEmails->getCampaignEmailById($emailId);

            if (!$campaignEmail) {
                throw new Exception(Craft::t('sprout', 'No entry exists with the ID “{id}”', ['id' => $emailId]));
            }
        } else {
            $campaignEmail = new CampaignEmail();
        }

        return $campaignEmail;
    }

    /**
     * Populates a Campaign Email Model
     *
     * @param CampaignEmail $campaignEmail
     *
     * @return CampaignEmail
     */
    protected function populateCampaignEmailModel(CampaignEmail $campaignEmail): CampaignEmail
    {
        $campaignEmail->campaignTypeId = $this->campaignType->id;
        $campaignEmail->slug = Craft::$app->getRequest()->getBodyParam('slug', $campaignEmail->slug);
        $campaignEmail->enabled = (bool)Craft::$app->getRequest()->getBodyParam('enabled', $campaignEmail->enabled);
        $campaignEmail->fromName = Craft::$app->getRequest()->getBodyParam('sproutcampaign.fromName');
        $campaignEmail->fromEmail = Craft::$app->getRequest()->getBodyParam('sproutcampaign.fromEmail');
        $campaignEmail->replyToEmail = Craft::$app->getRequest()->getBodyParam('sproutcampaign.replyToEmail');
        $campaignEmail->subjectLine = Craft::$app->getRequest()->getBodyParam('subjectLine');
        $campaignEmail->dateScheduled = Craft::$app->getRequest()->getBodyParam('dateScheduled');
        $campaignEmail->defaultBody = Craft::$app->getRequest()->getBodyParam('defaultBody');

        if (Craft::$app->getRequest()->getBodyParam('sproutcampaign.recipients') != null) {
            $campaignEmail->recipients = Craft::$app->request->getBodyParam('sproutcampaign.recipients');
        }

        $enableFileAttachments = Craft::$app->request->getBodyParam('sproutcampaign.enableFileAttachments');
        $campaignEmail->enableFileAttachments = $enableFileAttachments ?: false;

        $campaignEmail->title = $campaignEmail->subjectLine;

        if ($campaignEmail->slug === null) {
            $campaignEmail->slug = ElementHelper::createSlug($campaignEmail->subjectLine);
        }

        $fieldsLocation = Craft::$app->getRequest()->getParam('fieldsLocation', 'fields');

        $campaignEmail->setFieldValuesFromRequest($fieldsLocation);

        $campaignEmail->listSettings = Craft::$app->getRequest()->getBodyParam('lists');

        return $campaignEmail;
    }
}