<?php

namespace barrelstrength\sproutbase\services\sproutemail;

use barrelstrength\sproutbase\contracts\sproutemail\BaseEvent;
use barrelstrength\sproutbase\elements\sproutemail\NotificationEmail;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\events\RegisterNotificationEvent;
use barrelstrength\sproutemail\mail\Message;
use barrelstrength\sproutemail\models\Response;
use barrelstrength\sproutemail\records\NotificationEmail as NotificationEmailRecord;
use barrelstrength\sproutemail\SproutEmail;
use craft\base\Component;
use Craft;
use craft\base\Element;
use craft\helpers\UrlHelper;
use yii\base\Event;
use craft\base\ElementInterface;

/**
 * Class NotificationEmails
 *
 * @package barrelstrength\sproutbase\services\sproutemail
 */
class NotificationEmails extends Component
{

    const EVENT_REGISTER_EMAIL_EVENTS = 'defineSproutEmailEvents';
    /**
     * @var BaseEvent[]
     */
    protected $availableEvents;

    /**
     * @var \Callable[] Events that notifications have subscribed to
     */
    protected $registeredEvents = [];

    /**
     * Returns all campaign notifications based on the passed in event id
     *
     * @param string $eventId
     *
     * @return ElementInterface[]|null
     */
    public function getAllNotificationEmails($eventId = null)
    {
        if ($eventId) {
            $attributes = ['eventId' => $eventId];

            $notifications = NotificationEmail::find()->where($attributes)->all();
        } else {
            $notifications = NotificationEmail::find()->all();
        }

        return $notifications;
    }

    /**
     * @param $emailId
     *
     * @return ElementInterface
     */
    public function getNotificationEmailById($emailId)
    {
        return Craft::$app->getElements()->getElementById($emailId);
    }

    /**
     * Returns all the available events that notifications can subscribe to
     *
     * @return array
     */
    public function getAvailableEvents()
    {
        $event = new RegisterNotificationEvent([
            'availableEvents' => []
        ]);

        $this->trigger(self::EVENT_REGISTER_EMAIL_EVENTS, $event);

        $availableEvents = $event->availableEvents;

        $events = [];

        if (!empty($availableEvents)) {
            foreach ($availableEvents as $availableEvent) {
                $namespace = get_class($availableEvent);
                $events[$namespace] = $availableEvent;
            }
        }

        return $events;
    }

    public function getEventsWithBase()
    {
        $availableEvents = $this->getAvailableEvents();

        $events = [];

        if (!empty($availableEvents)) {
            foreach ($availableEvents as $availableEvent) {
                $pluginId = $availableEvent->getPluginId();

                $events[$pluginId] = $availableEvent;
            }
        }

        return $events;
    }

    public function getEventByBase($base)
    {
        $events = $this->getEventsWithBase();

        if (isset($events[$base])) {
            return $events[$base];
        }

        return null;
    }

    /**
     * Returns a single notification event
     *
     * @param string $id The return value of the event getId()
     * @param mixed  $default
     *
     * @return BaseEvent
     */
    public function getEventById($id, $default = null)
    {
        $events = $this->getAvailableEvents();

        return isset($events[$id]) ? $events[$id] : $default;
    }

    /**
     * @param $event
     * @param $notification
     *
     * @return mixed
     */
    public function getEventSelectedOptions(BaseEvent $event, $notification)
    {
        $options = [];

        if (Craft::$app->getRequest()->getIsActionRequest()) {
            $options = $event->prepareOptions();
        }

        if ($notification) {
            $options = $event->prepareValue($notification->options);
        }

        $options = json_decode($options, true);

        return $options;
    }

    /**
     * @param NotificationEmail $notificationEmail
     * @param bool              $isSettingPage
     *
     * @return NotificationEmail|bool
     * @throws \Exception
     * @throws \Throwable
     */

