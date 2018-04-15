<?php

namespace barrelstrength\sproutbase\services\sproutemail;

use barrelstrength\sproutbase\contracts\sproutemail\BaseEvent;
use barrelstrength\sproutbase\elements\sproutemail\NotificationEmail;
use barrelstrength\sproutbase\events\NotificationEmailEvent;
use barrelstrength\sproutbase\SproutBase;
use craft\base\Component;
use Craft;
use yii\base\Event;

/**
 * Class NotificationEmailEvents
 *
 * @package barrelstrength\sproutbase\services\sproutemail
 */
class NotificationEmailEvents extends Component
{
    /**
     * @event NotificationEmailEvent Event is triggered when the Craft App initializes
     */
    const EVENT_REGISTER_EMAIL_EVENT_TYPES = 'registerSproutNotificationEmailEvents';

    /**
     * @var \Callable[] Events that notifications have subscribed to
     */
    protected $registeredEvents = [];

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

        return function() {};
    }


    /**
     * Returns all the available Notification Email Event Types
     *
     * @return array
     */
    public function getNotificationEmailEventTypes()
    {
        $event = new NotificationEmailEvent([
            'events' => []
        ]);

        $this->trigger(self::EVENT_REGISTER_EMAIL_EVENT_TYPES, $event);

        return $event->events;
    }

    /**
     * Registers a closure for each event we should be listening for via Event::on
     *
     * @note
     * 1. Get all Notification Email Event Types that we need to listen for
     * 2. Register an anonymous function to $this->registeredEvents for each event using Event::on
     * 3. Call our anonymous function when the event is triggered
     *
     * @return bool
     */
    public function registerNotificationEmailEventHandlers()
    {
        $self = $this;
        $notificationEmailEventTypes = $this->getNotificationEmailEventTypes();

        if (!count($notificationEmailEventTypes)) {
            return false;
        }

        foreach ($notificationEmailEventTypes as $notificationEmailEventClassName) {

            // Create an instance of this event
            $notificationEmailEvent = new $notificationEmailEventClassName();

            if ($notificationEmailEvent instanceof BaseEvent) {

                // Register our event
                $self->registerEvent($notificationEmailEventClassName, $self->getDynamicEventHandler());

                if (Craft::$app->getRequest()->getIsConsoleRequest() == true) {
                    continue;
                }

                $eventClassName = $notificationEmailEvent->getEventClassName();
                $eventName = $notificationEmailEvent->getEventName();
                $eventHandlerClassName = $notificationEmailEvent->getEventHandlerClassName();

                Event::on($eventClassName, $eventName, function($eventHandlerClassName)
                    use ($self, $notificationEmailEventClassName, $notificationEmailEvent) {

                    return call_user_func($self->getRegisteredEvent($notificationEmailEventClassName),
                        $notificationEmailEventClassName, $eventHandlerClassName, $notificationEmailEvent);
                });
            }
        }

        return true;
    }

    /**
     * Returns the callable/closure that will handle dynamic event delegation when
     * a registered Event is called.
     *
     * This closure is necessary to avoid creating a method for every possible event
     * This closure allows us to avoid having to register for every possible event via Event::on
     * This closure allows us to know the current event being triggered dynamically
     *
     * @example - An overview of how this works. When the sproutemail is initialized...
     *
     * 1. We check which events we need to register for via Event::on
     * 2. We register an anonymous function as the handler
     * 3. This closure gets called with the name of the event and the event itself
     * 4. This closure executes as real event handler for the triggered event
     *
     * @return \Callable
     */
    public function getDynamicEventHandler()
    {
        $self = $this;

        return function($notificationEmailEventClassName, Event $eventClassName, BaseEvent $eventHandlerClassName) use ($self) {
            return $self->handleDynamicEvent($notificationEmailEventClassName, $eventClassName, $eventHandlerClassName);
        };
    }

    /**
     * This method hands things off to Sprout Email when a Notification Event we registered gets triggered.
     *
     * @param           $notificationEmailEventClassName
     * @param Event     $eventClassName
     * @param BaseEvent $eventHandlerClassName
     *
     * @return bool
     * @throws \Exception
     */
    public function handleDynamicEvent($notificationEmailEventClassName, Event $eventClassName, BaseEvent $eventHandlerClassName)
    {
        $params = $eventHandlerClassName->prepareParams($eventClassName);

        if ($params == false) {
            return false;
        }

        $element = $params['value'] ?? null;

        if ($notificationEmails = SproutBase::$app->notifications->getAllNotificationEmails($notificationEmailEventClassName)) {

            /**
             * @var $notificationEmail NotificationEmail
             */
            foreach ($notificationEmails as $notificationEmail) {

                $options = $notificationEmail->getOptions();

                if ($eventHandlerClassName->validateOptions($options, $element, $params)) {

                    SproutBase::$app->notifications->sendNotificationViaMailer($notificationEmail, $element);
                }
            }
        }

        return true;
    }

    /**
     * @return array
     */
    public function getEventsIndexedByPluginId()
    {
        $eventTypes = $this->getNotificationEmailEventTypes();

        $events = [];

        if (!empty($eventTypes)) {
            foreach ($eventTypes as $className) {

                $event = new $className();
                $plugin = $event->getPlugin();

                $events[$plugin->id] = $event;
            }
        }

        return $events;
    }

    /**
     * Returns events with a given plugin ID
     *
     * @example pluginId is the unique plugin handle
     *
     * sprout-forms
     * sprout-email
     *
     * @param string $pluginId
     *
     * @return mixed|null
     */
    public function getEventsByPluginId($pluginId)
    {
        $events = $this->getEventsIndexedByPluginId();

        if (isset($events[$pluginId])) {
            return $events[$pluginId];
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
        $events = $this->getNotificationEmailEventTypes();

        /** @noinspection NullCoalescingOperatorCanBeUsedInspection */
        return isset($events[$id]) ? $events[$id] : $default;
    }

    /**
     * @todo - consider refactoring
     *
     * @return array
     */
    public function getEventDropdownOptions()
    {
        $notificationEmailEventTypes = $this->getNotificationEmailEventTypes();

        $events = [];

        if (!empty($notificationEmailEventTypes)) {
            foreach ($notificationEmailEventTypes as $availableEvent) {
                $namespace = $availableEvent;
                $events[$namespace] = new $availableEvent();
            }
        }

        return $events;
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
}
