<?php

namespace App\Filters\Incomes;

use EloquentFilter\ModelFilter;

class Receivables extends ModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relatedModel => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    public function search($query)
    {
        return $this->whereLike('title', $query);
    }

    public function accounts($accounts)
    {
        return $this->whereIn('account_id', (array) $accounts);
    }

    public function customers($customers)
    {
        return $this->whereIn('customer_id', (array) $customers);
    }

    public function categories($categories)
    {
        return $this->whereIn('category_id', (array) $categories);
    }

    public function dueDate($date)
    {
        $dates = explode('_', $date);
        $dates[0] .= ' 00:00:00';
        $dates[1] .= ' 23:59:59';

        return $this->whereBetween('due_at', $dates);
    }
}
