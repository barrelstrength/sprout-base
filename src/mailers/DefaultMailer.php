<?php

namespace barrelstrength\sproutbase\mailers;

use barrelstrength\sproutbase\base\TemplateTrait;
use barrelstrength\sproutbase\contracts\sproutemail\BaseMailer;
use barrelstrength\sproutbase\contracts\sproutemail\CampaignEmailSenderInterface;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutemail\elements\CampaignEmail;
use barrelstrength\sproutbase\elements\sproutemail\NotificationEmail;
use barrelstrength\sproutemail\models\CampaignType;
use barrelstrength\sproutemail\models\Response;
use barrelstrength\sproutbase\models\sproutemail\SimpleRecipient;
use barrelstrength\sproutemail\SproutEmail;
use craft\helpers\Template;
use Craft;
use craft\helpers\UrlHelper;
use craft\mail\Message;

class DefaultMailer extends BaseMailer implements CampaignEmailSenderInterface
{
    use TemplateTrait;
    /**
     * @var
     */
    protected $lists;

    /**
     * @return string
     */
    public function getName()
    {
        return 'defaultmailer';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Sprout Email';
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return Craft::t('sprout-base','Smart transactional email, easy recipient management, and advanced third party integrations.');
    }

    /**
     * @return bool
     */
    public function hasCpSection()
    {
        return true;
    }

    /**
     * @param array $settings
     *
     * @return string|\Twig_Markup
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getSettingsHtml(array $settings = [])
    {
        /** @noinspection NullCoalescingOperatorCanBeUsedInspection */
        $settings = isset($settings['settings']) ? $settings['settings'] : $this->getSettings();

        $html = Craft::$app->getView()->renderTemplate('sprout-base/_integrations/mailers/defaultmailer/settings', [
            'settings' => $settings
        ]);

