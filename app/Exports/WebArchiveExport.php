<?php
namespace App\Exports;

use App\Exports\Sheets\WebArchiveSheet;
use App\Models\WebArchiveTest;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Database\Eloquent\Builder;

class WebArchiveExport implements WithMultipleSheets, WithHeadings
{
    use Exportable;

    protected string $server;

    public function __construct(string $server)
    {
        $this->server = $server;
    }

    public function headings(): array
    {
        return [
            'URL',
            'Title'
        ];
    }

    public function sheets(): array
    {
        $sheets = [];

        $this->getCategories()
            ->each(function ($result) use (&$sheets) {
                $query = $this->getSheetData($result->category);
                if (! $query) {
                    return;
                }
                $sheet = new WebArchiveSheet($query, $result->category);
                $sheets[] = $sheet;
            });

        return $sheets;
    }

    protected function getCategories(): Builder
    {
        return WebArchiveTest::query()
            ->select(['category'])
            ->where('server', $this->server)
            ->orderBy('category')
            ->distinct();
    }

    protected function getSheetData(string $category): ?Builder
    {
        $query = WebArchiveTest
            ::query()
            ->select(['web_root', 'page_title'])
            ->where('redirect_url', '0')
            ->where('web_root', 'NOT LIKE', '%delete_%')
            ->where('server', $this->server)
            ->where('category', $category);

        return $query->exists() ? $query :null;
    }
}
