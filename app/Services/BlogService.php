<?php

namespace App\Services;

use App\Generators\BlogsCsvGenerator;
use App\Generators\BlogsInDateRangeCsvGenerator;
use App\Models\Blog;
use App\Models\Option;
use App\Models\Post;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;

class BlogService
{
    protected const UNSET_COLUMNS = [
        'active_plugins',
        'current_theme',
        'template'
    ];

    public function createActiveBlogsCsv(string $filename = 'active_blogs.csv')
    {
        return (new BlogsCsvGenerator($filename))
            ->setData($this->getActiveBlogs())
            ->unsetColumns(self::UNSET_COLUMNS)
            ->run();
    }

    public function createStaleBlogsCsv(string $filename = 'stale_blogs.csv')
    {
        return (new BlogsCsvGenerator($filename))
            ->setData($this->getStaleBlogs())
            ->unsetColumns(self::UNSET_COLUMNS)
            ->run();
    }

    public function createMatBlogsCsv(string $filename = 'mat_blogs.csv')
    {
        return (new BlogsCsvGenerator($filename))
            ->setData($this->getActiveBlogs(['/mat']))
            ->unsetColumns(self::UNSET_COLUMNS)
            ->run();
    }

    public function createBlogsInDateRangeCsv(
        string $startDate = null,
        string $endDate = null
    )
    {
        if (! $startDate && ! $endDate) {
            return null;
        }

        $filename = "active_blogs_from_{$startDate}_to_{$endDate}.csv";

        $blogs = $this->getFormattedBlogs($startDate, $endDate);

        return (new BlogsInDateRangeCsvGenerator($filename))
            ->setData($blogs->sortBy('last_updated'))
            ->run();
    }

    public function createMatBlogsInDateRangeCsv(
        string $startDate = null,
        string $endDate = null
    )
    {
        if (! $startDate && ! $endDate) {
            return null;
        }

        $filename = "mat_blogs_from_{$startDate}_to_{$endDate}.csv";

        $blogs = $this->getFormattedBlogs($startDate, $endDate);

        return (new BlogsInDateRangeCsvGenerator($filename))
            ->setData($blogs->sortBy('last_updated'))
            ->run();
    }

    public function getActiveBlogs(array $filter = [], ?string $startDate = null, ?string $endDate = null): Collection
    {
        $rows = collect();
        if ($startDate && $endDate) {
            $blogs = Blog::whereBetween('last_updated', [date($startDate), date($endDate)])->get();
        } else {
            $blogs = Blog::all();
        }

        $blogs->each(function ($blog) use ($rows, $filter) {

            $data = $this->getBlogData($blog, $filter);

            if ($data) {
                $rows->push($data);
            }
        });

        return $rows;
    }

    public function getBlogsById(array $blogIds, array $filter = [])
    {
        $rows = collect();
        $blogs = Blog::whereIn('blog_id', $blogIds)->get();

        $blogs->each(function ($blog) use ($rows, $filter) {

            $data = $this->getBlogData($blog, $filter);

            if ($data) {
                $rows->push($data);
            }
        });

        return $rows;

    }


    protected function getBlogData(Blog $blog, array $filter): ?array
    {
        if ($blog->blog_id < 2) {
            return null;
        }

        $data = [];

        $options = (new Option())->setTable('wp_' . $blog->blog_id . '_options')
            ->whereIn('option_name', ['siteurl', 'admin_email', 'current_theme', 'template', 'active_plugins'])
            ->orderBy('option_name');

        $data['blog_id'] = $blog->blog_id;
        $data['last_updated'] = $blog->last_updated;

        $options->each(function (Option $option) use (&$data) {
            $data[$option->option_name] = $option->option_value;
        });

        if ($filter && str_replace($filter, '', $data['siteurl']) === $data['siteurl']) {
            return null;
        }

        return $data;
    }

    public function findTextInPosts(string $searchText): Collection
    {
        $postsFound = collect();
        $blogs = Blog::where('archived', 0);

        $searchRegex = '/' . $searchText . '/';

        $blogs->each(function ($blog) use ($searchRegex, &$postsFound) {
            $blogId = $blog->blog_id;
            $blogUrl = 'https://' . $blog->domain . $blog->path;

            if (! Schema::hasTable('wp_' . $blogId. '_posts')) {
                return;
            }

            $posts = (new Post())->setTable('wp_' . $blogId . '_posts')
                ->where('post_status', 'publish')
                ->orderBy('ID');

            $posts->each(function (Post $post) use ($searchRegex, $blogUrl, &$postsFound) {
                $foundContent = preg_match($searchRegex, $post->post_content, $matches);
                $foundTitle = preg_match($searchRegex, $post->post_title, $matches);
                if ($foundContent || $foundTitle) {
                    $postsFound->push([
                        'blog_url' => $blogUrl,
                        'post_name' => $post->post_name,
                        'title' => $post->post_title,
                        'date' => $post->post_date,
                        'content' => trim($post->post_content),
                    ]);
                }
            });

        });

        return $postsFound;

    }
    public function findShortCodeInPosts(string $shortCode): Collection
    {
        $postsFound = collect();
        $blogs = Blog::where('archived', 0);

        $shortCodeRegex = '/\[' . str_replace(['[', ']'], '', $shortCode) . '/';

        $blogs->each(function ($blog) use ($shortCodeRegex, &$postsFound) {
            $blogId = $blog->blog_id;
            $blogUrl = 'https://' . $blog->domain . $blog->path;

            if (! Schema::hasTable('wp_' . $blogId. '_posts')) {
                return;
            }

            $posts = (new Post())->setTable('wp_' . $blogId . '_posts')
                ->where('post_status', 'publish')
                ->orderBy('ID');

            $posts->each(function (Post $post) use ($shortCodeRegex, $blogUrl, &$postsFound) {
                $found = preg_match($shortCodeRegex, $post->post_content, $matches);
                if ($found) {
                    $postsFound->push([
                        'blog_url' => $blogUrl,
                        'post_name' => $post->post_name,
                        'title' => $post->post_title,
                        'date' => $post->post_date,
                        'content' => trim($post->post_content),
                    ]);
                }
            });

        });

        return $postsFound;

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

    public function getFormattedBlogs(?string $startDate = null, ?string $endDate = null)
    {
        return $this->getActiveBlogs([], $startDate, $endDate)
            ->transform(function ($blog) {
                $plugins = collect(unserialize($blog['active_plugins']))->map(function ($plugin) {
                    return current(explode('/', $plugin));
                })->implode(',');
                $blog['last_updated'] = Carbon::parse($blog['last_updated'])->format('m/d/Y');
                $blog['template'] = isset($blog['template']) && strlen($blog['template']) > 0
                    ? $blog['template']
                    : 'N/A';
                $blog['current_theme'] = isset($blog['current_theme']) && strlen($blog['current_theme']) > 0
                    ? $blog['current_theme']
                    : 'N/A';
                $blog['active_plugins'] = $plugins;

                return $blog;
            });
    }

    public function findPluginInSubsite(string $pluginName, string $title, $notFoundOnly = false)
    {
        $data = $this->getActiveBlogs();
        $notFound = true;

        $titleRow = '<div><b>' . $title . '</b></div>';
        $html = '<div style="font-family:  sans-serif">';
        $html .= $titleRow;
        $data->each(function ($row) use (&$notFound, &$html, $pluginName) {
            if (isset($row['active_plugins']) && stripos($row['active_plugins'], $pluginName) !== false) {
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
            if (!isset($row['current_theme'])) {
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
