<?php

namespace App\Exports\Sheets;

use App\Models\WebArchiveTest;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;

class WebArchiveSheet implements FromQuery, WithTitle
{
    protected string $category;
    protected string $server;

    public function __construct(string $server, string $category)
    {
        $this->category = $category;
        $this->server = $server;
    }

    public function query(): Builder
    {
        return WebArchiveTest
            ::query()
            ->select(['web_root', 'page_title'])
            ->where('redirect_url', '0')
            ->where('web_root', 'NOT LIKE', '%delete_%')
            ->where('server', $this->server)
            ->where('category', $this->category);
    }

    public function title(): string
    {
        return ucfirst($this->category);
    }
}
