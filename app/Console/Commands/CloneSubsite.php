<?php

namespace App\Console\Commands;

use App\Services\CloneService;
use Illuminate\Console\Command;

class CloneSubsite extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clone:site {--source=} {--dest=} {--blog_id=}';

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
        $sourceDb = $this->option('source');
        $destDb = $this->option('dest');
        $blogId = $this->option('blog_id');

        (new CloneService())->clone($sourceDb, $destDb, 'wp_' . $blogId . '_');

        return Command::SUCCESS;
    }
}
