<?php

namespace ChurakovMike\DelayedEvents\Events;

class DelayedEvent
{
    private $event;
    private $payload;

    /**
     * @param mixed $event
     * @param mixed $payload
     */
    public function __construct($event, $payload)
    {
        $this->event = $event;
        $this->payload = $payload;
    }

    /**
     * @return mixed
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }
}
