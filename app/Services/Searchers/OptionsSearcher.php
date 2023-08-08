<?php

namespace App\Services\Searchers;

use App\Models\Option;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class OptionsSearcher extends BlogSearcher
{
    protected array $already = [];
    protected array $headers = [
        'Blog ID',
        'Blog URL',
        'Option',
        'Value'
    ];

    public function process(string $blogId, string $blogUrl): bool
    {
        if (!Schema::hasTable('wp_' . $blogId . '_options')) {
            return false;
        }
        $foundSomething = false;
        $this->searchRegex = '/' . $this->searchText . '/';

        $options = (new Option())->setTable('wp_' . $blogId . '_options')
            ->orderBy('option_id');

        $options->each(function (Option $option) use ($blogId, $blogUrl, &$foundSomething) {
            if ($this->shouldSearchField) {
                $foundContent = preg_match($this->searchRegex, $option->option_name, $matches);
            } else {
                $foundContent = preg_match($this->searchRegex, $option->option_value, $matches);
            }

            if ($foundContent) {
                $foundSomething = true;
                $this->found->push([
                    'blog_id' => $blogId,
                    'blog_url' => $blogUrl,
                    'option_name' => $option->option_name,
                    'option_value' => $option->option_value,
                ]);
            }
        });

        return $foundSomething;
    }

    public function display(bool $showNotFound = false): void
    {
        $found = $showNotFound ? $this->notFound : $this->found;
        $count = 0;
        echo '<div style="font-family: sans-serif">';
        echo '<table>';
        echo $this->buildHeader();
        $found->each(function ($item) use (&$count) {
            if (in_array($item['blog_id'], $this->unique)) {
                return;
            }
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
            $this->unique[] = $item['blog_id'];
        });
        echo '<table>';
        echo '<br><strong>Total Found: ' . $count . '</strong>';
        echo '<div>';
    }

    protected function error(): void
    {
        echo 'No text specified. Syntax: ' . URL::to('/in_post') . '?text=';
    }
}
