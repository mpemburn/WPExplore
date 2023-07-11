<?php

namespace App\Services\Searchers;

use App\Models\Post;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class ShortCodeSearcher extends BlogSearcher
{
    function process(string $blogId, string $blogUrl): void
    {
        if (! Schema::hasTable('wp_' . $blogId. '_posts')) {
            return;
        }

        $this->searchRegex ='/\[' . str_replace(['[', ']'], '', $this->searchText) . '/';

        $posts = (new Post())->setTable('wp_' . $blogId . '_posts')
            ->where('post_status', 'publish')
            ->orderBy('ID');

        $posts->each(function (Post $post) use ($blogUrl) {
            $found = preg_match($this->searchRegex, $post->post_content, $matches);
            if ($found) {
                $this->found->push([
                    'blog_url' => $blogUrl,
                    'post_name' => $post->post_name,
                    'title' => $post->post_title,
                    'date' => $post->post_date,
                    'content' => trim($post->post_content),
                ]);
            }
        });
    }

    function display(): void
    {
        echo '<div style="font-family: sans-serif">';
        echo '<table>';
        echo '   <tr style="background-color: #e2e8f0;">';
        echo '      <td>';
        echo 'Page';
        echo '      </td>';
        echo '      <td>';
        echo 'Title';
        echo '      </td>';
        echo '      <td>';
        echo 'Content';
        echo '      </td>';
        echo '      <td>';
        echo 'Created';
        echo '      </td>';
        echo '   </tr>';
        $this->found->each(function ($page) {
            $url = $page['blog_url'] . $page['post_name'];
            echo '   <tr>';
            echo '      <td>';
            echo '<a href="' . $url . '" target="_blank">' . $url . '</a><br>';
            echo '      </td>';
            echo '      <td>';
            echo $page['title'];
            echo '      </td>';
            echo '      <td>';
            echo str_replace($this->searchText, '<strong>' . $this->searchText . '</strong>', $page['content']);
            echo '      </td>';
            echo '      <td>';
            echo Carbon::parse($page['date'])->format('F j, Y');
            echo '      </td>';
            echo '   </tr>';
        });
        echo '<div>';
        echo '<table>';
    }

    function error(): void
    {
        echo 'No shortcode specified. Syntax: ' . URL::to('/shortcode') . '?text=';
    }
}
