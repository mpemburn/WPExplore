<?php

namespace App\Services\Searchers;

use App\Models\Blog;
use App\Models\Option;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

abstract class BlogSearcher
{

    protected Collection $found;
    protected Collection $notFound;
    protected string $searchText;
    protected string $searchRegex;
    protected bool $verbose;
    protected array $headers = [];
    protected array $unique = [];
    protected bool $shouldSearchField = false;

    abstract public function process(string $blogId, string $blogUrl): bool;
    abstract public function display(): void;
    abstract protected function error(): void;

    public function __construct()
    {
        $this->found = collect();
        $this->notFound = collect();
    }

    public function run(?string $searchText, bool $verbose = false): self
    {
        if (! $searchText) {
            $this->error();

            return $this;
        }

        $this->verbose = $verbose;

        $blogs = Blog::where('archived', 0)
            ->where('deleted', 0)
            ->where('public', 1);

        $this->searchText = $searchText;

        $blogs->each(function ($blog) use ($searchText) {
            $blogId = $blog->blog_id;
            $blogUrl = 'https://' . $blog->domain . $blog->path;
            $found = $this->process($blogId, $blogUrl);
        });

        return $this;
    }

    public function searchFieldName(bool $shouldSearch): self
    {
        $this->shouldSearchField = $shouldSearch;

        return $this;
    }

    protected function buildHeader(): string
    {
        $html = '   <tr style="background-color: #e2e8f0;">';
        foreach ($this->headers as $header) {
            $html .= '      <td>';
            $html .= $header;
            $html .= '      </td>';
        }
        $html .= '   </tr>';

        return $html;
    }

    protected function truncateContent(string $content): string
    {
        $length = $this->verbose ? null : 100;

        $highlight = str_replace($this->searchText, '<strong>' . $this->searchText . '</strong>', $content);
        $position = stripos($highlight, $this->searchText);

        $start = ($position - 20) > 0 ? $position - 20 : 0;
        $prellipsis = $start > 0 ? '&hellip;' : '';
        $postellipsis = ! $this->verbose && strlen($highlight) > $length ? '&hellip;' : '';

        return $prellipsis . substr($highlight, $start, $length) . $postellipsis;
    }

}