    public function saveNotification(NotificationEmail $notificationEmail, $isSettingPage = false)
    {
        $result = false;

        $notificationEmailRecord = new NotificationEmail();

        if (!empty($notificationEmail->id)) {
            $notificationEmailRecord = NotificationEmail::findOne($notificationEmail->id);

            if (!$notificationEmailRecord) {
                throw new \Exception(Craft::t('sprout-email',
                    'No entry exists with the ID “{id}”', ['id' => $notificationEmail->id]));
            }
        } else {
            $notificationEmailRecord->subjectLine = $notificationEmail->subjectLine;
        }

        $eventId = $notificationEmail->eventId;

        $event = SproutBase::$app->notifications->getEventById($eventId);

        if ($event && $isSettingPage == false) {
            $options = $event->prepareOptions();

            $notificationEmail->options = $options;
        }

        $fieldLayout = $notificationEmail->getFieldLayout();

        // Assign our new layout id info to our
        // form model and records
        $notificationEmail->fieldLayoutId = $fieldLayout->id;
        $notificationEmailRecord->fieldLayoutId = $fieldLayout->id;

        $notificationEmail->addErrors($notificationEmailRecord->getErrors());

        if (!$notificationEmail->hasErrors()) {
            try {
                if (Craft::$app->getElements()->saveElement($notificationEmail)) {
                    return $notificationEmail;
                }
            } catch (\Exception $e) {
                throw $e;
            }
        }

        return $result;
    }

    /**
     * Deletes a Notification Email by ID
     *
     * @param $id
     *
     * @return bool
     */
    public function deleteNotificationEmailById($id)
    {
        $result = Craft::$app->getElements()->deleteElementById($id);

        return $result;
    }

    /**
     * Registers a closure for each event we should be listening for via craft()->on()
     *
     * @note
     * 1. Get all events that we need to listen for
     * 2. Register an anonymous function for each event using craft()->on()
     * 3. That function will be called with an event id and the event itself when triggered
     *
     * @return mixed
     */
    public function registerDynamicEventHandler()
    {
        $self = $this;
        $events = $this->getAvailableEvents();

        if (count($events)) {
            foreach ($events as $eventId => $listener) {
                if ($listener instanceof BaseEvent) {
                    $self->registerEvent($eventId, $self->getDynamicEventHandler());

                    if (Craft::$app->getRequest()->getIsConsoleRequest() == true) {
                        continue;
                    }
                    $params = $listener->getEventParams();

                    $class = $params['class'];
                    $name = $params['name'];
                    $event = $params['event'];

                    Event::on($class, $name, function($event) use ($self, $eventId, $listener) {
                        return call_user_func_array(
                            $self->getRegisteredEvent($eventId), [
                                $eventId,
                                $event,
                                $listener
                            ]
                        );
                    });
                }
            }
        }

        return true;
    }

    /**
     * Registers an event listener to be trigger dynamically
     *
     * @param string    $eventId
     * @param \Callable $callback
     */
    public function registerEvent($eventId, $callback)
    {
        $this->registeredEvents[$eventId] = $callback;
    }

    /**
     * Returns a callable for the given event
     *
     * @param string $eventId
     *
     * @return \Callable
     */
    public function getRegisteredEvent($eventId)
    {
        if (isset($this->registeredEvents[$eventId])) {
            return $this->registeredEvents[$eventId];
        };

        return function() {
        };
    }

    /**
     * Returns the callable/closure that handle dynamic event delegation for craft()->raiseEvent()
     *
     * This closure is necessary to avoid creating a method for every possible event (entries.saveEntry)
     * This closure allows us to avoid having to register for every possible event via craft()->on()
     * This closure allows us to know the current event being triggered dynamically
     *
     * @example
     * When the sproutemail is initialized...
     * 1. We check what events we need to register for via craft()->on()
     * 2. We register an anonymous function as the handler
     * 3. This closure gets called with the name of the event and the event itself
     * 4. This closure executes as real event handler for the triggered event
     *
     * @return \Callable
     */
    public function getDynamicEventHandler()
    {
        $self = $this;

        return function($eventId, Event $event, BaseEvent $listener) use ($self) {
            return $self->handleDynamicEvent($eventId, $event, $listener);
        };
    }

    /**
     * @param string    $eventId
     * @param Event     $event
     * @param BaseEvent $listener
     *
     * @return bool
     */
    public function handleDynamicEvent($eventId, Event $event, BaseEvent $listener)
    {
        $params = $listener->prepareParams($event);

        if ($params == false) {
            return false;
        }

        $element = isset($params['value']) ? $params['value'] : null;

        if ($notificationEmails = $this->getAllNotificationEmails($eventId)) {
            foreach ($notificationEmails as $notificationEmail) {
                $options = $notificationEmail['options'];

                $options = json_decode($options, true);

                if ($listener->validateOptions($options, $element, $params)) {
                    /**
                     * @var $notificationEmail NotificationEmailRecord
                     */
                    $notificationEmailElement = Craft::$app->getElements()->getElementById($notificationEmail->id);
                    $this->relayNotificationThroughAssignedMailer($notificationEmailElement, $element);
                }
            }
        }

        return true;
    }

