<?php

namespace DromedarDesign\Prismic;

use DromedarDesign\Prismic\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as LaravelModel;
use Illuminate\Support\Str;

class Model extends LaravelModel
{
    use HasFields;

    protected $connection = 'prismic';

    protected $primaryKey = 'uid';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    public $prismicMode = 'html';
    public $bodyKey = 'body';

    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    public function getTable()
    {
        return isset($this->table)
        ? $this->table
        : Str::snake(class_basename($this));
    }

    public function getAttribute($key)
    {
        if ($result = parent::getAttribute($key)) {
            return $result;
        }

        if ($key == $this->bodyKey) {
            return collect($this->data->{$key})
                ->map(function ($item) {
                    return new Slice(
                        $item,
                        $this
                    );
                });
        }

        return $this->getPrismicField($key);
    }

    public function setPrismicMode($mode)
    {
        $this->prismicMode = $mode;

        return $this;
    }
}
