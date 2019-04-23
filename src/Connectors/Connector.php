<?php

namespace DromedarDesign\Prismic\Connectors;

use DromedarDesign\Prismic\Database\Connection;
use Illuminate\Database\Connectors\Connector as LaravelConnector;
use Illuminate\Database\Connectors\ConnectorInterface;

class Connector extends LaravelConnector implements ConnectorInterface
{
    public function connect(array $config)
    {
        return new Connection;
    }
}
