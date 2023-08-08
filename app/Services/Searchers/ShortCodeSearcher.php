<?php

namespace App\Services\Searchers;

use App\Models\Post;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class ShortCodeSearcher extends BlogSearcher
{
    protected array $headers = [
        'Page',
        'Title',
        'Content',
        'Created',
    ];

    public function process(string $blogId, string $blogUrl): bool
    {
        if (! Schema::hasTable('wp_' . $blogId. '_posts')) {
            return false;
        }
        $foundSomething = false;
        $this->searchRegex ='/\[' . str_replace(['[', ']'], '', $this->searchText) . '/';

        $posts = (new Post())->setTable('wp_' . $blogId . '_posts')
            ->where('post_status', 'publish')
            ->orderBy('ID');

        $posts->each(function (Post $post) use ($blogUrl, &$foundSomething) {
            $found = preg_match($this->searchRegex, $post->post_content, $matches);
            if ($found) {
                $foundSomething = true;
                $this->found->push([
                    'blog_url' => $blogUrl,
                    'post_name' => $post->post_name,
                    'title' => $post->post_title,
                    'date' => $post->post_date,
                    'content' => trim($post->post_content),
                ]);
            }
        });

        return $foundSomething;
    }

    public function display(): void
    {
        $count = 0;
        echo '<div style="font-family: sans-serif">';
        echo '<table>';
        echo $this->buildHeader();
        $this->found->each(function ($page) use (&$count) {
            $url = $page['blog_url'] . $page['post_name'];
            $bgColor = ($count % 2) === 1 ? '#e2e8f0' : '#fffff';
            echo '   <tr style="background-color: ' . $bgColor . ';">';
            echo '      <td>';
            echo '<a href="' . $url . '" target="_blank">' . $url . '</a><br>';
            echo '      </td>';
            echo '      <td>';
            echo $page['title'];
            echo '      </td>';
            echo '      <td>';
            echo $this->truncateContent($page['content']);
            echo '      </td>';
            echo '      <td>';
            echo Carbon::parse($page['date'])->format('F j, Y');
            echo '      </td>';
            echo '   </tr>';

            $count++;
        });
        echo '<table>';
        echo '<br><strong>Total Found: ' . $count . '</strong>';
        echo '<div>';
    }

    protected function error(): void
    {
        echo 'No shortcode specified. Syntax: ' . URL::to('/shortcode') . '?text=';
    }
}
