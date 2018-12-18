<?php

namespace Biigle\RoundRobinQueue;

use Illuminate\Queue\Queue;
use Illuminate\Contracts\Queue\Queue as QueueContract;

class RoundRobinQueue extends Queue implements QueueContract
{

    /**
     * Queue connections to push jobs to.
     *
     * @var array
     */
    protected $connections;

    /**
     * Default queue to push jobs to.
     *
     * @var string
     */
    protected $default;

    /**
     * Create a new instance.
     *
     * @param array $config Connection configuration.
     */
    public function __construct($connections, $default = 'default')
    {
        $this->connections = $connections;
        $this->default = $default;
    }

    /**
     * Get the size of the queue.
     *
     * @param  string  $queue
     * @return int
     */
    public function size($queue = null)
    {
        $this->checkForInfiniteRecursion();
        $queue = $this->getQueue($queue);

        return array_reduce($this->connections, function ($carry, $item) use ($queue) {
            return $carry + $this->queueManager()->connection($item)->size($queue);
        }, 0);
    }

    /**
     * Push a new job onto the queue.
     *
     * @param  string  $job
     * @param  mixed   $data
     * @param  string  $queue
     * @return mixed
     */
    public function push($job, $data = '', $queue = null)
    {
        $this->checkForInfiniteRecursion();
        $queue = $this->getQueue($queue);
        $connection = $this->getCurrentConnection();
        $result = $connection->push($job, $data, $queue);
        $this->advanceCurrentConnectionIndex();

        return $result;
    }

    /**
     * Push a raw payload onto the queue.
     *
     * @param  string  $payload
     * @param  string  $queue
     * @param  array   $options
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $this->checkForInfiniteRecursion();
        $queue = $this->getQueue($queue);
        $connection = $this->getCurrentConnection();
        $result = $connection->pushRaw($payload, $queue, $options);
        $this->advanceCurrentConnectionIndex();

        return $result;
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @param  string  $job
     * @param  mixed   $data
     * @param  string  $queue
     * @return mixed
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        $this->checkForInfiniteRecursion();
        $queue = $this->getQueue($queue);
        $connection = $this->getCurrentConnection();
        $result = $connection->later($delay, $job, $data, $queue);
        $this->advanceCurrentConnectionIndex();

        return $result;
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param  string  $queue
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    public function pop($queue = null)
    {
        // This queue is not meant to be queried directly.
    }

    /**
     * Get the QueueManager instance of the application.
     *
     * @return \Illuminate\Queue\QueueManager
     */
    protected function queueManager()
    {
        return $this->container ? $this->container['queue'] : null;
    }

    /**
     * Get the CacheManager instance of the application.
     *
     * @return \Illuminate\Cache\CacheManager
     */
    protected function cacheManager()
    {
        return $this->container ? $this->container['cache'] : null;
    }

    /**
     * Get the queue or return the default.
     *
     * @param  string|null  $queue
     * @return string
     */
    protected function getQueue($queue)
    {
        return $queue ?: $this->default;
    }

    /**
     * Get the cache key to track the current connection index.
     *
     * @param string $prefix
     *
     * @return string
     */
    protected function getCacheKey($prefix = 'round-robin-queue')
    {
        return $prefix.'-'.$this->getConnectionName();
    }

    /**
     * Get the index of the current queue connection to use.
     *
     * @return int
     */
    protected function getCurrentConnectionIndex()
    {
        $current = $this->cacheManager()->get($this->getCacheKey(), 0);

        // The size of the connections array may have changed although the current index
        // in the cache stayed the same. Make sure to handle a too large current index
        // gracefully.
        return $current % count($this->connections);
    }

    /**
     * Get the  the current queue connection to use.
     *
     * @return Queue
     */
    protected function getCurrentConnection()
    {
        $index = $this->getCurrentConnectionIndex();

        return $this->queueManager()->connection($this->connections[$index]);
    }

    /**
     * Get the index of the next queue connection to use.
     *
     * @return int
     */
    protected function getNextConnectionIndex()
    {
        $current = $this->getCurrentConnectionIndex();

        return ($current + 1) % count($this->connections);
    }

    /**
     * Set the index of the current queue connection to the next index.
     *
     * @return int The new current index.
     */
    protected function advanceCurrentConnectionIndex()
    {
        $next = $this->getNextConnectionIndex();
        $this->cacheManager()->forever($this->getCacheKey(), $next);

        return $next;
    }

    /**
     * Check if this queue has itself as target.
     *
     * @throws InfiniteRecursionException
     */
    protected function checkForInfiniteRecursion()
    {
        if (in_array($this->getConnectionName(), $this->connections)) {
            throw new InfiniteRecursionException('A round robin queue must not have itself as target connection.');
        }
    }
}
