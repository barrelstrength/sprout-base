<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\sentemail\models;

use Craft;
use craft\base\Model;

class SentEmailInfoTable extends Model
{
    // General Info
    /**
     * The type of email being sent.
     *
     * @var string Notification Email, Campaign Email, System Message
     */
    public $emailType;

    /**
     * The type of delivery
     *
     * @var string Live, Test
     */
    public $deliveryType;

    /**
     * The status of the email that was sent
     *
     * @var string Sent, Error
     */
    public $deliveryStatus;

    /**
     * Any response or error message generated while sending or attempting to send the email
     *
     * @var string
     */
    public $message;

    // Sender Info

    /**
     * The From Name
     *
     * @var string
     */
    public $senderName;

    /**
     * The From Email
     *
     * @var string
     */
    public $senderEmail;

    /**
     * The plugin or module that initiated sending the email
     *
     * @var string
     */
    public $source;

    /**
     * The version number of the plugin or module that initiated sending the email
     *
     * @var string
     */
    public $sourceVersion;

    /**
     * The version of Craft being used while sending the email
     *
     * @var string
     */
    public $craftVersion;

    /**
     * The IP Address of the request when sending the email
     *
     * @var string
     */
    public $ipAddress;

    /**
     * The User Agent of the request when sending the email
     *
     * @var string
     */
    public $userAgent;

    // Email Settings

    /**
     * @var
     */
    public $mailer;

    /**
     * @var
     */
    public $transportType;

    /**
     * @var
     */
    public $protocol;

    /**
     * @var
     */
    public $host;

    /**
     * @var
     */
    public $port;

    /**
     * @var
     */
    public $username;

    /**
     * @var
     */
    public $encryptionMethod;

    /**
     * @var
     */
    public $timeout;

    public function getEmailTypes(): array
    {
        return [
            'Campaign' => Craft::t('sprout', 'Campaign'),
            'Notification' => Craft::t('sprout', 'Notification'),
            'Resent' => Craft::t('sprout', 'Resent'),
            'Sent' => Craft::t('sprout', 'Sent'),
            'System' => Craft::t('sprout', 'System Message')
        ];
    }

    public function getDeliveryStatuses(): array
    {
        return [
            'Sent' => Craft::t('sprout', 'Sent'),
            'Error' => Craft::t('sprout', 'Error')
        ];
    }

    public function getDeliveryTypes(): array
    {
        return [
            'Live' => Craft::t('sprout', 'Live'),
            'Test' => Craft::t('sprout', 'Test')
        ];
    }
}