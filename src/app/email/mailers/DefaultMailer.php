<?php

namespace barrelstrength\sproutbase\app\email\mailers;

use barrelstrength\sproutbase\app\campaigns\elements\CampaignEmail;
use barrelstrength\sproutbase\app\email\base\EmailElement;
use barrelstrength\sproutbase\app\email\base\Mailer;
use barrelstrength\sproutbase\app\email\base\NotificationEmailSenderInterface;
use barrelstrength\sproutbase\app\email\elements\NotificationEmail;
use barrelstrength\sproutbase\app\reports\elements\Report;
use barrelstrength\sproutbase\app\reports\records\Report as ReportRecord;
use barrelstrength\sproutbase\app\sentemail\services\SentEmails;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutforms\fields\formfields\FileUpload;
use Craft;
use craft\base\Element;
use craft\base\LocalVolumeInterface;
use craft\db\Query;
use craft\elements\Asset;
use craft\elements\db\AssetQuery;
use craft\fields\Assets;
use craft\helpers\FileHelper;
use craft\helpers\Json;
use craft\helpers\Template;
use craft\mail\Message;
use craft\volumes\Local;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Markup;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 *
 * @property string $description
 */
class DefaultMailer extends Mailer implements NotificationEmailSenderInterface
{
    /**
     * @var
     */
    protected $lists = [];

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return 'Sprout Email';
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return Craft::t('sprout', 'Smart transactional email, easy recipient management, and advanced third party integrations.');
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
     * @param array $settings
     *
     * @return Markup
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getSettingsHtml(array $settings = []): Markup
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
     * @throws Throwable
     * @throws InvalidConfigException
     */
    public function sendNotificationEmail(NotificationEmail $notificationEmail): bool
    {
        $mailer = $notificationEmail->getMailer();
        /**
         * @var $message Message
         */
        $message = $mailer->getMessage($notificationEmail);

        $externalPaths = [];

        /**
         * @var $object Element
         */
        $object = $notificationEmail->getEventObject();

        // Adds support for attachments
        if ($notificationEmail->enableFileAttachments &&
            $object instanceof Element &&
            method_exists($object, 'getFieldLayout') &&
            $object->getFieldLayout()) {

            foreach ($object->getFieldLayout()->getFields() as $field) {
                if (get_class($field) === FileUpload::class || get_class($field) === Assets::class) {
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
            $notificationEmail->addError('recipients', Craft::t('sprout', 'No recipients found.'));
        }

        $recipientCc = $mailer->getRecipients($notificationEmail, $notificationEmail->cc);
        $recipientBc = $mailer->getRecipients($notificationEmail, $notificationEmail->bcc);

        if ($notificationEmail->hasErrors() || !$recipients) {
            return false;
        }

        // Only track Sent Emails if Sprout Email is installed ...
        if (Craft::$app->plugins->getPlugin('sprout-email')) {

            $infoTable = SproutBase::$app->sentEmails->createInfoTableModel('sprout-email', [
                'emailType' => $notificationEmail->displayName(),
                'mailer' => self::displayName()
            ]);

            $deliveryTypes = $infoTable->getDeliveryTypes();
            $infoTable->deliveryType = $notificationEmail->getIsTest() ? $deliveryTypes['Test'] : $deliveryTypes['Live'];

            $variables = [
                SentEmails::SENT_EMAIL_MESSAGE_VARIABLE => $infoTable
            ];

            $message->variables = $variables;
        }

        if ($notificationEmail->sendMethod === 'singleEmail') {
            if ($bcc = $recipientBc->getRecipientEmails()) {
                $message->setBcc($bcc);
            }

            if ($cc = $recipientCc->getRecipientEmails()) {
                $message->setCc($cc);
            }
        }

        $this->sendEmail($notificationEmail, $message, $recipients);

        $this->deleteExternalPaths($externalPaths);

        return true;
    }

    /**
     * @param CampaignEmail $campaignEmail
     *
     * @return bool
     * @throws Exception
     * @throws Throwable
     */
    public function sendTestCampaignEmail(CampaignEmail $campaignEmail): bool
    {
        $message = $this->getMessage($campaignEmail);

        $recipientList = $this->getRecipientList($campaignEmail);

        $recipients = $recipientList->getRecipients();

        if (empty($recipients)) {
            $campaignEmail->addError('recipients', Craft::t('sprout', 'No recipients found.'));
        }

        if (!$recipients) {
            return false;
        }

        $this->sendEmail($campaignEmail, $message, $recipients);

        return true;
    }

    /**
     * @inheritdoc
     *
     */
    public function getPrepareModalHtml(EmailElement $email): string
    {
        if (!empty($email->recipients)) {
            $recipients = $email->recipients;
        }

        if (empty($recipients)) {
            $recipients = Craft::$app->getUser()->getIdentity()->email;
        }

        if (empty($email->getEmailTemplateId())) {
            $email->addError('emailTemplateId', Craft::t('sprout', 'No email template setting found.'));
        }

        return Craft::$app->getView()->renderTemplate('sprout-base-email/_modals/prepare-email-snapshot', [
            'email' => $email,
            'recipients' => $recipients
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
     * @return bool
     */
    public function hasLists(): bool
    {
        $sproutReportsIsEnabled = Craft::$app->getPlugins()->isPluginEnabled('sprout-reports');
        $sproutReportsTableExists = Craft::$app->db->tableExists(ReportRecord::tableName());

        if ($sproutReportsIsEnabled && $sproutReportsTableExists) {
            return (new Query())
                ->select('id')
                ->from(ReportRecord::tableName())
                ->where(['not', ['emailColumn' => null]])
                ->exists();
        }

        return false;
    }

    /**
     * @inheritdoc
     *
     */
    public function getLists(): array
    {
        if (empty($this->lists)) {
            // Get all selected Mailing List Reports
            // Prepare SimpleRecipientList of their data
            // Assign it to $this->lists and return it...
            // @todo - how can we attach all fields as arbitrary attributes to be used as personalization in email?
            // Do we even need this to be a model?

            // Assign lists
//            $this->lists = $listType ? $listType->getLists() : [];
        }

        return $this->lists;
    }

    /**
     * @param array $values
     *
     * @return string|null
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getListsHtml($values = [])
    {
        $selectedElements = [];

        $listIds = [];

        // Convert json format to array
        if ($values !== null && is_string($values)) {
            $listIds = Json::decode($values);
            $listIds = $listIds['listIds'];
        }

        if (!empty($listIds)) {
            foreach ($listIds as $key => $listId) {
                $selectedElements[] = Craft::$app->elements->getElementById($listId, Report::class);
            }
        }

        return Craft::$app->getView()->renderTemplate('sprout-base-email/_components/mailers/defaultmailer/lists', [
            'selectedElements' => $selectedElements,
        ]);
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
     * @throws InvalidConfigException
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
     * @throws InvalidConfigException
     */
    protected function getLocalAssetFilePath(Asset $asset): string
    {
        /**
         * @var $volume LocalVolumeInterface
         */
        $volume = $asset->getVolume();

        $path = $volume->getRootPath().DIRECTORY_SEPARATOR.$asset->getPath();

        return FileHelper::normalizePath($path);
    }

    private function sendEmail(EmailElement $emailElement, Message $message, $recipients)
    {
        $processedRecipients = [];
        $prepareRecipients = [];
        $mailer = Craft::$app->getMailer();

        if ($emailElement->sendMethod === 'singleEmail') {
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
                    $emailElement->addError('send-failure', $e->getMessage());
                }
            }
        }

        return $emailElement;
    }
}
