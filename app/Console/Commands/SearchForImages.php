<?php

namespace App\Console\Commands;

use App\Facades\Database;
use App\Facades\Reader;
use App\Models\Post;
use App\Services\BlogService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SearchForImages extends Command
{
    const SKIP = [
        '1.png',
        '2.png',
        '1.jpg',
        '2.jpg',
        '1-scaled.jpg',
        '2-scaled.jpg'
    ];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'image:search';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $images = Storage::path('webp-candidates.txt');
        $candidates = collect(Reader::getContentsAsArray($images))->unique();
        $count = 0;
        $candidates->each(function ($image) use (&$count) {
            if (in_array($image, self::SKIP)) {
                return;
            }
            $this->lookForImage($image);
            $count++;
        });

        echo $count . PHP_EOL;

        return Command::SUCCESS;
    }

    protected function lookForImage(string $image): void
    {
        Database::setDb('www_clarku');
        $blogs = (new BlogService())->getActiveBlogs();
        $foundRecords = collect();

        $blogs->each(function ($blog)  use ($image, &$foundRecords) {
            $blogUrl = $blog['siteurl'];
            $blogId = $blog['blog_id'];
            $posts = (new Post())->setTable('wp_' . $blogId . '_posts')
                ->where('post_status', 'publish')
                ->orderBy('ID');

            $posts->each(function (Post $post) use ($blogUrl, $image, $blogId, &$foundRecords) {
                $found = stripos($post->post_content, $image) !== false;
                if ($found) {
                    echo 'IMAGE: ' . $image . PHP_EOL;
                    $foundRecords->push([
                        'blog_url' => $blogUrl,
                        'blog_id' => $blogId,
                        'post_name' => $post->post_name,
                        'post_type' => $post->post_type,
                        'title' => $post->post_title,
                        'date' => $post->post_date,
                        'content' => trim($post->post_content),
                    ]);
                }
            });

        });

        if ($foundRecords->count() > 0) {
            $foundRecords->each(function ($post) {
                echo $post['blog_url']  . '/' . $post['post_name']  . PHP_EOL;
            });
        }
    }
}
