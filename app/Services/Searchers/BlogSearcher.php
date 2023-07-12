<?php

namespace App\Services\Searchers;

use App\Models\Blog;
use App\Models\Option;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

abstract class BlogSearcher
{

    protected Collection $found;
    protected string $searchText;
    protected string $searchRegex;

    abstract function process(string $blogId, string $blogUrl): void;
    abstract function display(): void;
    abstract function error(): void;

    public function __construct()
    {
        $this->found = collect();
    }

    public function run(?string $searchText): self
    {
        if (! $searchText) {
            $this->error();

            return $this;
        }

        $blogs = Blog::where('archived', 0);

        $this->searchText = $searchText;

        $blogs->each(function ($blog) use ($searchText) {
            $blogId = $blog->blog_id;
            $blogUrl = 'https://' . $blog->domain . $blog->path;
            $this->process($blogId, $blogUrl);
        });

        return $this;
    }

    protected function truncateContent(string $content)
    {
        $highlight = str_replace($this->searchText, '<strong>' . $this->searchText . '</strong>', $content);
        $position = stripos($highlight, $this->searchText);

        $start = ($position - 20) > 0 ? $position - 20 : 0;
        $prellipsis = $start > 0 ? '&hellip;' : '';
        $postellipsis = strlen($highlight) > 50 ? '&hellip;' : '';

        return $prellipsis . substr($highlight, $start, 50) . $postellipsis;
    }

}
