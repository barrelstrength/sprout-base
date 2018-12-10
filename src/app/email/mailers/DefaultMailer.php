<?php

namespace barrelstrength\sproutbase\app\email\mailers;

use barrelstrength\sproutbase\app\email\base\EmailElement;
use barrelstrength\sproutbase\app\email\base\Mailer;
use barrelstrength\sproutbase\app\email\base\NotificationEmailSenderInterface;
use barrelstrength\sproutbase\app\email\elements\NotificationEmail;
use barrelstrength\sproutemail\elements\CampaignEmail;
use barrelstrength\sproutemail\models\CampaignType;
use barrelstrength\sproutemail\services\SentEmails;
use barrelstrength\sproutemail\SproutEmail;
use barrelstrength\sproutforms\fields\formfields\FileUpload;
use barrelstrength\sproutlists\listtypes\SubscriberListType;
use barrelstrength\sproutlists\SproutLists;
use craft\base\Element;
use craft\base\LocalVolumeInterface;
use craft\elements\Asset;
use craft\elements\db\AssetQuery;
use craft\fields\Assets;
use craft\helpers\Json;
use craft\helpers\Template;
use Craft;
use craft\mail\Message;
use craft\volumes\Local;
use yii\base\Exception;

class DefaultMailer extends Mailer implements NotificationEmailSenderInterface
{
    /**
     * @var
     */
    protected $lists = [];

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'Sprout Email';
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return Craft::t('sprout-base', 'Smart transactional email, easy recipient management, and advanced third party integrations.');
    }

    /**
     * @inheritdoc
     */
    public function hasCpSection(): bool
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
     * @param EmailElement $notificationEmail
     *
     * @return bool
     * @throws Exception
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    public function sendNotificationEmail(NotificationEmail $notificationEmail): bool
    {
        $mailer = $notificationEmail->getMailer();
        /**
         * @var $message Message
         */
        $message = $mailer->getMessage($notificationEmail);

        $externalPaths = [];
        $object = $notificationEmail->getEventObject();

        // Adds support for attachments
        if ($notificationEmail->enableFileAttachments && $object instanceof Element && method_exists($object, 'getFields')) {
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

        $recipientList = $mailer->getRecipientList($notificationEmail);

        $recipients = $recipientList->getRecipients();

        if (empty($recipients)) {
            $notificationEmail->addError('recipients', Craft::t('sprout-base', 'No recipients found.'));
        }

        $recipientCc = $mailer->getRecipients($notificationEmail, $notificationEmail->cc);
        $recipientBc = $mailer->getRecipients($notificationEmail, $notificationEmail->bcc);

        if (!$recipients) {
            return false;
        }

        // Only track Sent Emails if Sprout Email is installed
        if (Craft::$app->plugins->getPlugin('sprout-email')) {

            $infoTable = SproutEmail::$app->sentEmails->createInfoTableModel('sprout-email', [
                'emailType' => $notificationEmail->displayName(),
                'mailer' => $this->getName()
            ]);

            $deliveryTypes = $infoTable->getDeliveryTypes();
            $infoTable->deliveryType = $notificationEmail->getIsTest() ? $deliveryTypes['Test'] : $deliveryTypes['Live'];

            $variables = [
                SentEmails::SENT_EMAIL_MESSAGE_VARIABLE => $infoTable
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
             * Assigning email with name array does not work on craft
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
                $path = $this->getLocalAssetFilePath($asset);
            } else {
                // External Asset sources
                $path = $asset->getCopyOfFile();
                // let's save the path to delete it after sent
                $externalPaths[] = $path;
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
    protected function getLocalAssetFilePath(Asset $asset): string
    {
        /** @var LocalVolumeInterface $volume */
        $volume = $asset->getVolume();

        return $volume->getRootPath().$asset->getFolder()->path.DIRECTORY_SEPARATOR.$asset->filename;
    }

    /**
     * @inheritdoc
     *
     * @throws Exception
     * @throws \Twig_Error_Loader
     */
    public function getPrepareModalHtml(EmailElement $email): string
    {
        if (!empty($email->recipients)) {
            $recipients = $email->recipients;
        }

        if (empty($recipients)) {
            $recipients = Craft::$app->getUser()->getIdentity()->email;
        }

        $errors = [];
// @todo - re-implement error handling using Yii Component getErrors behavior
//        $errors = $this->getErrors($campaignEmail, $campaignType, $errors);

        return Craft::$app->getView()->renderTemplate('sprout-base-email/_modals/campaigns/prepare-email-snapshot', [
            'campaignEmail' => $email,
            'campaignType' => $email->getCampaignType(),
            'recipients' => $recipients,
            'errors' => $errors
        ]);
    }

    /**
     * @inheritdoc
     */
    public function hasInlineRecipients(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     *
     * @throws Exception
     */
    public function getLists(): array
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
//    public function getErrors(CampaignEmail $campaignEmail, CampaignType $campaignType, $errors)
//    {
//        $currentPluginHandle = Craft::$app->getRequest()->getSegment(1);
//        $notificationEditSettingsUrl = UrlHelper::cpUrl($currentPluginHandle.'/settings/notifications/edit/'.$campaignType->id);
//
//        if (empty($campaignType->template)) {
//            $errors[] = Craft::t('sprout-base', 'Email Template setting is blank. <a href="{url}">Edit Settings</a>.', [
//                'url' => $notificationEditSettingsUrl
//            ]);
//        }
//
//        return $errors;
//    }
}
