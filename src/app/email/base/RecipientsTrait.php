<?php

namespace barrelstrength\sproutbase\app\email\base;

use barrelstrength\sproutbase\app\email\models\SimpleRecipient;
use barrelstrength\sproutbase\app\email\models\SimpleRecipientList;
use barrelstrength\sproutlists\records\Lists as ListsRecord;
use barrelstrength\sproutlists\records\Subscribers;
use Craft;
use craft\helpers\Json;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\RFCValidation;

trait RecipientsTrait
{
    /**
     * @var SimpleRecipient[]
     */
    private $onTheFlyRecipients = [];

    /**
     * Returns a list of On The Fly Recipients
     *
     * @return SimpleRecipient[]
     */
    public function getOnTheFlyRecipients(): array
    {
        return $this->onTheFlyRecipients;
    }

    /**
     * Sets a list of On The Fly Recipients
     *
     * @param array $onTheFlyRecipients Array of Email Addresses
     */
    public function setOnTheFlyRecipients($onTheFlyRecipients = [])
    {
        $recipients = [];

        if ($onTheFlyRecipients) {
            foreach ($onTheFlyRecipients as $onTheFlyRecipient) {
                $recipient = new SimpleRecipient();
                $recipient->email = $onTheFlyRecipient;

                $recipients[] = $recipient;
            }
        }

        $this->onTheFlyRecipients = $recipients;
    }

    /**
     * Returns if a Mailer supports Recipients
     *
     * This setting is mostly to support the Copy/Paste Mailer use case where a user is using
     * Sprout Email to prepare an email to be sent from another platform
     *
     * @return bool
     */
    public function hasRecipients(): bool
    {
        return true;
    }

    /**
     * Return true to allow and show mailer dynamic recipients
     *
     * @return bool
     */
    public function hasInlineRecipients(): bool
    {
        return false;
    }

    /**
     * Returns whether this Mailer supports mailing lists
     *
     * @return bool Whether this Mailer supports lists. Default is `true`.
     */
    public function hasLists(): bool
    {
        return true;
    }

    /**
     * Prepare the list data before we save it in the database
     *
     * @param $lists
     *
     * @return mixed
     */
    public function prepListSettings($lists)
    {
        return $lists;
    }

    /**
     * Returns the Lists available to this Mailer
     *
     * @return array
     */
    public function getLists(): array
    {
        return [];
    }

    /**
     * Returns the HTML for our List Settings on the Campaign and Notification Email edit page
     *
     * @param array $values
     *
     * @return null
     */
    public function getListsHtml($values = [])
    {
        return null;
    }

    /**
     * @return SimpleRecipientList
     * @throws \Throwable
     * @throws \yii\base\Exception
     */
    public function getRecipientList(): SimpleRecipientList
    {
        $recipientList = new SimpleRecipientList();

        $validator = new EmailValidator();
        $multipleValidations = new MultipleValidationWithAnd([
            new RFCValidation()
        ]);

        // Add any On The Fly Recipients to our List
        if ($onTheFlyRecipients = $this->getOnTheFlyRecipients()) {
            foreach ($onTheFlyRecipients as $onTheFlyRecipient) {
                if ($validator->isValid($onTheFlyRecipient->email, $multipleValidations)) {
                    $recipientList->addRecipient($onTheFlyRecipient);
                }

                $recipientList->addInvalidRecipient($onTheFlyRecipient);
            }

            // On the Fly Recipients are added in Test Modals and override all other
            // potential recipients.
            return $recipientList;
        }

        $recipientList = $this->getRecipients($this->recipients, $this);

        // @todo - test this integration
        if (Craft::$app->getPlugins()->getPlugin('sprout-lists')) {

            $listRecipients = $this->getRecipientsFromSelectedLists($this->listSettings);

            if ($listRecipients) {
                foreach ($listRecipients as $listRecipient) {

                    if ($validator->isValid($listRecipient->email, $multipleValidations)) {
                        $recipientList->addRecipient($listRecipient);
                    } else {
                        $recipientList->addInvalidRecipient($listRecipient);
                    }
                }
            }
        }

        return $recipientList;
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getSenderHtml(): string
    {
        // @todo - move these defaults to the Campaign Type model
        $defaultFromName = '';
        $defaultFromEmail = '';
        $defaultReplyTo = '';

        return Craft::$app->getView()->renderTemplate('sprout-base-email/_components/mailers/recipients-html', [
            'campaignEmail' => $this,
            'defaultFromName' => $defaultFromName,
            'defaultFromEmail' => $defaultFromEmail,
            'defaultReplyTo' => $defaultReplyTo,
        ]);
    }

    /**
     * Get SimpleRecipient objects group in valid and invalid emails
     *
     * @param $recipients
     * @param $email
     *
     * @return SimpleRecipientList
     * @throws \Throwable
     * @throws \yii\base\Exception
     */
    public function getRecipients($recipients, EmailElement $email): SimpleRecipientList
    {
        $recipientList = new SimpleRecipientList();

        $validator = new EmailValidator();
        $multipleValidations = new MultipleValidationWithAnd([
            new RFCValidation()
        ]);

        if (!empty($recipients)) {
            // Recipients are added as a comma-delimited list. While not on a formal list,
            // they are considered permanent and will be included alongside any more formal lists
            // Recipients can be dynamic values if matched to a value in the Event Object
            $recipients = Craft::$app->getView()->renderObjectTemplate($recipients, $email->getEventObject());

            $recipientArray = explode(',', $recipients);

            foreach ($recipientArray as $recipient) {
                $recipientModel = new SimpleRecipient();
                $recipientModel->email = trim($recipient);

                if ($validator->isValid($recipientModel->email, $multipleValidations)) {
                    $recipientList->addRecipient($recipientModel);
                } else {
                    $recipientList->addInvalidRecipient($recipientModel);
                }
            }
        }

        return $recipientList;
    }

    /**
     * @param $listSettings
     *
     * @return array
     */
    public function getRecipientsFromSelectedLists($listSettings): array
    {
        $listIds = [];
        // Convert json format to array
        if ($listSettings != null AND is_string($listSettings)) {
            $listIds = Json::decode($listSettings);
            $listIds = $listIds['listIds'];
        }

        if (empty($listIds)) {
            return [];
        }

        // Get all subscribers by list IDs from the Subscriber ListType
        $listRecords = ListsRecord::find()
            ->where([
                'id' => $listIds
            ])
            ->all();


        $sproutListsRecipientsInfo = [];
        if ($listRecords != null) {
            foreach ($listRecords as $listRecord) {
                if (!empty($listRecord->subscribers)) {

                    /** @var Subscribers $subscribers */
                    $subscribers = $listRecord->subscribers;

                    /** @var SimpleRecipient $subscriber */
                    foreach ($subscribers as $subscriber) {
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

                $firstName = $listRecipient['firstName'] ?? '';
                $lastName = $listRecipient['lastName'] ?? '';
                $name = $firstName.' '.$lastName;

                $recipientModel->name = trim($name) ?? null;
                $recipientModel->email = $listRecipient['email'] ?? null;

                $listRecipients[] = $recipientModel;
            }
        }

        return $listRecipients;
    }
}