    /**
     * Returns an array of recipients from the event element if any are found
     * This allows us to allow notifications to have recipients defined at runtime
     *
     * @param mixed $element Most likely a BaseElementModel but can be an array of event a string
     *
     * @return array
     */
    public function getDynamicRecipientsFromElement($element)
    {
        $recipients = [];

        if (is_object($element) && $element instanceof Element) {
            if (isset($element->sproutEmailRecipients) && is_array($element['sproutEmailRecipients']) && count($element['sproutEmailRecipients'])) {
                $recipients = (array)$element->sproutEmailRecipients;
            }
        }

        if (is_array($element) && isset($element['sproutEmailRecipients'])) {
            if (is_array($element['sproutEmailRecipients']) && count($element['sproutEmailRecipients'])) {
                $recipients = $element['sproutEmailRecipients'];
            }
        }

        return $recipients;
    }

    /**
     * Returns an array or variables for a notification template
     *
     * @example
     * The following syntax is supported to access event element properties
     *
     * {attribute}
     * {{ attribute }}
     * {{ object.attribute }}
     *
     * @param NotificationEmail $notificationEmail
     * @param mixed|null        $element
     *
     * @return array
     */
    public function prepareNotificationTemplateVariables(NotificationEmail $notificationEmail, $element = null)
    {
        if (is_object($element) && method_exists($element, 'getAttributes')) {
            $attributes = $element->getAttributes();

            if (isset($element->elementType)) {
                $content = $element->getContent()->getAttributes();

                if (count($content)) {
                    foreach ($content as $key => $value) {
                        if (!isset($attributes[$key])) {
                            $attributes[$key] = $value;
                        }
                    }
                }
            }
        } else {
            $attributes = (array)$element;
        }

        return array_merge($attributes, [
            'email' => $notificationEmail,
            'object' => $element
        ]);
    }

