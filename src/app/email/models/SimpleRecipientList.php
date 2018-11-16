<?php

namespace barrelstrength\sproutbase\app\email\models;

use craft\base\Model;

/**
 * Represents a list of Simple Recipients
 */
class SimpleRecipientList extends Model
{
    /**
     * An array of all valid recipients
     *
     * @var SimpleRecipient[]
     */
    protected $recipients;

    /**
     * An array of invalid recipients
     *
     * @var array
     */
    protected $invalidRecipients;

    public function addRecipient(SimpleRecipient $recipient)
    {
        $this->recipients[] = $recipient;
    }

    public function getRecipients()
    {
        return $this->recipients;
    }

    public function getRecipientEmails()
    {
        $recipients = $this->recipients;
        $emails = [];
        if ($recipients) {
            foreach ($recipients as $recipient) {
                $emails[] =  $recipient->email;
            }
        }

        return $emails;
    }

    public function addInvalidRecipient(SimpleRecipient $recipient)
    {
        $this->invalidRecipients[] = $recipient;
    }

    public function getInvalidRecipients()
    {
        return $this->invalidRecipients;
    }
}
