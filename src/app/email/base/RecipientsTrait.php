<?php

namespace barrelstrength\sproutbase\app\email\base;

use barrelstrength\sproutbase\app\email\models\SimpleRecipient;
use barrelstrength\sproutbase\app\email\models\SimpleRecipientList;
use barrelstrength\sproutbase\app\reports\elements\Report;
use Craft;
use craft\helpers\Json;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\RFCValidation;
use Throwable;
use yii\base\Exception;
use yii\web\NotFoundHttpException;

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
     * @return bool Whether this Mailer supports lists. Default is `false`.
     */
    public function hasLists(): bool
    {
        return false;
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
     * Returns the SubscriberList available to this Mailer
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
     * @noinspection PhpUnusedParameterInspection
     */
    public function getListsHtml($values = [])
    {
        return null;
    }

    /**
     * @param EmailElement $email
     *
     * @return SimpleRecipientList
     * @throws Throwable
     * @throws Exception
     */
    public function getRecipientList(EmailElement $email): SimpleRecipientList
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
                } else {
                    $recipientList->addInvalidRecipient($onTheFlyRecipient);
                }
            }

            // On the Fly Recipients are added in Test Modals and override all other
            // potential recipients.
            return $recipientList;
        }

        $recipientList = $this->getRecipients($email, $email->recipients);

        $listRecipients = $this->getRecipientsFromSelectedLists($email->listSettings);

        if ($listRecipients) {
            foreach ($listRecipients as $listRecipient) {

                if ($validator->isValid($listRecipient->email, $multipleValidations)) {
                    $recipientList->addRecipient($listRecipient);
                } else {
                    $recipientList->addInvalidRecipient($listRecipient);
                }
            }
        }

        return $recipientList;
    }

    /**
     * Get SimpleRecipient objects group in valid and invalid emails
     *
     * @param EmailElement $email
     *
     * @param              $recipients
     *
     * @return SimpleRecipientList
     * @throws Throwable
     * @throws Exception
     */
    public function getRecipients(EmailElement $email, $recipients): SimpleRecipientList
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
     * @throws NotFoundHttpException
     */
    public function getRecipientsFromSelectedLists($listSettings = null): array
    {
        $listIds = [];

        // Convert json format to array
        if ($listSettings !== null && is_string($listSettings)) {
            $listIds = Json::decode($listSettings);
            $listIds = $listIds['listIds'];
        }

        if (empty($listIds)) {
            return [];
        }

        $listRecipients = [];
        $emailsOnList = [];

        foreach ($listIds as $reportId) {

            /** @var Report $report */
            $report = Craft::$app->elements->getElementById($reportId, Report::class);

            if (!$report) {
                throw new NotFoundHttpException('Report not found.');
            }

            $dataSource = $report->getDataSource();

            if (!$dataSource) {
                throw new NotFoundHttpException('Data Source not found.');
            }

            $labels = $dataSource->getDefaultLabels($report);
            $values = $dataSource->getResults($report);

            if (empty($labels) && !empty($values)) {
                $firstItemInArray = reset($values);
                $labels = array_keys($firstItemInArray);
            }

            $cleanLabels = array_map(static function($value) {
                // Replace any non-word characters with an underscore
                $value = preg_replace('/[\W]/', '_', $value);

                // Replaces multiple underscores with a single underscore
                return preg_replace('/_+/', '_', $value);
            }, $labels);

            $emailColumn = $report->emailColumn;

            foreach ($values as $value) {
                // Match our recipient with the updated, clean version of the array keys that can be used in templates
                $recipientArray = array_combine($cleanLabels, $value);

                $recipientModel = new SimpleRecipient();

                $email = $recipientArray[$emailColumn] ?? null;

                // Skip duplicate emails, only process the first email found
                if (in_array($email, $emailsOnList, true)) {
                    continue;
                }

                $recipientModel->email = $email;

                // Track the emails we have added to the list so we can check for duplicates
                $emailsOnList[] = $email;

                unset($recipientArray[$emailColumn]);

                $recipientModel->setCustomFields($recipientArray);

                $listRecipients[] = $recipientModel;
            }
        }

        return $listRecipients;
    }
}
