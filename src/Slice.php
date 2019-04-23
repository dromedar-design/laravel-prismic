<?php

namespace DromedarDesign\Prismic;

class Slice extends Item
{
    public function getPrismicData()
    {
        return $this->data->primary;
    }

    public function items()
    {
        return collect($this->data->items)
            ->map(function ($item) {
                return new Item(
                    $item,
                    $this->model
                );
            });
    }
}
