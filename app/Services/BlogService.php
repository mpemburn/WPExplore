<?php

namespace App\Services;

use App\Generators\BlogsCsvGenerator;
use App\Models\Blog;
use App\Models\Option;
use App\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;

class BlogService
{
    public function createActiveBlogsCsv(string $filename = 'active_blogs.csv')
    {
        return (new BlogsCsvGenerator($filename))
            ->setData($this->getActiveBlogs())
            ->run();
    }

    public function createStaleBlogsCsv(string $filename = 'stale_blogs.csv')
    {
        return (new BlogsCsvGenerator($filename))
            ->setData($this->getStaleBlogs())
            ->run();
    }

    public function createMatBlogsCsv(string $filename = 'mat_blogs.csv')
    {
        return (new BlogsCsvGenerator($filename))
            ->setData($this->getActiveBlogs(['/mat']))
            ->run();
    }

    public function getActiveBlogs(array $filter = []): Collection
    {
        $rows = collect();

        $blogs = Blog::where('archived', 0);

        $blogs->each(function ($blog) use ($rows, $filter) {
            if ($blog->blog_id < 2) {
                return;
            }

            $data = [];

            $options = (new Option())->setTable('wp_' . $blog->blog_id . '_options')
                ->whereIn('option_name', ['siteurl', 'admin_email'])
                ->orderBy('option_name');

            $data['blog_id'] = $blog->blog_id;
            $data['last_updated'] = $blog->last_updated;

            $options->each(function (Option $option) use (&$data) {
                $data[$option->option_name] = $option->option_value;
            });

            if ($filter && str_replace($filter, '', $data['siteurl']) === $data['siteurl']) {
                return;
            }

            $rows->push($data);
        });

        return $rows;
    }

    public function getStaleBlogs(): Collection
    {
        $rows = collect();
        $blogs = Blog::where('archived', 0);

        $blogs->each(function ($blog) use (&$rows) {
            if ($blog->blog_id < 2) {
                return;
            }

            $posts = (new Post())->setTable('wp_' . $blog->blog_id . '_posts')
                ->where('post_status', 'publish');

            if ($posts->count() < 3) {
                $post = (new Post())->setTable('wp_' . $blog->blog_id . '_posts')
                    ->where('post_title', 'LIKE', '%Hello World%')
                    ->orWhere('post_title', 'LIKE', '%Sample Page%')
                    ->orWhere('post_content', 'LIKE', '%cupcake%')
                    ->first();

                if ($post) {
                    $data = [];

                    $data['blog_id'] = $blog->blog_id;
                    $data['last_updated'] = $blog->last_updated;

                    $options = (new Option())->setTable('wp_' . $blog->blog_id . '_options')
                        ->whereIn('option_name', ['siteurl', 'admin_email'])
                        ->orderBy('option_name');

                    $options->each(function (Option $option) use (&$data) {
                        $data[$option->option_name] = $option->option_value;
                    });

                    $rows->push($data);
                }
            }
        });

        return $rows;
    }

}
