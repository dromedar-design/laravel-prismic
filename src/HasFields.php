<?php

namespace DromedarDesign\Prismic;

trait HasFields
{
    public function getPrismicData()
    {
        return $this->data;
    }

    public function getPrismicField($key)
    {
        $data = $this->getPrismicData();

        if (isset($data->{$key})) {
            return new Field(
                $data->{$key},
                $this instanceof Model ? $this : $this->model,
            );
        }

        return null;
    }
}
