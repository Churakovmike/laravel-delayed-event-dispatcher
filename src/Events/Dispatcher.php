<?php

namespace ChurakovMike\DelayedEvents\Events;

use ChurakovMike\DelayedEvents\Collection\EventCollection;
use Illuminate\Contracts\Container\Container as ContainerContract;
use Illuminate\Events\Dispatcher as BaseDispatcher;
use Illuminate\Foundation\Http\Events\RequestHandled;
use ChurakovMike\DelayedEvents\Events\DelayedEvent as DelayedEventContract;

class Dispatcher extends BaseDispatcher
{
    /**
     * The registered events.
     *
     * @var array|EventCollection
     */
    private $events;

    public function __construct(ContainerContract $container = null)
    {
        $this->events = new EventCollection();

        parent::__construct($container);
    }

    /**
     * Fire an event and call the listeners.
     *
     * @param  string|object  $event
     * @param  mixed  $payload
     * @param  bool  $halt
     * @return array|null
     */
    public function dispatch($event, $payload = [], $halt = false)
    {
        // When the given "event" is actually an object we will assume it is an event
        // object and use the class as the event name and this event itself as the
        // payload to the handler, which makes object based events quite simple.
        [$event, $payload] = $this->parseEventAndPayload(
            $event, $payload
        );

        if ($this->shouldBroadcast($payload)) {
            $this->broadcastEvent($payload[0]);
        }

        if ($this->shouldDelay($payload)) {
            $this->delayEvent($event, $payload);

            return null;
        }

        if ($this->isRequestHandled($payload)) {
            $this->fireEvents();
        }

        $responses = [];

        foreach ($this->getListeners($event) as $listener) {
            $response = $listener($event, $payload);

            // If a response is returned from the listener and event halting is enabled
            // we will just return this response, and not call the rest of the event
            // listeners. Otherwise we will add the response on the response list.
            if ($halt && ! is_null($response)) {
                return $response;
            }

            // If a boolean false is returned from a listener, we will stop propagating
            // the event to any further listeners down in the chain, else we keep on
            // looping through the listeners and firing every one in our sequence.
            if ($response === false) {
                break;
            }

            $responses[] = $response;
        }

        return $halt ? null : $responses;
    }

    public function fireEvents(): array
    {
        $responses = [];

        /** @var DelayedEvent $delayedEvent */
        foreach ($this->events as $delayedEvent) {
            foreach ($this->getListeners($delayedEvent->getEvent()) as $listener) {
                $response = $listener($delayedEvent->getEvent(), $delayedEvent->getPayload());

                if (is_null($response)) {
                    continue;
                }

                $responses[] = $response;
            }
        }

        return $responses;
    }

    /**
     * @param mixed $payload
     * @return bool
     */
    public function shouldDelay($payload): bool
    {
        return isset($payload[0])
            && $payload[0] instanceof DelayedEventContract;
    }

    public function delayEvent($event, $payload): void
    {
        $this->events->add(new DelayedEvent($event, $payload));
    }

    /**
     * @param $payload
     * @return bool
     */
    public function isRequestHandled($payload): bool
    {
        return isset($payload[0])
            && $payload[0] instanceof RequestHandled;
    }
}
