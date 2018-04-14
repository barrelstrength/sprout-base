<?php

namespace barrelstrength\sproutbase\services\sproutemail;

use barrelstrength\sproutbase\base\TemplateTrait;
use barrelstrength\sproutbase\contracts\sproutemail\BaseEvent;
use barrelstrength\sproutbase\elements\sproutemail\NotificationEmail;
use barrelstrength\sproutbase\mailers\DefaultMailer;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\events\RegisterNotificationEvent;
use barrelstrength\sproutemail\mail\Message;
use barrelstrength\sproutbase\models\sproutbase\Response;
use barrelstrength\sproutbase\records\sproutemail\NotificationEmail as NotificationEmailRecord;
use craft\base\Component;
use Craft;
use craft\base\Element;
use craft\base\Plugin;
use craft\helpers\ElementHelper;
use craft\helpers\UrlHelper;
use craft\web\View;
use yii\base\Event;
use craft\base\ElementInterface;

/**
 * Class NotificationEmails
 *
 * @package barrelstrength\sproutbase\services\sproutemail
 */
class NotificationEmails extends Component
{
    use TemplateTrait;

    const EVENT_REGISTER_EMAIL_EVENTS = 'defineSproutEmailEvents';
    const DEFAULT_TEMPLATE = 'sprout-base/sproutemail/notifications/_special/notification';

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

    /**
     * @return array
     */
    public function getEventsWithBase()
    {
        $availableEvents = $this->getAvailableEvents();

        $events = [];

        if (!empty($availableEvents)) {
            foreach ($availableEvents as $availableEvent) {
                $plugin = $availableEvent->getPlugin();

                $events[$plugin->id] = $availableEvent;
            }
        }

        return $events;
    }

    /**
     * @param $base
     *
     * @return mixed|null
     */
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

