<?php

namespace App\Services\Searchers;

use App\Models\Option;
use App\Models\Post;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class PostsSearcher extends BlogSearcher
{
    protected array $headers = [
        'Post ID',
        'Page',
        'Title',
        'Content',
        'Created',
    ];

    function process(string $blogId, string $blogUrl): bool
    {
        if (! Schema::hasTable('wp_' . $blogId. '_posts')) {
            return false;
        }

        $foundSomething = false;
        $this->searchRegex = '/' . $this->searchText . '/';

        $posts = (new Post())->setTable('wp_' . $blogId . '_posts')
            ->where('post_status', 'publish')
            ->orderBy('ID');

        $posts->each(function (Post $post) use ($blogUrl, &$foundSomething) {
            $foundContent = preg_match($this->searchRegex, $post->post_content, $matches);
            $foundTitle = preg_match($this->searchRegex, $post->post_title, $matches);
            if ($foundContent || $foundTitle) {
                $foundSomething = true;
                $this->found->push([
                    'blog_url' => $blogUrl,
                    'post_id' => $post->ID,
                    'post_name' => $post->post_name,
                    'title' => $post->post_title,
                    'date' => $post->post_date,
                    'content' => trim($post->post_content),
                ]);
            }
        });

        return $foundSomething;
    }

    function display(): void
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
            echo $page['post_id'];
            echo '      </td>';
            echo '      <td>';
            echo '<a href="' . $url . '" target="_blank">' . $url . '</a><br>';
            echo '      </td>';
            echo '      <td>';
            echo str_replace($this->searchText, '<strong>' . $this->searchText . '</strong>', $page['title']);
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

    function error(): void
    {
        echo 'No search text specified. Syntax: ' . URL::to('/in_post') . '?text=';
    }
}
