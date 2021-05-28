<?php

namespace ChurakovMike\DelayedEvents\Providers;

use App\Events\TestedEvent;
use App\Listeners\TestedListener;
use ChurakovMike\DelayedEvents\Events\Dispatcher;
use ChurakovMike\DelayedEvents\Listeners\RequestHandledListener;
use Illuminate\Contracts\Queue\Factory as QueueFactoryContract;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;

class BaseDelayedProvider extends EventServiceProvider
{
    protected $listen = [
        RequestHandled::class => [
            RequestHandledListener::class,
        ],
    ];

    protected $listenDelayed = [
        TestedEvent::class => [
            TestedListener::class,
        ],
    ];

    /**
     * Get the events and handlers.
     *
     * @return array
     */
    public function listens(): array
    {
        return $this->listenDelayed;
    }

    /**
     * Get the base events and handlers.
     *
     * @return array
     */
    public function getBaseListens(): array
    {
        return $this->listen;
    }

    public function register(): void
    {
        $this->app->singleton('events', function ($app) {
            return (new Dispatcher($app))->setQueueResolver(function () use ($app) {
                return $app->make(QueueFactoryContract::class);
            });
        });

        $this->booting(function () {
            $events = $this->getEvents();

            foreach ($events as $event => $listeners) {
                foreach (array_unique($listeners) as $listener) {
                    Event::listen($event, $listener);
                }
            }

            foreach ($this->subscribe as $subscriber) {
                Event::subscribe($subscriber);
            }

            $baseEvents = $this->getBaseEvents();

            foreach ($baseEvents as $event => $listeners) {
                foreach (array_unique($listeners) as $listener) {
                    Event::listen($event, $listener);
                }
            }

            foreach ($this->subscribe as $subscriber) {
                Event::subscribe($subscriber);
            }
        });
    }

    /**
     * Get the discovered events and listeners for the application.
     *
     * @return array
     */
    public function getBaseEvents(): array
    {
        if ($this->app->eventsAreCached()) {
            $cache = require $this->app->getCachedEventsPath();

            return $cache[get_class($this)] ?? [];
        } else {
            return array_merge_recursive(
                $this->discoveredEvents(),
                $this->getBaseListens()
            );
        }
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ => base_path('app/Providers'),
        ], 'providers');

        parent::boot();
    }
}
