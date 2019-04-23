<?php

namespace DromedarDesign\Prismic\Eloquent;

use Illuminate\Database\Eloquent\Builder as LaravelBuilder;
use Illuminate\Pagination\Paginator;

class Builder extends LaravelBuilder
{
    public function whereLanguage($language)
    {
        return $this->where('lang', $language);
    }

    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $this->query->perPage = $perPage;
        $this->query->page = $page ?? Paginator::resolveCurrentPage($pageName);

        return parent::paginate($perPage, $columns, $pageName, $page);
    }

    public function search($text, $column = null)
    {
        $this->query->s[] = [
            'text' => $text,
            'column' => $column,
        ];

        return $this;
    }
}
