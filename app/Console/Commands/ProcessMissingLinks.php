<?php

namespace App\Console\Commands;

use App\Models\MissingLink;
use App\Models\ProductionLink;
use App\Models\TestLink;
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
        (new TestLink())->where('found', 0)
            ->each(function (TestLink $testLink) {
                $parts = pathinfo($testLink->link_url);
                $fromProd = (new ProductionLink())
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
