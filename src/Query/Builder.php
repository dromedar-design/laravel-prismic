<?php

namespace DromedarDesign\Prismic\Query;

use Illuminate\Database\Query\Builder as LaravelBuilder;

class Builder extends LaravelBuilder
{
    public function toSql()
    {
        return $this;
    }

    public function __toString()
    {
        return 'Prismic Query: ' . parent::toSql();
    }
}
