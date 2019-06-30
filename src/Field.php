<?php

namespace DromedarDesign\Prismic;

use Prismic\Dom\RichText;

class Field
{
    protected $data;
    protected $model;

    public function __construct($data, $model)
    {
        $this->data = $data;
        $this->model = $model;
    }

    public function __toString()
    {
        return $this->{$this->model->prismicMode};
    }

    public function __get($key)
    {
        if (method_exists($this, $key)) {
            return $this->{$key}();
        }

        if (is_array($this->data)) {
            return $this->data[0]->{$key};
        }

        return $this->data->{$key};
    }

    public function raw()
    {
        return $this->data;
    }

    public function text()
    {
        if ($this->isImage()) {
            return $this->data->url;
        }

        if ($this->isModel()) {
            return $this->model();
        }

        return rtrim(RichText::asText($this->data));
    }

    public function html()
    {
        if ($this->isImage()) {
            return $this->image();
        }

        if ($this->isModel()) {
            return $this->model();
        }

        return RichText::asHtml($this->data);
    }

    public function isImage()
    {
        return isset($this->data->url);
    }

    public function image()
    {
        return sprintf(
            '<img src="%s" alt="%s">',
            $this->data->url,
            $this->data->alt
        );
    }

    public function isModel()
    {
        return isset($this->data->type) && isset($this->data->id);
    }

    public function model()
    {
        return $this->data->{$this->model->getKeyName()};
    }
}