        return Template::raw($html);
    }

    /**
     * @param NotificationEmail $notificationEmail
     * @param null              $object
     * @param bool              $useMockData
     *
     * @return bool
     * @throws \Exception
     */
    public function sendNotificationEmail(NotificationEmail $notificationEmail, $object = null, $useMockData = false)
    {
        $email = new Message();

        // Allow disabled emails to be tested
        if (!$notificationEmail->isReady() && !$useMockData) {
            return false;
        }

        $recipients = $this->prepareRecipients($notificationEmail, $object, $useMockData);

        if (empty($recipients)) {
            SproutBase::$app->utilities->addError('no-recipients', Craft::t('sprout-base', 'No recipients found.'));
        }

        $template = $notificationEmail->template;

        $renderEmail = $this->renderEmailTemplates($email, $template, $notificationEmail, $object);

        $email = $renderEmail->model;
        $body = $renderEmail->body;
        $htmlBody = $renderEmail->htmlBody;

        $templateErrors = SproutBase::$app->utilities->getErrors();

        if (empty($templateErrors) && (empty($body) || empty($htmlBody))) {
            $message = Craft::t('sprout-base', 'Email Text or HTML template cannot be blank. Check template setting.');

            SproutBase::$app->utilities->addError('blank-template', $message);
        }

        $processedRecipients = [];

        foreach ($recipients as $recipient) {
            $toEmail = $this->renderObjectTemplateSafely($recipient->email, $object);
            $name = $recipient->firstName.' '.$recipient->lastName;

            /**
             * @var $email Message
             */
            $email->setTo([$toEmail => $name]);

            if (array_key_exists($toEmail, $processedRecipients)) {
                continue;
            }

            try {
                $variables = [];

                if (Craft::$app->plugins->getPlugin('sprout-email')) {
                    $infoTable = SproutEmail::$app->sentEmails->createInfoTableModel('sprout-email', [
                        'emailType' => 'Notification',
                        'deliveryType' => $useMockData ? 'Test' : 'Live'
                    ]);

                    $variables = [
                        'email' => $notificationEmail,
                        'renderedEmail' => $renderEmail,
                        'object' => $object,
                        'recipients' => $recipients,
                        'processedRecipients' => null,
                        'info' => $infoTable
                    ];
                }

                if (SproutBase::$app->mailers->sendEmail($email, $variables)) {
                    $processedRecipients[] = $toEmail;
                } else {
                    return false;
                }
            } catch (\Exception $e) {
                SproutBase::$app->utilities->addError('fail-send-email', $e->getMessage());
            }
        }

        // Trigger on send notification event
        if (!empty($processedRecipients)) {
            $variables['processedRecipients'] = $processedRecipients;
        }

        return true;
    }

    /**
     * @param CampaignEmail $campaignEmail
     * @param CampaignType  $campaignType
     *
     * @return Response|mixed
     */
    public function sendCampaignEmail(CampaignEmail $campaignEmail, CampaignType $campaignType)
    {
        $lists = [];
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
                throw new \InvalidArgumentException(Craft::t('sprout-base', 'Empty recipients.'));
            }

            $result = SproutEmail::$app->getValidAndInvalidRecipients($recipients);

            $invalidRecipients = $result['invalid'];
            $validRecipients = $result['valid'];

            if (!empty($invalidRecipients)) {
                $invalidEmails = implode('<br/>', $invalidRecipients);

                throw new \InvalidArgumentException(Craft::t('sprout-base', 'The following recipient email addresses do not validate: {invalidEmails}',
                    [
                        'invalidEmails' => $invalidEmails
                    ]));
            }

            $recipients = $validRecipients;

            foreach ($recipients as $recipient) {
                try {
                    $params['recipient'] = $recipient;
                    $body = SproutEmail::$app->renderSiteTemplateIfExists($campaignType->template.'.txt', $params);

                    $email->setTextBody($body);
                    $htmlBody = SproutEmail::$app->renderSiteTemplateIfExists($campaignType->template, $params);

                    $email->setHtmlBody($htmlBody);
                    $name = $recipient->firstName.' '.$recipient->lastName;
                    $email->setTo([$recipient->email => $name]);

                    SproutEmail::$app->sendEmail($email);
                } catch (\Exception $e) {
                    throw $e;
                }
            }

            $response['emailModel'] = $email;

            return Response::createModalResponse(
                'sprout-email/_modals/response',
                [
                    'email' => $campaignEmail,
                    'campaign' => $campaignType,
                    'emailModel' => $response['emailModel'],
                    'recipentLists' => $lists,
                    'message' => Craft::t('sprout-email', 'Campaign sent successfully to email.'),
                ]
            );
        } catch (\Exception $e) {
            SproutEmail::$app->utilities->addError('fail-campaign-email', $e->getMessage());

            return Response::createErrorModalResponse(
                'sprout-email/_modals/response',
                [
                    'email' => $campaignEmail,
                    'campaign' => $campaignType,
                    'message' => Craft::t('sprout-email', $e->getMessage()),
                ]
            );
        }
    }

    /**
     * @param CampaignEmail $campaignEmail
     * @param CampaignType  $campaignType
     *
     * @return mixed|string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
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

        return Craft::$app->getView()->renderTemplate('sprout-email/_modals/campaigns/prepareEmailSnapshot', [
            'campaignEmail' => $campaignEmail,
            'campaignType' => $campaignType,
            'recipients' => $recipients,
            'errors' => $errors
        ]);
    }

    /**
     * Get all supported Lists. Requires Sprout Lists.
     *
     * @return array|void
     */
    public function getLists()
    {
        /**
         * @todo update when sprout list when we develop sprout lists plugin
         */
        /*if ($this->lists === null && Craft::$app->getPlugins()->getPlugin('sprout-lists') != null)
        {
            $listType = SproutLists::$app->lists->getListType('subscriber');

            $this->lists = $listType ? $listType->getLists() : array();
        }

        return $this->lists;*/
    }

    /**
     * Get the HTML for our List Settings on the Notification Email edit page
     *
     * @param array $values
     *
     * @return null|string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getListsHtml(array $values = [])
    {
        $selected = [];
        $options = [];
        $lists = $this->getLists();

        if (count($lists)) {
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
        } else {
            // Do not display lists if sprout plugin is disabled
            return '';
        }

        $listIds = isset($values['listIds']) ?? $values['listIds'];

        if (is_array($listIds) && count($listIds)) {
            foreach ($listIds as $key => $listId) {
                $selected[] = $listId;
            }
        }

        return Craft::$app->getView()->renderTemplate('sprout-email/_integrations/mailers/defaultmailer/lists', [
            'options' => $options,
            'values' => $selected,
        ]);
    }

    /**
     * @return bool
     */
    public function hasInlineRecipients()
    {
        return true;
    }

    /**
     * @param $email
     * @param $object
     * @param $useMockData
     *
     * @return array
     * @throws \Exception
     */
    protected function prepareRecipients($email, $object, $useMockData)
    {
        // Get recipients for test notifications
        if ($useMockData) {
            $recipients = Craft::$app->getRequest()->getBodyParam('recipients');

            if (empty($recipients)) {
                return [];
            }

            $recipients = Craft::$app->getRequest()->getBodyParam('recipients');

            $result = SproutEmail::$app->getValidAndInvalidRecipients($recipients);

            $invalidRecipients = $result['invalid'];
            $validRecipients = $result['valid'];

            if (!empty($invalidRecipients)) {
                $invalidEmails = implode('<br>', $invalidRecipients);

                throw new \InvalidArgumentException(Craft::t('sprout-base', 'Recipient email addresses do not validate: <br /> {invalidEmails}', [
                    'invalidEmails' => $invalidEmails
                ]));
            }

            return $validRecipients;
        }

        // Get recipients for live emails
        // @todo Craft 3 - improve and standardize how we use entryRecipents and dynamicRecipients
        $entryRecipients = $this->getRecipientsFromCampaignEmailModel($email, $object);

        $dynamicRecipients = SproutBase::$app->notifications->getDynamicRecipientsFromElement($object);

        $recipients = array_merge(
            $entryRecipients,
            $dynamicRecipients
        );

        // @todo implement this when we develop sprout lists plugin
//        if (Craft::$app->getPlugins()->getPlugin('sprout-lists') != null) {
//            // Get all subscribers by list IDs from the SproutLists_SubscriberListType
//            $listRecords = SproutLists_ListRecord::model()->findAllByPk($email->listSettings['listIds']);
//
//            $sproutListsRecipientsInfo = array();
//            if ($listRecords != null)
//            {
//                foreach ($listRecords as $listRecord)
//                {
//                    if (!empty($listRecord->subscribers))
//                    {
//                        foreach ($listRecord->subscribers as $subscriber)
//                        {
//                            // Assign email as key to not repeat subscriber
//                            $sproutListsRecipientsInfo[$subscriber->email] = $subscriber->getAttributes();
//                        }
//                    }
//                }
//            }
//
//            $simpleRecipientModel = new SimpleRecipient();
//            $sproutListsRecipients = $simpleRecipientModel->setAttributes($sproutListsRecipientsInfo, false);
//
//            $recipients = array_merge($recipients, $sproutListsRecipients);
//        }

        return $recipients;
    }

    /**
     * @param $campaignEmail
     * @param $element
     *
     * @return array
     */
    public function getRecipientsFromCampaignEmailModel($campaignEmail, $element)
    {
        $recipients = [];

        $onTheFlyRecipients = $campaignEmail->getRecipients($element);

        if (is_string($onTheFlyRecipients)) {
            $onTheFlyRecipients = explode(',', $onTheFlyRecipients);
        }

        if (count($onTheFlyRecipients)) {
            foreach ($onTheFlyRecipients as $index => $recipient) {
                $recipients[$index] = SimpleRecipient::create(
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
     * @param CampaignEmail     $campaignEmail
     * @param CampaignType      $campaignType
     * @param                   $errors
     *
     * @return array
     */
    public function getErrors(CampaignEmail $campaignEmail, CampaignType $campaignType, $errors)
    {
        $currentBase = Craft::$app->getRequest()->getSegment(1);
        $notificationEditSettingsUrl = UrlHelper::cpUrl($currentBase.'/settings/notifications/edit/'.$campaignType->id);

        if (empty($campaignType->template)) {
            $errors[] = Craft::t('sprout-base', 'Email Template setting is blank. <a href="{url}">Edit Settings</a>.',
                [
                    'url' => $notificationEditSettingsUrl
                ]);
        }

        return $errors;
    }
}
