<?php

namespace App\Services;

use App\Models\Blog;
use App\Models\Option;
use Illuminate\Support\Facades\Response;

class BlogService
{
    public function createCsv(string $filename = 'blogs.csv')
    {
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns =  [
            "Blog ID",
            "Last Update",
            "Admin Email",
            "URL",
        ];

        $rows = collect();

        $blogs = Blog::where('archived', 0);

        $blogs->each(function ($blog) use ($rows) {
            if ($blog->blog_id < 2) {
                return;
            }

            $data = [];

            $options = (new Option())->setTable('wp_'. $blog->blog_id .'_options')
                ->whereIn('option_name', ['siteurl', 'admin_email'])
                ->orderBy('option_name');

            $data[] = $blog->blog_id;
            $data[] = $blog->last_updated;

            $options->each(function (Option $option) use (&$data) {
                $data[] = $option->option_value;
            });

            $rows->push($data);
        });


        $callback = function () use ($rows, $columns)
        {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $rows->each(function ($row) use ($file) {
                fputcsv($file, $row);
            });

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
