Laravel delayed events
=======================

Installation
-------------
Install this package with composer
```bash
composer require churakovmike/laravel-delayed-events
```

Then you need to publish new Service Provider
```bash
php artisan vendor:publish --tag=providers
```

Add `DelayedServiceProvider` to your `config/app.php`
```php
'providers' => [

    /*
     * Package Service Providers...
     */
    DelayedServiceProvider::class,
    
];
```

All delayed events and listeners must be add to $listenDelayed.
```php
class DelayedEventServiceProvider extends BaseDelayedProvider
{
    protected $listenDelayed = [
        // your delayed events and listeners here
        // TestDelayedEvent::class => [
        //      TestDelayedListener::class,
        // ]
    ];
}
```
