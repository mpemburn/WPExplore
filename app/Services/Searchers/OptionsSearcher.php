<?php

namespace App\Services\Searchers;

use App\Models\Option;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class OptionsSearcher extends BlogSearcher
{
    function process(string $blogId, string $blogUrl): void
    {
        if (! Schema::hasTable('wp_' . $blogId. '_options')) {
            return;
        }

        $this->searchRegex = '/' . $this->searchText . '/';

        $options = (new Option())->setTable('wp_' . $blogId . '_options')
            ->orderBy('option_id');

        $options->each(function (Option $option) use ($blogId, $blogUrl) {
            $foundContent = preg_match($this->searchRegex, $option->option_value, $matches);

            if ($foundContent) {
                $this->found->push([
                    'blog_id' => $blogId,
                    'blog_url' => $blogUrl,
                    'option_name' => $option->option_name,
                    'option_value' => $option->option_value,
                ]);
            }
        });
    }

    function display(): void
    {
        $count = 0;
        echo '<div style="font-family: sans-serif">';
        echo '<table>';
        echo '   <tr style="background-color: #e2e8f0;">';
        echo '      <td>';
        echo 'Blog ID';
        echo '      </td>';
        echo '      <td>';
        echo 'Blog URL';
        echo '      </td>';
        echo '      <td>';
        echo 'Option';
        echo '      </td>';
        echo '      <td>';
        echo 'Value';
        echo '      </td>';
        echo '   </tr>';
        $this->found->each(function ($item) use (&$count) {
            $url = $item['blog_url'];
            $bgColor = ($count % 2) === 1 ? '#e2e8f0' : '#fffff';
            echo '   <tr style="background-color: ' . $bgColor . ';">';
            echo '      <td>';
            echo $item['blog_id'];
            echo '      </td>';
            echo '      <td>';
            echo '<a href="' . $url . '" target="_blank">' . $url . '</a><br>';
            echo '      </td>';
            echo '      <td>';
            echo $item['option_name'];
            echo '      </td>';
            echo '      <td>';
            echo $this->truncateContent($item['option_value']);
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
        echo 'No text specified. Syntax: ' . URL::to('/in_post') . '?text=';
    }
}