    /**
     *
     * @param ElementInterface $notificationEmail
     *
     * @param                  $object - will be an element model most of the time
     *
     * @return bool
     * @throws \Exception
     */
    protected function relayNotificationThroughAssignedMailer(ElementInterface $notificationEmail, $object)
    {
        $mailer = SproutBase::$app->mailers->getMailerByName('barrelstrength\\sproutbase\\mailers\\DefaultMailer');

        if (!method_exists($mailer, 'sendNotificationEmail')) {
            throw new \Exception(Craft::t('sprout-base', 'The {mailer} does not have a sendNotificationEmail() method.',
                ['mailer' => get_class($mailer)]));
        }

        try {
            /**
             * @var $notificationEmail NotificationEmail
             */
            return $mailer->sendNotificationEmail($notificationEmail, $object);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $notificationId
     *
     * @return Response
     */
    public function getPrepareModal($notificationId)
    {
        $notificationEmail = Craft::$app->getElements()->getElementById($notificationId);

        $response = new Response();
        /**
         * @var $notificationEmail NotificationEmail
         */
        if ($notificationEmail) {
            try {
                $response->success = true;
                $response->content = $this->getPrepareModalHtml($notificationEmail);

                return $response;
            } catch (\Exception $e) {
                $response->success = false;
                $response->message = $e->getMessage();

                return $response;
            }
        } else {
            $response->success = false;
            $response->message = "<p>".Craft::t('sprout-email', 'No actions available for this notification.')."</p>";
        }

        return $response;
    }

    /**
     * @param NotificationEmail $notificationEmail
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getPrepareModalHtml(NotificationEmail $notificationEmail)
    {
        // Display the testToEmailAddress if it exists
        $recipients = Craft::$app->getConfig()->getGeneral()->testToEmailAddress;

        if (empty($recipients)) {
            $currentUser = Craft::$app->getUser()->getIdentity();
            $recipients = $currentUser->email;
        }

        $errors = [];

        $errors = $this->getNotificationErrors($notificationEmail, $errors);

        return Craft::$app->getView()->renderTemplate(
            'sprout-email/_modals/notifications/prepare-email-snapshot',
            [
                'email' => $notificationEmail,
                'recipients' => $recipients,
                'errors' => $errors
            ]
        );
    }


    public function sendTestNotificationEmail(NotificationEmail $notificationEmail)
    {
        try {
            $this->sendMockNotificationEmail($notificationEmail);

            return Response::createModalResponse('sprout-email/_modals/response', [
                    'email' => $notificationEmail,
                    'message' => Craft::t('sprout-email', 'Notification sent successfully.')
                ]
            );
        } catch (\Exception $e) {
            SproutEmail::$app->utilities->addError('notification-mock-error', $e->getMessage());

            return Response::createErrorModalResponse('sprout-email/_modals/response', [
                'email' => $notificationEmail,
                'message' => Craft::t('sprout-email', $e->getMessage())
            ]);
        }
    }

    public function sendMockNotificationEmail(NotificationEmail $notificationEmail)
    {
        $event = $this->getEventById($notificationEmail->eventId);

        if ($event) {
            try {
                $mailer = SproutBase::$app->mailers->getMailerByName('barrelstrength\\sproutbase\\mailers\\DefaultMailer');

                // Must pass email options for getMockedParams methods to use $this->options
                $event->setOptions($notificationEmail->options);

                $sent = $mailer->sendNotificationEmail($notificationEmail, $event->getMockedParams());

                if (!$sent) {
                    $customErrorMessage = SproutEmail::$app->utilities->getErrors();

                    if (!empty($customErrorMessage)) {
                        $message = $customErrorMessage;
                    } else {
                        $message = Craft::t('sprout-email', 'Unable to send mock notification. Check email settings');
                    }

                    SproutEmail::$app->utilities->addError('sent-fail', $message);
                }
            } catch (\Exception $e) {
                throw $e;
            }
        }

        return false;
    }

    /**
     * Retrieves a rendered Notification Email to be shared or for Live Preview
     *
     * @param      $notificationId
     * @param null $type
     */
    public function getPreviewNotificationEmailById($notificationId, $type = null)
    {
        $notificationEmail = SproutEmail::$app->notificationEmails->getNotificationEmailById($notificationId);

        $eventId = $notificationEmail->eventId;

        $event = SproutEmail::$app->notificationEmails->getEventById($eventId);

        if (!$event) {
            ob_start();

            echo Craft::t('sprout-email', 'Notification Email cannot display. The Event setting must be set.');

            // End the request
            Craft::$app->end();
        }

        $email = new Message();
        $template = $notificationEmail->template;
        $fileExtension = ($type != null && $type == 'text') ? 'txt' : 'html';

        $email = SproutEmail::$app->renderEmailTemplates($email, $template, $notificationEmail);

        SproutEmail::$app->campaignEmails->showCampaignEmail($email, $fileExtension);
    }

    /**
     * @param $notificationEmail
     * @param $errors
     *
     * @return array
     */
    public function getNotificationErrors($notificationEmail, $errors = [])
    {
        $notificationEditUrl = UrlHelper::cpUrl('sprout-email/notifications/edit/'.$notificationEmail->id);
        $notificationEditSettingsUrl = UrlHelper::cpUrl('sprout-email/settings/notifications/edit/'.
            $notificationEmail->id);

        $event = $this->getEventById($notificationEmail->eventId);

        $template = $notificationEmail->template;

        if (empty($event)) {
            $errors[] = Craft::t('sprout-email', 'No Event is selected. <a href="{url}">Edit Notification</a>.', [
                'url' => $notificationEditUrl
            ]);
        }

        if (empty($template)) {
            $errors[] = Craft::t('sprout-email', 'No template added. <a href="{url}">Edit Notification Settings</a>.',
                [
                    'url' => $notificationEditSettingsUrl
                ]);
        }

        if (empty($errors)) {
            $object = $event->getMockedParams();

            $emailModel = new Message();

            SproutEmail::$app->renderEmailTemplates($emailModel, $template, $notificationEmail, $object);

            $templateErrors = SproutEmail::$app->utilities->getErrors();

            if (!empty($templateErrors['template'])) {
                foreach ($templateErrors['template'] as $templateError) {
                    $errors[] = Craft::t('sprout-email', $templateError.' <a href="{url}">Edit Settings</a>.',
                        [
                            'url' => $notificationEditSettingsUrl
                        ]);
                }
            }
        }

        return $errors;
    }
}
