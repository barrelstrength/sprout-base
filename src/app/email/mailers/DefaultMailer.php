<?php

namespace barrelstrength\sproutbase\app\email\mailers;

use barrelstrength\sproutbase\app\email\base\EmailTemplateTrait;
use barrelstrength\sproutbase\app\email\base\Mailer;
use barrelstrength\sproutbase\app\email\base\NotificationEmailSenderInterface;
use barrelstrength\sproutbase\app\email\models\Message;
use barrelstrength\sproutbase\app\email\models\SimpleRecipientList;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutemail\elements\CampaignEmail;
use barrelstrength\sproutbase\app\email\elements\NotificationEmail;
use barrelstrength\sproutemail\models\CampaignType;
use barrelstrength\sproutbase\app\email\models\Response;
use barrelstrength\sproutbase\app\email\models\SimpleRecipient;
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
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\RFCValidation;
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

                    if ($recipient->name) {
                        $email->setTo([$recipient->email => $recipient->name]);
                    } else {
                        $email->setTo($recipient->email);
                    }

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
     * @todo - the $useMockData parameter is not in the parent method signature and likely not in use.
     * Perhaps update this to be a boolean on the sproutemail_notificationemail model
     *
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
            // @todo can we remove this in favor of adding errors to the NotificationEmail model?
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

        // @todo can we remove this in favor of adding errors to the NotificationEmail model?
        // Where do these template errors get added?
        $templateErrors = SproutBase::$app->emailErrorHelper->getErrors();
        SproutBase::error($templateErrors);

        if (empty($templateErrors) && (empty($body) || empty($htmlBody))) {
            $message = Craft::t('sprout-base', 'Email Text or HTML template cannot be blank. Check template setting.');

            // @todo can we remove this in favor of adding errors to the NotificationEmail model?
            SproutBase::$app->emailErrorHelper->addError('blank-template', $message);
        }

        $externalPaths = [];

        // Adds support for attachments
        if ($notificationEmail->enableFileAttachments) {
            if ($object instanceof Element && method_exists($object, 'getFields')) {
                foreach ($object->getFields() as $field) {
                    if (get_class($field) === 'barrelstrength\\sproutforms\\fields\\formfields\\FileUpload' OR get_class($field) === Assets::class) {
                        $query = $object->{$field->handle};

                        if ($query instanceof AssetQuery) {
                            $assets = $query->all();

                            $this->attachAssetFilesToEmailModel($message, $assets, $externalPaths);
                        }
                    }
                }
            }
        }

        $processedRecipients = [];

        foreach ($recipients as $recipient) {

            // @todo - is this happening a second time? This should have been handled in prepareRecipients() above.
//            $toEmail = $this->renderObjectTemplateSafely($recipient->email, $object);

//            $name = $recipient->name;

            if ($recipient->name) {
                $message->setTo([$recipient->email => $recipient->name]);
            } else {
                $message->setTo($recipient->email);
            }

            if (array_key_exists($recipient->email, $processedRecipients)) {
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
                    $processedRecipients[] = $recipient->email;
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

        $this->deleteExternalPaths($externalPaths);

        return true;
    }

    /**
     * @param $externalPaths
     */
    protected function deleteExternalPaths($externalPaths)
    {
        foreach ($externalPaths as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    /**
     * @param Message $message
     * @param Asset[] $assets
     * @param array   $externalPaths
     *
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
            if ($path) {
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
        return $asset->getVolume()->getRootPath().$asset->getFolder()->path.DIRECTORY_SEPARATOR.$asset->filename;
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

        return Craft::$app->getView()->renderTemplate('sprout-base-email/_modals/campaigns/prepare-email-snapshot', [
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
     * 0. Process an on the fly modal recipients field for a test email
     * 1. Process plain text emails in recipients field
     * 2. Process object syntax code in recipients field
     * 3. Process lists
     *
     * Add errors to a model that helps us display them later
     *
     * Check a setting that helps us decide if we
     * loop through emails individually or send all emails in the TO field together.
     *
     * @param NotificationEmail $notificationEmail
     * @param                   $object
     * @param                   $useMockData
     *
     * @return SimpleRecipient[]
     * @throws \Exception
     */
    protected function prepareRecipients(NotificationEmail $notificationEmail, $object, $useMockData)
    {
        if ($useMockData) {
            return $this->getRecipientsFromSendTestModal();
        }

        $recipients = [];
        $listRecipients = [];

        if ($notificationEmail->recipients) {
            $recipients = $this->getRecipientsFromNotificationEmail($notificationEmail->recipients, $object);
        }

        // @todo - doesn't return SimpleRecipient List array like above elements
        if (Craft::$app->getPlugins()->getPlugin('sprout-lists')) {
            $listRecipients = $this->getRecipientsFromSelectedLists($notificationEmail->listSettings);
        }

        $recipients = array_merge($recipients, $listRecipients);

        // @todo inconsistent return types above. Test that models are being standardized and returned properly for use.
        return $recipients;
    }

    /**
     * The Send Test modal allows a user to send a test to an
     * email address they add on the fly. It supports a comma-delimited
     * list of plain text emails.
     *
     * @return array|SimpleRecipient[]
     */
    public function getRecipientsFromSendTestModal()
    {
        $onTheFlyRecipients = Craft::$app->getRequest()->getBodyParam('recipients');

        if (empty($onTheFlyRecipients)) {
            return [];
        }

        $recipientList = $this->buildRecipientList($onTheFlyRecipients);

        if ($recipientList->getInvalidRecipients()) {
            throw new InvalidArgumentException(Craft::t('sprout-base', 'An Email Address provided does not validate.'));
        }

        return $recipientList->getRecipients();
    }

    /**
     * @param string      $unprocessedRecipients
     * @param object|null $object
     *
     * @return array|SimpleRecipient[]
     * @throws Exception
     */
    public function getRecipientsFromNotificationEmail($unprocessedRecipients, $object)
    {
        $recipients = Craft::$app->getView()->renderObjectTemplate($unprocessedRecipients, $object);

        if (empty($recipients)) {
            return [];
        }

        $recipientList = $this->buildRecipientList($recipients);

        if ($recipientList->getInvalidRecipients()) {
            throw new InvalidArgumentException(Craft::t('sprout-base', 'An Email Address provided does not validate.'));
        }

        return $recipientList->getRecipients();
    }

    public function getRecipientsFromSelectedLists($listSettings)
    {
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

        // @todo - review what attributes are passed for recipients.
        $listRecipients = [];
        if ($sproutListsRecipientsInfo) {
            foreach ($sproutListsRecipientsInfo as $listRecipient) {
                $recipientModel = new SimpleRecipient();
                $recipientModel->name = $listRecipient['name'] ?? null;
                $recipientModel->email = $listRecipient['email'] ?? null;

                $listRecipients[] = $recipientModel;
            }
        }

        return $listRecipients;
    }

    /**
     * @param CampaignEmail     $campaignEmail
     * @param CampaignType      $campaignType
     * @param                   $errors
     *
     * @return array
     */
    public function getErrors(CampaignEmail $campaignEmail, CampaignType $campaignType, $errors)
    {
        $currentPluginHandle = Craft::$app->getRequest()->getSegment(1);
        $notificationEditSettingsUrl = UrlHelper::cpUrl($currentPluginHandle.'/settings/notifications/edit/'.$campaignType->id);

        if (empty($campaignType->template)) {
            $errors[] = Craft::t('sprout-base', 'Email Template setting is blank. <a href="{url}">Edit Settings</a>.', [
                'url' => $notificationEditSettingsUrl
            ]);
        }

        return $errors;
    }

    /**
     * @param $campaignEmail
     *
     * @return string
     * @throws Exception
     * @throws \Twig_Error_Loader
     */
    public function getRecipientsHtml($campaignEmail)
    {
        $defaultFromName = $this->settings->fromName ?? null;
        $defaultFromEmail = $this->settings->fromEmail ?? null;
        $defaultReplyTo = $this->settings->replyTo ?? null;

        return Craft::$app->getView()->renderTemplate('sprout-base-email/_components/mailers/recipients-html', [
            'campaignEmail' => $campaignEmail,
            'defaultFromName' => $defaultFromName,
            'defaultFromEmail' => $defaultFromEmail,
            'defaultReplyTo' => $defaultReplyTo,
        ]);
    }
}
