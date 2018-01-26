<?php

namespace barrelstrength\sproutbase\services\sproutemail;

use barrelstrength\sproutbase\contracts\sproutemail\BaseMailer;
use barrelstrength\sproutbase\events\RegisterMailersEvent;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutemail\events\RegisterSendEmailEvent;
use craft\base\Component;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use craft\mail\Message;
use Craft;
use craft\elements\User;
use yii\base\Event;

class Mailers extends Component
{
    const EVENT_REGISTER_MAILERS = 'defineSproutEmailMailers';
    const ON_SEND_EMAIL = "onSendEmail";
    const ON_SEND_EMAIL_ERROR = "onSendEmailError";

    protected $mailers;

    public function getMailers()
    {
        $event = new RegisterMailersEvent([
            'mailers' => []
        ]);

        $this->trigger(self::EVENT_REGISTER_MAILERS, $event);

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
     * @return BaseMailer|null
     * @internal param bool $includeMailersNotYetLoaded
     *
     */
    public function getMailerByName($name)
    {
        $this->mailers = $this->getMailers();

        return isset($this->mailers[$name]) ? $this->mailers[$name] : null;
    }


    public function sendEmail(Message $message, $variables = [])
    {
        $errorMessage = SproutBase::$app->utilities->getErrors();

        if (!empty($errorMessage)) {

            $errorMessage = SproutBase::$app->utilities->formatErrors();

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
            SproutBase::$app->utilities->addError($e->getMessage());
        }

        return null;
    }

    /**
     * @param null $element
     * @param      $model
     *
     * @return array|mixed
     * @throws \Exception
     */
    public function getRecipients($element = null, $model)
    {
        $recipientsString = $model->recipients;

        // Possibly called from entry edit screen
        if (is_null($element)) {
            return $recipientsString;
        }

        // Previously converted to array somehow?
        if (is_array($recipientsString)) {
            return $recipientsString;
        }

        // Previously stored as JSON string?
        if (stripos($recipientsString, '[') === 0) {
            return Json::decode($recipientsString);
        }

        // Still a string with possible twig generator code?
        if (stripos($recipientsString, '{') !== false) {
            try {
                $recipients = Craft::$app->getView()->renderObjectTemplate(
                    $recipientsString,
                    $element
                );

                return array_unique(ArrayHelper::filterEmptyStringsFromArray(ArrayHelper::toArray($recipients)));
            } catch (\Exception $e) {
                throw $e;
            }
        }

        // Just a regular CSV list
        if (!empty($recipientsString)) {
            return ArrayHelper::filterEmptyStringsFromArray(ArrayHelper::toArray($recipientsString));
        }

        return [];
    }


    public function handleOnSendEmailErrorEvent($message, Message $emailModel, $variables = [])
    {
        $user = Craft::$app->getUsers()->getUserByUsernameOrEmail($emailModel->toEmail);

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
}