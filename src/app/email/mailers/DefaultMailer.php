<?php

namespace barrelstrength\sproutbase\app\email\mailers;

use barrelstrength\sproutbase\app\email\base\EmailTemplateTrait;
use barrelstrength\sproutbase\app\email\base\Mailer;
use barrelstrength\sproutbase\app\email\base\NotificationEmailSenderInterface;
use barrelstrength\sproutbase\app\email\models\Message;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutemail\elements\CampaignEmail;
use barrelstrength\sproutbase\app\email\elements\NotificationEmail;
use barrelstrength\sproutemail\models\CampaignType;
use barrelstrength\sproutbase\app\email\models\Response;
use barrelstrength\sproutbase\app\email\models\Recipient;
use barrelstrength\sproutemail\SproutEmail;
use barrelstrength\sproutlists\elements\Subscribers;
use barrelstrength\sproutlists\listtypes\SubscriberListType;
use barrelstrength\sproutlists\records\Lists;
use barrelstrength\sproutlists\SproutLists;
use craft\base\Element;
use craft\base\Volume;
use craft\db\Query;
use craft\elements\Asset;
use craft\elements\db\AssetQuery;
use craft\fields\Assets;
use craft\helpers\Json;
use craft\helpers\Template;
use Craft;
use craft\helpers\UrlHelper;
use craft\volumes\Local;
use yii\base\Exception;
use yii\base\InvalidArgumentException;


class DefaultMailer extends Mailer implements NotificationEmailSenderInterface
{
    use EmailTemplateTrait;

