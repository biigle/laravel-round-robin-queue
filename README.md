# Round Robin Queue

A round robin load balancing queue for multiple queue connections in Laravel or Lumen.

Works well with multiple `biigle/laravel-remote-queue` connections.

## Installation

```
composer require biigle/laravel-round-robin-queue
```

### Laravel

The service provider is auto-discovered by Laravel.

### Lumen

Add `$app->register(Biigle\RoundRobinQueue\RoundRobinQueueServiceProvider::class);` to `bootstrap/app.php`.
