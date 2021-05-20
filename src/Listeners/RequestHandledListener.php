<?php

namespace ChurakovMike\DelayedEvents\Listeners;

use Illuminate\Foundation\Http\Events\RequestHandled;

class RequestHandledListener
{
    public function handle(RequestHandled $event): void
    {
        // ...
    }
}
