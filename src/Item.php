<?php

namespace DromedarDesign\Prismic;

use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

class Item implements Jsonable, JsonSerializable
{
    use HasFields;

    protected $data;
    protected $model;

    public function __construct($data, $model)
    {
        $this->data = $data;
        $this->model = $model;
    }

    public function __get($key)
    {
        if ($field = $this->getPrismicField($key)) {
            return $field;
        }

        if (method_exists($this, $key)) {
            return $this->{$key}();
        }

        return $this->data->{$key};
    }

    public function __toString()
    {
        return collect($this->getPrismicData())
            ->map(function ($item, $key) {
                return $this->getPrismicField($key)->__toString();
            })
            ->implode('');
    }

    public function toJson($options = 0)
    {
        return $this->jsonSerialize();
    }

    public function jsonSerialize()
    {
        return json_encode($this->data);
    }
}
