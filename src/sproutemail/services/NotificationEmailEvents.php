<?php

namespace barrelstrength\sproutbase\sproutemail\services;

use barrelstrength\sproutbase\sproutemail\contracts\BaseNotificationEvent;
use barrelstrength\sproutbase\sproutemail\elements\NotificationEmail;
use barrelstrength\sproutbase\sproutemail\events\NotificationEmailEvent;
use barrelstrength\sproutbase\SproutBase;
use craft\base\Component;
use Craft;
use craft\base\Plugin;
use yii\base\Event;

/**
 * Class NotificationEmailEvents
 *
 * @package barrelstrength\sproutbase\sproutemail\services
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

        return function() {
        };
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

            if ($notificationEmailEvent instanceof BaseNotificationEvent) {

                // Register our event
                $self->registerEvent($notificationEmailEventClassName, $self->getDynamicEventHandler());

                if (Craft::$app->getRequest()->getIsConsoleRequest() == true) {
                    continue;
                }

                $eventClassName = $notificationEmailEvent->getEventClassName();
                $event = $notificationEmailEvent->getEventName();
                $eventHandlerClassName = $notificationEmailEvent->getEventHandlerClassName();

                Event::on($eventClassName, $event, function($eventHandlerClassName)
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

        return function($notificationEmailEventClassName, Event $event, BaseNotificationEvent $eventHandlerClass) use ($self) {
            return $self->handleDynamicEvent($notificationEmailEventClassName, $event, $eventHandlerClass);
        };
    }

    /**
     * This method hands things off to Sprout Email when a Notification Event we registered gets triggered.
     *
     * @param                       $notificationEmailEventClassName
     * @param Event                 $event
     * @param BaseNotificationEvent $eventHandlerClass
     *
     * @return bool
     * @throws \yii\base\ExitException
     */
    public function handleDynamicEvent($notificationEmailEventClassName, Event $event, BaseNotificationEvent $eventHandlerClass)
    {
        Craft::info(Craft::t('sprout-base', 'A Notification Event has been triggered: {eventName}', [
            'eventName' => $eventHandlerClass->getName()
        ]));

        // Get all Notification Emails that match this Notification Event
        $notificationEmails = SproutBase::$app->notifications->getAllNotificationEmails($notificationEmailEventClassName);

        if ($notificationEmails) {
            /**
             * @var NotificationEmail $notificationEmail
             */
            foreach ($notificationEmails as $notificationEmail) {

                // Add the Notification Event settings to the $eventHandlerClass
                $settings = json_decode($notificationEmail->settings, true);
                $eventHandlerClass = new $eventHandlerClass($settings);

                $eventHandlerClass->notificationEmail = $notificationEmail;
                $eventHandlerClass->event = $event;

                if ($eventHandlerClass->validate()) {

                    $object = $eventHandlerClass->getEventObject();

                    SproutBase::$app->notifications->sendNotificationViaMailer($notificationEmail, $object);
                }
            }
        }

        return true;
    }

    /**
     * Returns a single notification event
     *
     * @param string $type The return value of the event getId()
     * @param mixed  $default
     *
     * @return BaseNotificationEvent
     */
    public function getEventById($type, $default = null)
    {
        $notificationEmailEventTypes = $this->getNotificationEmailEventTypes();

        foreach ($notificationEmailEventTypes as $notificationEmailEventClass) {
            if ($type === $notificationEmailEventClass) {
                return new $notificationEmailEventClass();
            }
        }

        return $default;
    }

    /**
     * Returns list of events for an Event dropdown and initializes the current selected event with any existing settings
     *
     * @param NotificationEmail $notificationEmail
     *
     * @return array
     */
    public function getNotificationEmailEvents(NotificationEmail $notificationEmail)
    {
        $notificationEmailEventTypes = $this->getNotificationEmailEventTypes();

        $events = [];

        if (!empty($notificationEmailEventTypes)) {
            foreach ($notificationEmailEventTypes as $notificationEmailEventClass) {

                $settings = json_decode($notificationEmail->settings, true);

                if ($notificationEmailEventClass === $notificationEmail->eventId) {
                    // If the Event matches are current selected event, initialize the BaseNotificationEvent class with the Event settings
                    $event = new $notificationEmailEventClass($settings);
                } else {
                    $event = new $notificationEmailEventClass();
                }

                $events[$notificationEmailEventClass] = $event;
            }
        }

        uasort($events, function($a, $b) {
            /**
             * @var $a BaseNotificationEvent
             * @var $b BaseNotificationEvent
             */
            return $a->getName() <=> $b->getName();
        });

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
     * @param $notificationEmail
     * @param $pluginId
     *
     * @return array
     */
    public function getNotificationEmailEventsByPluginId($notificationEmail, $pluginId)
    {
        $events = $this->getNotificationEmailEvents($notificationEmail);

        foreach ($events as $key => $event) {
            if ($pluginId !== $event->getPlugin()->id)
            {
                unset($events[$key]);
            }
        }

        return $events;
    }
}
