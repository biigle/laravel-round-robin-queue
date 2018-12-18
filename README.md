# Round Robin Queue

A round robin load balancing queue for multiple queue connections in Laravel or Lumen.

Works well with multiple [`biigle/laravel-remote-queue`](https://github.com/biigle/laravel-remote-queue) connections.

[![Build Status](https://travis-ci.org/biigle/laravel-round-robin-queue.svg)](https://travis-ci.org/biigle/laravel-round-robin-queue)

## Installation

```
composer require biigle/laravel-round-robin-queue
```

### Laravel

The service provider is auto-discovered by Laravel.

### Lumen

Add `$app->register(Biigle\RoundRobinQueue\RoundRobinQueueServiceProvider::class);` to `bootstrap/app.php`.

## Set up

The round robin queue requires a persistent cache to work correctly. Make sure you have a cache set up and don't use the `array` cache.

## Usage

Configure a new queue connection in `queue.connections` to use the `roundrobin` diver like this:

```php
// queue.connections
[
 'rr' => [
    'driver' => 'roundrobin',
    'queue' => 'default',
    'connections' => ['q1', 'q2'],
 ],
 'q1' => [/* ... */],
 'q2' => [/* ... */],
]
```

The connection expects the additional config options `queue` and `connections`. The `queue` option sets the default queue name to which new jobs should be pushed. The `connections` option sets the other queue connections that should be used for load balancing.

Whenever you push a new job to the queue with the `roundrobin` driver, it will forward it to one of the connections that are configured in `connections`, in a round robin fashion. Example:

```php
use Queue;

$queue = Queue::connection('rr');

$queue->push($job1); // Pushed to connection 'q1'.
$queue->push($job2); // Pushed to connection 'q2'.
$queue->push($job3); // Pushed to connection 'q1'.
$queue->push($job4); // Pushed to connection 'q2'.
```
