<?php

namespace App\Services;

use App\Generators\BlogsCsvGenerator;
use App\Generators\BlogsInDateRangeCsvGenerator;

class CsvService
{
    public const AVAILABLE_CSV_TYPES = [
        'All Active Blogs' => 'createActiveBlogsCsv',
        'All Stale Blogs' => 'createStaleBlogsCsv',
        'All Active Blogs in Date Range' => 'createBlogsInDateRangeCsv',
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


}
