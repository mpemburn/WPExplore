<?php

namespace App\Services;

use App\Generators\BlogsCsvGenerator;
use App\Models\Blog;
use App\Models\Option;
use App\Models\Post;
use Illuminate\Support\Carbon;
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
                ->whereIn('option_name', ['siteurl', 'current_theme', 'template', 'admin_email', 'active_plugins'])
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

    public function findPluginInSubsite(string $pluginName, string $title, $notFoundOnly = false)
    {
        $data = $this->getActiveBlogs();
        $notFound = true;

        $titleRow = '<div><b>' . $title . '</b></div>';
        $html = '<div style="font-family:  sans-serif">';
        $html .= $titleRow;
        $data->each(function ($row) use (&$notFound, &$html, $pluginName) {
            if (stripos($row['active_plugins'], $pluginName) !== false) {
                $html .= '<div>';
                $html .= $row['siteurl'];
                $html .= '</div>';
                $notFound = false;
            }
        });

        if ($notFoundOnly) {
            return $notFound ? $titleRow : '';
        }

        return $notFound ? $titleRow . '<div style="font-family: sans-serif">Not Found</div><br>' : $html . '<br>';
    }

    public function findThemesInSubsite(string $themeName, $notFoundOnly = false)
    {
        $data = $this->getActiveBlogs();
        $notFound = true;
        $notFoundMsg = '<div style="font-family: sans-serif">Not Found</div><br>';

        $titleRow = '<div style="font-family: sans-serif"><b>' . $themeName . '</b></div>';
        $html = '<div style="font-family: sans-serif">';
        $html .= $titleRow;
        $data->each(function ($row) use (&$notFound, &$html, $themeName) {
            if (! isset($row['current_theme'])) {
                return;
            }
            if ($row['current_theme'] === $themeName) {
                $html .= '<div>';
                $html .= $row['siteurl'];
                $html .= '</div>';
                $notFound = false;
            }
        });
        $html .= '</div>';

        if ($notFoundOnly) {
            return $notFound ? $titleRow : '';
        }

        return $notFound ? $titleRow . '<div style="font-family: sans-serif">Not Found</div><br>' : $html . '<br>';
    }

}
