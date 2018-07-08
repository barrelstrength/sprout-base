<?php

namespace barrelstrength\sproutbase\app\email\services;

use barrelstrength\sproutbase\app\email\base\Mailer;
use barrelstrength\sproutbase\app\email\elements\NotificationEmail;
use barrelstrength\sproutbase\app\email\events\RegisterMailersEvent;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\app\email\events\RegisterSendEmailEvent;
use craft\base\Component;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use craft\mail\Message;
use Craft;
use craft\elements\User;
use yii\base\Event;

class Mailers extends Component
{
    const EVENT_REGISTER_MAILER_TYPES = 'defineSproutEmailMailers';
    const ON_SEND_EMAIL = 'onSendEmail';
    const ON_SEND_EMAIL_ERROR = 'onSendEmailError';

    protected $mailers;

    /**
     * @return array
     */
    public function getMailers()
    {
        $event = new RegisterMailersEvent([
            'mailers' => []
        ]);

        $this->trigger(self::EVENT_REGISTER_MAILER_TYPES, $event);

        $eventMailers = $event->mailers;

        $mailers = [];

        if (!empty($eventMailers)) {
            foreach ($eventMailers as $eventMailer) {
                $namespace = get_class($eventMailer);
                $mailers[$namespace] = $eventMailer;
            }
        }

        return $mailers;
    }

    /**
     * @param string $name
     *
     * @return Mailer|null
     * @internal param bool $includeMailersNotYetLoaded
     *
     */
    public function getMailerByName($name)
    {
        $this->mailers = $this->getMailers();

        return ($this->mailers[$name] ?? null) ? $this->mailers[$name] : null;
    }

    /**
     * @param Message $message
     * @param array   $variables
     *
     * @return bool|null
     */
    public function sendEmail(Message $message, array $variables = [])
    {
        $errorMessage = SproutBase::$app->emailErrorHelper->getErrors();

        if (!empty($errorMessage)) {

            $errorMessage = SproutBase::$app->emailErrorHelper->formatErrors();

            $this->handleOnSendEmailErrorEvent($errorMessage, $message, $variables);

            return false;
        }

        $mailer = Craft::$app->getMailer();

        try {
            $result = $mailer->send($message);

            if ($result) {
                $event = new RegisterSendEmailEvent([
                    'mailer' => $mailer,
                    'message' => $message,
                    'variables' => $variables
                ]);

                $this->trigger(self::ON_SEND_EMAIL, $event);
            }

            return $result;
        } catch (\Exception  $e) {
            SproutBase::$app->emailErrorHelper->addError($e->getMessage());
        }

        return null;
    }

    /**
     * @param         $message
     * @param Message $emailModel
     * @param array   $variables
     */
    public function handleOnSendEmailErrorEvent($message, Message $emailModel, array $variables = [])
    {
        $user = Craft::$app->getUsers()->getUserByUsernameOrEmail($emailModel->toEmail);

        // @todo - clean up toFirstName/toLastName logic. Message should only have name if it has anything.
        if (!$user) {
            $user = new User();
            $user->email = $emailModel->toEmail;
            $user->firstName = $emailModel->toFirstName;
            $user->lastName = $emailModel->toLastName;
        }

        // Call Email service class instead of $this to get sender settings

        $event = new Event([
            'user' => $user,
            'emailModel' => $emailModel,
            'variables' => $variables,
            'message' => $message,

            // Set this here so we can set the status properly when saving
            'deliveryStatus' => 'failed',
        ]);

        $this->trigger(self::ON_SEND_EMAIL_ERROR, $event);
    }

    public function includeMailerModalResources()
    {
        $mailers = SproutBase::$app->mailers->getMailers();

        if (count($mailers)) {
            /**
             * @var $mailer Mailer
             */
            foreach ($mailers as $mailer) {
                $mailer->includeModalResources();
            }
        }
    }
}