    /**
     * @var
     */
    protected $lists;

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Sprout Email';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Craft::t('sprout-base', 'Smart transactional email, easy recipient management, and advanced third party integrations.');
    }

    /**
     * @inheritdoc
     */
    public function hasCpSection()
    {
        return true;
    }

    /**
     * @inheritdoc
     *
     * @throws Exception
     * @throws \Twig_Error_Loader
     */
    public function getSettingsHtml(array $settings = [])
    {
        /** @noinspection NullCoalescingOperatorCanBeUsedInspection */
        $settings = isset($settings['settings']) ? $settings['settings'] : $this->getSettings();

        $html = Craft::$app->getView()->renderTemplate('sprout-base/_integrations/sproutemail/mailers/defaultmailer/settings', [
            'settings' => $settings
        ]);

        return Template::raw($html);
    }

    /**
     * @inheritdoc
     *
     * @throws \Exception
     * @throws \Twig_Error_Loader
     */
    public function sendCampaignEmail(CampaignEmail $campaignEmail, CampaignType $campaignType)
    {
        $email = new Message();

        try {
            $response = [];

            $params = [
                'email' => $campaignEmail,
                'campaignType' => $campaignType,
            ];

            $email->setFrom([$campaignEmail->fromEmail => $campaignEmail->fromName]);
            $email->setSubject($campaignEmail->subjectLine);

            if ($campaignEmail->replyToEmail && filter_var($campaignEmail->replyToEmail, FILTER_VALIDATE_EMAIL)) {
                $email->setReplyTo($campaignEmail->replyToEmail);
            }

            $recipients = Craft::$app->getRequest()->getBodyParam('recipients');

            if ($recipients === null) {
                throw new InvalidArgumentException(Craft::t('sprout-base', 'Empty recipients.'));
            }

            $result = $this->getValidAndInvalidRecipients($recipients);

            $invalidRecipients = $result['invalid'];
            $validRecipients = $result['valid'];

            if (!empty($invalidRecipients)) {
                $invalidEmails = implode('<br/>', $invalidRecipients);

                throw new InvalidArgumentException(Craft::t('sprout-base', 'The following recipient email addresses do not validate: {invalidEmails}',
                    [
                        'invalidEmails' => $invalidEmails
                    ]));
            }

            $recipients = $validRecipients;

            foreach ($recipients as $recipient) {
                try {
                    $params['recipient'] = $recipient;
                    $body = $this->renderSiteTemplateIfExists($campaignType->template.'.txt', $params);

                    $email->setTextBody($body);
                    $htmlBody = $this->renderSiteTemplateIfExists($campaignType->template, $params);

                    $email->setHtmlBody($htmlBody);
                    $name = $recipient->firstName.' '.$recipient->lastName;
                    $email->setTo([$recipient->email => $name]);

                    SproutBase::$app->mailers->sendEmail($email);
                } catch (\Exception $e) {
                    throw $e;
                }
            }

            $response['emailModel'] = $email;

            return Response::createModalResponse(
                'sprout-base-email/_modals/response',
                [
                    'email' => $campaignEmail,
                    'campaign' => $campaignType,
                    'emailModel' => $response['emailModel'],
                    'message' => Craft::t('sprout-base', 'Campaign sent successfully to email.'),
                ]
            );
        } catch (Exception $e) {
            SproutBase::$app->emailErrorHelper->addError('fail-campaign-email', $e->getMessage());

            return Response::createErrorModalResponse(
                'sprout-base-email/_modals/response',
                [
                    'email' => $campaignEmail,
                    'campaign' => $campaignType,
                    'message' => Craft::t('sprout-base', $e->getMessage()),
                ]
            );
        }
    }

    /**
     * @inheritdoc
     *
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function sendNotificationEmail(NotificationEmail $notificationEmail, $object = null, $useMockData = false)
    {
        // Allow disabled emails to be tested
        if (!$notificationEmail->isReady() && !$useMockData) {
            return false;
        }

        $recipients = $this->prepareRecipients($notificationEmail, $object, $useMockData);

        if (empty($recipients)) {
            SproutBase::$app->emailErrorHelper->addError('no-recipients', Craft::t('sprout-base', 'No recipients found.'));
        }

        $template = SproutBase::$app->sproutEmail->getEmailTemplate($notificationEmail);

        $view = Craft::$app->getView();
        $oldTemplatePath = $view->getTemplatesPath();

        $view->setTemplatesPath($template);

        /** @var Message $message */
        $message = SproutBase::$app->notifications->getNotificationEmailMessage($notificationEmail, $object);

        $view->setTemplatesPath($oldTemplatePath);

        $body = $message->renderedBody;
        $htmlBody = $message->renderedHtmlBody;

        $templateErrors = SproutBase::$app->emailErrorHelper->getErrors();

        SproutBase::error($templateErrors);

        if (empty($templateErrors) && (empty($body) || empty($htmlBody))) {
            $message = Craft::t('sprout-base', 'Email Text or HTML template cannot be blank. Check template setting.');

            SproutBase::$app->emailErrorHelper->addError('blank-template', $message);
        }

        // Adds support for attachments
        if ($notificationEmail->enableFileAttachments) {
            if ($object instanceof Element && method_exists($object, 'getFields')) {
                $externalPaths = [];
                foreach ($object->getFields() as $field) {
                    if (get_class($field) === 'barrelstrength\\sproutforms\\fields\\formfields\\FileUpload' OR get_class($field) === Assets::class) {
                        $query = $object->{$field->handle};

                        if ($query instanceof AssetQuery) {
                            $assets = $query->all();

                            $this->attachAssetFilesToEmailModel($message, $assets, $externalPaths);
                        }
                    }
                }

                $this->deleteExternalPaths($externalPaths);
            }
        }

        $processedRecipients = [];

        foreach ($recipients as $recipient) {
            $toEmail = $this->renderObjectTemplateSafely($recipient->email, $object);

            $name = $recipient->firstName.' '.$recipient->lastName;

            /**
             * @var $message Message
             */
            $message->setTo([$toEmail => $name]);

            if (array_key_exists($toEmail, $processedRecipients)) {
                continue;
            }

            try {
                $variables = [];

                if (Craft::$app->plugins->getPlugin('sprout-email')) {
                    $infoTable = SproutEmail::$app->sentEmails->createInfoTableModel('sprout-email', [
                        'emailType' => Craft::t('sprout-base', 'Notification'),
                        'deliveryType' => $useMockData ? Craft::t('sprout-base', 'Test') : Craft::t('sprout-base', 'Live')
                    ]);

                    $variables = [
                        'email' => $notificationEmail,
                        'renderedEmail' => $message,
                        'object' => $object,
                        'recipients' => $recipients,
                        'processedRecipients' => null,
                        'info' => $infoTable
                    ];
                }

                if (SproutBase::$app->mailers->sendEmail($message, $variables)) {
                    $processedRecipients[] = $toEmail;
                } else {
                    return false;
                }
            } catch (\Exception $e) {
                SproutBase::$app->emailErrorHelper->addError('fail-send-email', $e->getMessage());
            }
        }

        // Trigger on send notification event
        if (!empty($processedRecipients)) {
            $variables['processedRecipients'] = $processedRecipients;
        }

        return true;
    }

    /**
     * @param $externalPaths
     */
    protected function deleteExternalPaths($externalPaths)
    {
        foreach ($externalPaths as $path)
        {
            if (file_exists($path)){
                unlink($path);
            }
        }
    }

    /**
     * @param Message $message
     * @param Asset[] $assets
     * @param array $externalPaths
     * @throws \yii\base\InvalidConfigException
     */
    protected function attachAssetFilesToEmailModel(Message $message, array $assets, &$externalPaths = [])
    {
        foreach ($assets as $asset) {
            $name = $asset->filename;
            $volume = $asset->getVolume();
            $path = null;

            if (get_class($volume) === Local::class) {
                $path = $this->getAssetFilePath($asset);
            } else {
                // External Asset sources
                $path = $asset->getCopyOfFile();
                // let's save the path to delete it after sent
                array_push($externalPaths, $path);
            }
            if ($path){
                $message->attach($path, ['fileName' => $name]);
            }
        }
    }

    /**
     * @param Asset $asset
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    protected function getAssetFilePath(Asset $asset)
    {
        return $asset->getVolume()->getRootPath() . $asset->getFolder()->path . DIRECTORY_SEPARATOR . $asset->filename;
    }

    /**
     * @inheritdoc
     *
     * @throws Exception
     * @throws \Twig_Error_Loader
     */
    public function getPrepareModalHtml(CampaignEmail $campaignEmail, CampaignType $campaignType)
    {
        if (!empty($campaignEmail->recipients)) {
            $recipients = $campaignEmail->recipients;
        }

        if (empty($recipients)) {
            $recipients = Craft::$app->getUser()->getIdentity()->email;
        }

        $errors = [];

        $errors = $this->getErrors($campaignEmail, $campaignType, $errors);

        return Craft::$app->getView()->renderTemplate('sprout-base-email/_modals/campaigns/prepareEmailSnapshot', [
            'campaignEmail' => $campaignEmail,
            'campaignType' => $campaignType,
            'recipients' => $recipients,
            'errors' => $errors
        ]);
    }

    /**
     * @inheritdoc
     */
    public function hasInlineRecipients()
    {
        return true;
    }

    /**
     * @inheritdoc
     *
     * @throws Exception
     */
    public function getLists()
    {
        if ($this->lists === null && Craft::$app->getPlugins()->getPlugin('sprout-lists') != null) {
            $listType = SproutLists::$app->lists
                ->getListType(SubscriberListType::class);

            $this->lists = $listType ? $listType->getLists() : [];
        }

        return $this->lists;
    }

    /**
     * @inheritdoc
     *
     * @throws Exception
     * @throws \Twig_Error_Loader
     */
    public function getListsHtml($values = [])
    {
        $selected = [];
        $options = [];
        $lists = $this->getLists();

        if (!count($lists)) {
            return '';
        }

        foreach ($lists as $list) {
            $listName = $list->name;

            if (count($list->totalSubscribers)) {
                $listName .= ' ('.$list->totalSubscribers.')';
            } else {
                $listName .= ' (0)';
            }

            $options[] = [
                'label' => $listName,
                'value' => $list->id
            ];
        }

        $listIds = [];

        // Convert json format to array
        if ($values != null AND is_string($values)) {
            $listIds = Json::decode($values);
            $listIds = $listIds['listIds'];
        }

        if (!empty($listIds)) {
            foreach ($listIds as $key => $listId) {
                $selected[] = $listId;
            }
        }

        return Craft::$app->getView()->renderTemplate('sprout-base-email/_components/mailers/defaultmailer/lists', [
            'options' => $options,
            'values' => $selected,
        ]);
    }

    /**
     * @param NotificationEmail $notificationEmail
     * @param                   $object
     * @param                   $useMockData
     *
     * @return array|mixed
     * @throws \Exception
     */
    protected function prepareRecipients(NotificationEmail $notificationEmail, $object, $useMockData)
    {
        // Get recipients for test notifications
        if ($useMockData) {
            $recipients = Craft::$app->getRequest()->getBodyParam('recipients');

            if (empty($recipients)) {
                return [];
            }

            $recipients = Craft::$app->getRequest()->getBodyParam('recipients');

            $result = $this->getValidAndInvalidRecipients($recipients);

            $invalidRecipients = $result['invalid'];
            $validRecipients = $result['valid'];

            if (!empty($invalidRecipients)) {
                $invalidEmails = implode('<br>', $invalidRecipients);

                throw new InvalidArgumentException(Craft::t('sprout-base', 'Recipient email addresses do not validate: <br /> {invalidEmails}', [
                    'invalidEmails' => $invalidEmails
                ]));
            }

            return $validRecipients;
        }

        // Get recipients for live emails
        // @todo Craft 3 - improve and standardize how we use entryRecipients
        $recipients = $this->getRecipientsFromEmailElement($notificationEmail, $object);

        // @todo implement this when we develop sprout lists plugin
        if (Craft::$app->getPlugins()->getPlugin('sprout-lists') != null) {

            $listSettings = $notificationEmail->listSettings;
            $listIds = [];
            // Convert json format to array
            if ($listSettings != null AND is_string($listSettings)) {
                $listIds = Json::decode($listSettings);
                $listIds = $listIds['listIds'];
            }

            // Get all subscribers by list IDs from the SproutLists_SubscriberListType
            $listRecords = Lists::find()
                ->where(['id' => $listIds])->all();

            $sproutListsRecipientsInfo = [];
            if ($listRecords != null) {
                foreach ($listRecords as $listRecord) {
                    if (!empty($listRecord->subscribers)) {

                        /** @var Subscribers $subscriber */
                        foreach ($listRecord->subscribers as $subscriber) {
                            // Assign email as key to not repeat subscriber
                            $sproutListsRecipientsInfo[$subscriber->email] = $subscriber->getAttributes();
                        }
                    }
                }
            }

            $listRecipients = [];
            if ($sproutListsRecipientsInfo) {
                foreach ($sproutListsRecipientsInfo as $listRecipient) {
                    $recipientModel = new Recipient();
                    $recipientModel->setAttributes($listRecipient, false);

                    $listRecipients[] = $recipientModel;
                }
            }

            $recipients = array_merge($recipients, $listRecipients);
        }

        return $recipients;
    }

    /**
     * The $email is the Notification Email or Campaign Email Element
     * The $object defined by the custom event
     *
     * @param NotificationEmail|CampaignEmail $email
     * @param mixed $object
     *
     * @return array
     * @throws \Exception
     */
    public function getRecipientsFromEmailElement($email, $object)
    {
        $recipients = [];

        $onTheFlyRecipients = $email->getRecipients($object);

        if (is_string($onTheFlyRecipients)) {
            $onTheFlyRecipients = explode(',', $onTheFlyRecipients);
        }

        if (count($onTheFlyRecipients)) {
            foreach ($onTheFlyRecipients as $index => $recipient) {
                $recipients[$index] = Recipient::create(
                    [
                        'firstName' => '',
                        'lastName' => '',
                        'email' => $recipient
                    ]
                );
            }
        }

        return $recipients;
    }

    /**
     * @param CampaignEmail $campaignEmail
     * @param CampaignType $campaignType
     * @param                   $errors
     *
     * @return array
     */
    public function getErrors(CampaignEmail $campaignEmail, CampaignType $campaignType, $errors)
    {
        $currentPluginHandle = Craft::$app->getRequest()->getSegment(1);
        $notificationEditSettingsUrl = UrlHelper::cpUrl($currentPluginHandle.'/settings/notifications/edit/'.$campaignType->id);

        if (empty($campaignType->template)) {
            $errors[] = Craft::t('sprout-base', 'Email Template setting is blank. <a href="{url}">Edit Settings</a>.',
                [
                    'url' => $notificationEditSettingsUrl
                ]);
        }

        return $errors;
    }
}
