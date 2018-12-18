<?php

namespace Biigle\RoundRobinQueue;

use Illuminate\Queue\Connectors\ConnectorInterface;

class RoundRobinConnector implements ConnectorInterface
{
   /**
     * Establish a queue connection.
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        return new RoundRobinQueue($config['connections'], $config['queue']);
    }
}
