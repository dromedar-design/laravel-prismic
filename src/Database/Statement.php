<?php

namespace DromedarDesign\Prismic\Database;

use App;
use Prismic\Api;
use Prismic\Predicates;

class Statement
{
    protected $query;
    protected $api;
    protected $predicates;
    protected $options;
    protected $resp;

    protected const DOCUMENT_ATTRIBUTES = [
        'id',
        'type',
        'href',
        'tags',
        'first_publication_date',
        'last_publication_date',
        'linked_documents',
        'alternate_languages',
        'data',
    ];

    protected const OPTIONS_ATTRIBUTES = [
        'lang',
        'orderings',
        'pageSize',
        'page',
    ];

    protected const DONT_CHANGE = [
        'fulltext',
    ];

    public function __construct($query)
    {
        $this->query = $query;
        $this->api = resolve(Api::class);
    }

    public function execute()
    {
        if ($this->query->orders) {
            $orderings = collect($this->query->orders)
                ->reduce(function ($res, $item) {
                    $direction = $item['direction'] == 'desc' ? ' desc' : '';
                    $order = "my.{$this->query->from}.{$item['column']}{$direction}";
                    return $res->push($order);
                }, collect())
                ->implode(', ');
            $this->addCondition('orderings', "[$orderings]");
        }

        if ($this->query->limit || isset($this->query->perPage)) {
            $this->addCondition('pageSize', $this->query->limit ?? $this->query->perPage);
        }

        if (isset($this->query->s)) {
            foreach ($this->query->s as $s) {
                $column = $s['column'] ? "my.{$this->query->from}.{$s['column']}" : 'document';
                $this->addCondition($column, $s['text'], 'fulltext');
            }
        }

        foreach ($this->query->wheres as $where) {
            $column = str_replace($this->query->from . '.', '', $where['column']);
            $value = $where['value'] ?? $where['values'] ?? null;

            $method = null;
            if (isset($where['operator'])) {
                if ($where['operator'] == '!=') {
                    $method = 'not';
                } elseif ($where['operator'] == '<') {
                    $method = 'lt';
                } elseif ($where['operator'] == '>') {
                    $method = 'gt';
                } elseif ($where['operator'] == '<=') {
                    $this->addCondition($column, $value);
                    $method = 'lt';
                } elseif ($where['operator'] == '>=') {
                    $this->addCondition($column, $value);
                    $method = 'gt';
                }
            }

            $this->addCondition($column, $value, $method);
        }
    }

    public function addCondition($column, $value, $method = null)
    {
        if (is_object($value)) {
            $value = (string) $value;
        }

        if (in_array($column, self::OPTIONS_ATTRIBUTES)) {
            $this->addOption($column, $value);
        } else {
            $this->addWhere($column, $value, $method);
        }
    }

    public function addWhere($column, $value, $method = null)
    {
        if (!in_array($method, self::DONT_CHANGE)) {
            if (!in_array($column, self::DOCUMENT_ATTRIBUTES)) {
                $column = "my.{$this->query->from}.{$column}";
            } else {
                $column = "document.{$column}";
            }
        }

        if (null != $method) {
            $predicate = Predicates::{$method}($column, $value);
        } elseif (is_array($value)) {
            $predicate = Predicates::any($column, $value);
        } else {
            $predicate = Predicates::at($column, $value);
        }

        $this->predicates[] = $predicate;
    }

    public function addOption($option, $value)
    {
        $this->options[$option] = $value;
    }

    public function fetchAll()
    {
        // Add defaults
        if (!isset($this->options['lang'])) {
            $this->addOption('lang', $this->normalizeLanguage(App::getLocale()));
        }

        if (!isset($this->options['page']) && isset($this->query->page)) {
            $this->addOption('page', $this->query->page);
        }

        $this->addWhere('type', $this->query->from);

        // Run the query
        $this->resp = $this->api->query($this->predicates, $this->options);

        if (count($this->resp->results)) {
            $this->resp->results[0]->aggregate = $this->resp->total_results_size;
        }

        return collect($this->resp->results);
    }

    public function normalizeLanguage($language)
    {
        switch ($language) {
            case 'en':
                return 'en-us';
            default:
                return $language;
        };
    }

    public function bindValue()
    {
        return true;
    }

    public function rowCount()
    {
        return count($this->resp->results);
    }
}
