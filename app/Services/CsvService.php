<?php

namespace App\Services;

use App\Generators\BlogsCsvGenerator;
use App\Generators\BlogsInDateRangeCsvGenerator;
use Illuminate\Http\Request;

class CsvService
{
    public const AVAILABLE_CSV_TYPES = [
        'All Active Blogs' => 'active_blogs',
        'All Stale Blogs' => 'stale_blogs',
        'All Active Blogs in Date Range' => 'active_blogs_in_date_range',
    ];

    protected const UNSET_COLUMNS = [
        'active_plugins',
        'current_theme',
        'template'
    ];

    protected $blogService;

    public function __construct(BlogService $blogService)
    {
        $this->blogService = $blogService;
    }

    public function callCsvMethod(string $type, Request $request): string
    {
        switch ($type) {
            case 'active_blogs':
                break;
            case 'stale_blogs':
                break;
            case 'active_blogs_in_date_range':
                break;

        }
    }

    public function createActiveBlogsCsv(string $filename = 'active_blogs.csv')
    {
        return (new BlogsCsvGenerator($filename))
            ->setData($this->blogService->getActiveBlogs())
            ->unsetColumns(self::UNSET_COLUMNS)
            ->run();
    }

    public function createStaleBlogsCsv(string $filename = 'stale_blogs.csv')
    {
        return (new BlogsCsvGenerator($filename))
            ->setData($this->blogService->getStaleBlogs())
            ->unsetColumns(self::UNSET_COLUMNS)
            ->run();
    }

    public function createBlogsInDateRangeCsv(
        string $startDate = null,
        string $endDate = null
    )
    {
        if (!$startDate && !$endDate) {
            return null;
        }

        $filename = "active_blogs_from_{$startDate}_to_{$endDate}.csv";

        $blogs = $this->blogService->getFormattedBlogs($startDate, $endDate);

        return (new BlogsInDateRangeCsvGenerator($filename))
            ->setData($blogs->sortBy('last_updated'))
            ->run();
    }

    public function createMatBlogsCsv(string $filename = 'mat_blogs.csv')
    {
        return (new BlogsCsvGenerator($filename))
            ->setData($this->blogService->getActiveBlogs(['/mat']))
            ->unsetColumns(self::UNSET_COLUMNS)
            ->run();
    }

    protected function makeFilenameFromDates()
    {

    }
}
