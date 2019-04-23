<?php

namespace DromedarDesign\Prismic;

use DromedarDesign\Prismic\Query\Builder;
use Illuminate\Database\Connection as LaravelConnection;
use Illuminate\Database\Events\StatementPrepared;

class Connection extends LaravelConnection
{
    public function query()
    {
        return new Builder(
            $this, $this->getQueryGrammar(), $this->getPostProcessor()
        );
    }

    protected function prepared($statement)
    {
        $this->event(new StatementPrepared(
            $this, $statement
        ));

        return $statement;
    }
}
