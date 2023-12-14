<?php

namespace App\Exports\Sheets;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;

class WebArchiveSheet implements FromQuery, WithTitle
{
    protected string $category;
    protected Builder $query;

    public function __construct(Builder $query, string $category)
    {
        $this->query = $query;
        $this->category = $category;
    }

    public function query(): Builder
    {
        return $this->query;
    }

    public function title(): string
    {
        return ucfirst($this->category);
    }
}
