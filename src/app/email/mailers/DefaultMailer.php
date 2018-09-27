<?php

namespace barrelstrength\sproutbase\app\email\mailers;

use barrelstrength\sproutbase\app\email\base\EmailElement;
use barrelstrength\sproutbase\app\email\base\Mailer;
use barrelstrength\sproutbase\app\email\base\NotificationEmailSenderInterface;
use barrelstrength\sproutemail\elements\CampaignEmail;
use barrelstrength\sproutbase\app\email\elements\NotificationEmail;
use barrelstrength\sproutemail\models\CampaignType;
use barrelstrength\sproutemail\SproutEmail;
use barrelstrength\sproutforms\fields\formfields\FileUpload;
use barrelstrength\sproutlists\listtypes\SubscriberListType;
use barrelstrength\sproutlists\SproutLists;
use craft\base\Element;
use craft\elements\Asset;
use craft\elements\db\AssetQuery;
use craft\fields\Assets;
use craft\helpers\Json;
use craft\helpers\Template;
use Craft;
use craft\helpers\UrlHelper;
use craft\mail\Message;
use craft\volumes\Local;
use yii\base\Exception;

class DefaultMailer extends Mailer implements NotificationEmailSenderInterface
{
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
     * @throws Exception
     * @throws \Twig_Error_Loader
     * @throws \yii\base\InvalidConfigException
     */
    public function sendNotificationEmail(EmailElement $notificationEmail)
    {
        $mailer = $notificationEmail->getMailer();
        /**
         * @var $message Message
         */
        $message = $mailer->getMessage($notificationEmail);

        $externalPaths = [];
        $object = $notificationEmail->getEventObject();

        // Adds support for attachments
        if ($notificationEmail->enableFileAttachments) {
            if ($object instanceof Element && method_exists($object, 'getFields')) {
                foreach ($object->getFields() as $field) {
                    if (get_class($field) === FileUpload::class OR get_class($field) === Assets::class) {
                        $query = $object->{$field->handle};

                        if ($query instanceof AssetQuery) {
                            $assets = $query->all();

                            $this->attachAssetFilesToEmailModel($message, $assets, $externalPaths);
                        }
                    }
                }
            }
        }

        $recipientList = $mailer->getRecipientList($notificationEmail);

        // @todo - we throw an error if RecipientLIst is empty and then immediately check recipients and do the same... do we need both?
        if (empty($recipientList)) {
            $notificationEmail->addError('recipients', Craft::t('sprout-base', 'No recipients found.'));
        }

        $recipients = $recipientList->getRecipients();

        $recipientCc = $mailer->getRecipients($notificationEmail->cc, $notificationEmail);
        $recipientBc = $mailer->getRecipients($notificationEmail->bcc, $notificationEmail);

        if (!$recipients) {
            return false;
        }

        // Only track Sent Emails if Sprout Email is installed
        if (Craft::$app->plugins->getPlugin('sprout-email')) {

            $infoTable = SproutEmail::$app->sentEmails->createInfoTableModel('sprout-email', [
                'emailType' => $notificationEmail->displayName(),
                'mailer' => $this->getName(),
                'deliveryType' => $notificationEmail->getIsTest() ? Craft::t('sprout-base', 'Test') : Craft::t('sprout-base', 'Live')
            ]);

            $variables = [
                'info' => $infoTable
            ];

            $message->variables = $variables;
        }

        $processedRecipients = [];
        $prepareRecipients = [];
        $mailer = Craft::$app->getMailer();

        if ($bcc = $recipientBc->getRecipientEmails()) {
            $message->setBcc($bcc);
        }

        if ($cc = $recipientCc->getRecipientEmails()) {
            $message->setCc($cc);
        }

        if ($notificationEmail->singleEmail) {
             /*
              * Assigning email with name array does not work on carft
              * [$recipient->email => $recipient->name]
              */
            foreach ($recipients as $key => $recipient) {
              $prepareRecipients[] = $recipient->email;
            }
            $message->setTo($prepareRecipients);

            $mailer->send($message);

        } else {
            foreach ($recipients as $recipient) {

                if ($recipient->name) {
                    $message->setTo([$recipient->email => $recipient->name]);
                } else {
                    $message->setTo($recipient->email);
                }

                // Skip any emails that we have already processed
                if (array_key_exists($recipient->email, $processedRecipients)) {
                    continue;
                }

                try {
                    if ($mailer->send($message)) {
                        $processedRecipients[] = $recipient->email;
                    } else {
                        //  If it fails proceed to next email
                        continue;
                    }
                } catch (\Exception $e) {
                    $notificationEmail->addError('send-failure', $e->getMessage());
                }
            }
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

        if (empty($lists)) {
            return '';
        }

        foreach ($lists as $list) {
            $listName = $list->name;

            if ($list->totalSubscribers) {
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
