<?php

namespace App\Console\Commands;

use App\Models\MissingLink;
use App\Models\WordpressProductionLink;
use App\Models\WordpressTestLink;
use Illuminate\Console\Command;

class ProcessMissingLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:links';

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
        (new WordpressTestLink())->where('found', 0)
            ->each(function (WordpressTestLink $testLink) {
                $parts = pathinfo($testLink->link_url);
                $fromProd = (new WordpressProductionLink())
                                ->where('blog_id', $testLink->blog_id)
                                ->where('link_url', 'LIKE', '%' . $parts['basename'])
                                ->first();
                if (! $fromProd) {
                    return;
                }
                if ($fromProd->found) {
                    $missingLink = new MissingLink();
                    $missingLink->create([
                        'blog_id' => $testLink->blog_id,
                        'page_url' => $testLink->page_url,
                        'link_url' => $testLink->link_url,
                        'found' => false

                    ]);
                }
            });

        return Command::SUCCESS;
    }
}
