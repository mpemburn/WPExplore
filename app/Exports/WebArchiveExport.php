<?php
namespace App\Exports;

use App\Exports\Sheets\WebArchiveSheet;
use App\Services\WebArchiveService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class WebArchiveExport implements WithMultipleSheets
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
            'Title',
        ];
    }

    public function sheets(): array
    {
        $sheets = collect();

        (new WebArchiveService())
            ->getFileList('public/' . $this->server)
            ->each(function ($file) use (&$sheets) {
                $category = $file->getFilenameWithoutExtension();
                $sheet = new WebArchiveSheet($this->server, $category);
                if (empty($sheet)) {
                   return;
                }
                $sheets->push($sheet);
            });

        return $sheets->filter()->toArray();
    }
}