        /** @noinspection NullCoalescingOperatorCanBeUsedInspection */
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
     * @return NotificationEmail|null
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\base\Exception
     */
    public function saveNotification(NotificationEmail $notificationEmail, $isSettingPage = false)
    {
        $notificationEmailRecord = new NotificationEmailRecord();

        if ($notificationEmail->id !== null) {
            $notificationEmailRecord = NotificationEmail::findOne($notificationEmail->id);

            if (!$notificationEmailRecord) {
                throw new \InvalidArgumentException(Craft::t('sprout-base',
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
            /**
             * @var $plugin Plugin
             */
            $plugin = $event->getPlugin();

            $notificationEmail->pluginId = $plugin->id;
        }

        $fieldLayout = $notificationEmail->getFieldLayout();

        Craft::$app->getFields()->saveLayout($fieldLayout);

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

        return null;
    }

    /**
     * Deletes a Notification Email by ID
     *
     * @param $id
     *
     * @return bool
     * @throws \Throwable
     */
    public function deleteNotificationEmailById($id)
    {
        return Craft::$app->getElements()->deleteElementById($id);
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
        }

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
     * @param           $eventId
     * @param Event     $event
     * @param BaseEvent $listener
     *
     * @return bool
     * @throws \Exception
     */
    public function handleDynamicEvent($eventId, Event $event, BaseEvent $listener)
    {
        $params = $listener->prepareParams($event);

        if ($params == false) {
            return false;
        }
        $element = ($params['value'] != null) ? $params['value'] : null;

        if ($notificationEmails = $this->getAllNotificationEmails($eventId)) {

            /**
             * @var $notificationEmail NotificationEmail
             */
            foreach ($notificationEmails as $notificationEmail) {
                $options = $notificationEmail['options'];

                $options = json_decode($options, true);

                if ($listener->validateOptions($options, $element, $params)) {
                    $notificationEmailElement = Craft::$app->getElements()->getElementById($notificationEmail->id);

                    if ($notificationEmailElement) {
                        $this->relayNotificationThroughAssignedMailer($notificationEmailElement, $element);
                    }
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
     * @param ElementInterface $notificationEmail
     * @param                  $object - will be an element model most of the time
     *
     * @return bool|null
     * @throws \Exception
     */
    protected function relayNotificationThroughAssignedMailer(ElementInterface $notificationEmail, $object)
    {
        $mailer = SproutBase::$app->mailers->getMailerByName(DefaultMailer::class);

        if (!method_exists($mailer, 'sendNotificationEmail')) {
            throw new \BadMethodCallException(Craft::t('sprout-base', 'The {mailer} does not have a sendNotificationEmail() method.',
                ['mailer' => get_class($mailer)]));
        }

        try {

            if ($mailer) {
                /**
                 * @var $notificationEmail NotificationEmail
                 */
                return $mailer->sendNotificationEmail($notificationEmail, $object);
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return null;
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

            $response->message = Craft::t('sprout-base', 'No actions available for this notification.');
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
            'sprout-base/sproutemail/_modals/prepare-email-snapshot',
            [
                'email' => $notificationEmail,
                'recipients' => $recipients,
                'errors' => $errors
            ]
        );
    }

    /**
     * @param NotificationEmail $notificationEmail
     *
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function sendTestNotificationEmail(NotificationEmail $notificationEmail)
    {
        try {
            $this->sendMockNotificationEmail($notificationEmail);

            Craft::$app->getView()->setTemplateMode(View::TEMPLATE_MODE_CP);

            return Response::createModalResponse('sprout-base/sproutemail/_modals/response', [
                    'email' => $notificationEmail,
                    'message' => Craft::t('sprout-base', 'Notification sent successfully.')
                ]
            );
        } catch (\Exception $e) {
            SproutBase::$app->common->addError('notification-mock-error', $e->getMessage());

            return Response::createErrorModalResponse('sprout-base/sproutemail/_modals/response', [
                'email' => $notificationEmail,
                'message' => Craft::t('sprout-base', $e->getMessage())
            ]);
        }
    }

    /**
     * @param NotificationEmail $notificationEmail
     *
     * @return bool
     * @throws \Exception
     */
    public function sendMockNotificationEmail(NotificationEmail $notificationEmail)
    {
        $event = $this->getEventById($notificationEmail->eventId);

        if ($event) {
            try {

                $mailer = SproutBase::$app->mailers->getMailerByName(DefaultMailer::class);
                //$options = $notificationEmail->options;
                $options = json_decode($notificationEmail->options, true);

                // Must pass email options for getMockedParams methods to use $this->options
                $event->setOptions($options);

                $sent = false;

                if ($mailer) {
                    $sent = $mailer->sendNotificationEmail($notificationEmail, $event->getMockedParams());
                }

                if (!$sent) {
                    $customErrorMessage = SproutBase::$app->common->getErrors();

                    if (!empty($customErrorMessage)) {
                        $message = $customErrorMessage;
                    } else {
                        $message = Craft::t('sprout-base', 'Unable to send mock notification. Check email settings');
                    }

                    SproutBase::$app->common->addError('sent-fail', $message);
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
     *
     * @throws \yii\base\Exception
     * @throws \yii\base\ExitException
     */
    public function getPreviewNotificationEmailById($notificationId, $type = null)
    {
        /**
         * @var $notificationEmail NotificationEmail
         */
        $notificationEmail = $this->getNotificationEmailById($notificationId);

        $eventId = $notificationEmail->eventId;

        $event = $this->getEventById($eventId);

        if (!$event) {
            ob_start();

            echo Craft::t('sprout-base', 'Notification Email cannot display. The Event setting must be set.');

            // End the request

            Craft::$app->end();
        }

        $event->setOptions($notificationEmail->options);

        $email = new Message();

        $template = $notificationEmail->template;

        if (empty($notificationEmail->template)) {
            /**
             * Get the templates path for the sprout base default notification template
             */
            $template = SproutBase::$app->sproutEmail->getEmailTemplate($notificationEmail);
        }

        // The getBodyParam is for livePreviewNotification to update on change
        $subjectLine = Craft::$app->getRequest()->getBodyParam('subjectLine');
        if ($subjectLine) {
            $notificationEmail->subjectLine = $subjectLine;
        }

        $body = Craft::$app->getRequest()->getBodyParam('body');
        if ($body) {
            $notificationEmail->body = $body;
        }

        $fieldsLocation = Craft::$app->getRequest()->getParam('fieldsLocation', 'fields');
        $notificationEmail->setFieldValuesFromRequest($fieldsLocation);

        $fileExtension = ($type != null && $type == 'text') ? 'txt' : 'html';

        $email = $this->renderEmailTemplates($email, $notificationEmail);

        $this->showPreviewEmail($email, $fileExtension);
    }

    /**
     * @param        $email
     * @param string $fileExtension
     *
     * @throws \yii\base\ExitException
     */
    public function showPreviewEmail($email, $fileExtension = 'html')
    {
        if ($fileExtension == 'txt') {
            $output = $email->body;
        } else {
            $output = $email->htmlBody;
        }

        // Output it into a buffer, in case TasksService wants to close the connection prematurely
        ob_start();

        echo $output;

        // End the request
        Craft::$app->end();
    }

    /**
     * @param       $notificationEmail
     * @param array $errors
     *
     * @return array
     * @throws \yii\base\Exception
     */
    public function getNotificationErrors($notificationEmail, array $errors = [])
    {
        $currentPluginHandle = Craft::$app->request->getSegment(1);

        $notificationEditUrl = UrlHelper::cpUrl($currentPluginHandle.'/notifications/edit/'.$notificationEmail->id);
        $notificationEditSettingsUrl = UrlHelper::cpUrl($currentPluginHandle.'/settings/notifications/edit/'.
            $notificationEmail->id);

        $event = $this->getEventById($notificationEmail->eventId);

        $template = SproutBase::$app->sproutEmail->getEmailTemplate($notificationEmail);

        if ($event === null) {
            $errors[] = Craft::t('sprout-base', 'No Event is selected. <a href="{url}">Edit Notification</a>.', [
                'url' => $notificationEditUrl
            ]);
        }

        if (empty($template)) {
            $errors[] = Craft::t('sprout-base', 'No template added. <a href="{url}">Edit Notification Settings</a>.',
                [
                    'url' => $notificationEditSettingsUrl
                ]);
        }

        if (count($errors)) {
            return $errors;
        }

        $options = json_decode($notificationEmail->options, true);

        $event->setOptions($options);

        $mockObject = $event->getMockedParams();

        $emailModel = new Message();

        $this->renderEmailTemplates($emailModel, $notificationEmail, $mockObject);

        $templateErrors = SproutBase::$app->common->getErrors();

        if (!empty($templateErrors['template'])) {

            foreach ($templateErrors['template'] as $templateError) {
                $errors[] = Craft::t('sprout-base', $templateError.' <a href="{url}">Edit Settings</a>.',
                    [
                        'url' => $notificationEditSettingsUrl
                    ]);
            }
        }

        return $errors;
    }

    /**
     * @param string $subjectLine
     * @param string $handle
     *
     * @return NotificationEmail|null
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\base\Exception
     */
    public function createNewNotification($subjectLine = null, $handle = null)
    {
        $currentPluginHandle = Craft::$app->request->getSegment(1);

        $notification = new NotificationEmail();
        $subjectLine = $subjectLine ?? Craft::t('sprout-base', 'Notification');
        $handle = $handle ?? ElementHelper::createSlug($subjectLine);

        $subjectLine = $this->getFieldAsNew('subjectLine', $subjectLine);

        $notification->title = $subjectLine;
        $notification->subjectLine = $subjectLine;
        $notification->pluginId = $currentPluginHandle;
        $notification->slug = $handle;

        // Set default tab
        $field = null;

        if ($this->saveNotification($notification)) {

            return $notification;
        }

        return null;
    }

    public function getFieldAsNew($field, $value)
    {
        $newField = null;
        $i = 1;
        $band = true;
        do {
            $newField = $field == 'handle' ? $value.$i : $value.' '.$i;
            $form = $this->getFieldValue($field, $newField);
            if ($form === null) {
                $band = false;
            }

            $i++;
        } while ($band);

        return $newField;
    }

    public function getFieldValue($field, $value)
    {
        return NotificationEmailRecord::findOne([
            $field => $value
        ]);
    }
}
