<?php

namespace App\Console\Commands;

use App\Models\CfLinks;
use Illuminate\Console\Command;

class CfRedirects extends Command
{
    const FIELDS = [
        'server',
        'category',
        'url',
        'redirect',
    ];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cf:redirect';

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
        $links = CfLinks::whereNotNull('redirect')
            ->orderBy('server')
            ->orderBy('category')
            ->get();

        $links->each(function ($link) {
            if ($link->redirect !== $link->url) {
                return;
            }
            $line = [];
            foreach (self::FIELDS as $field) {
                $prop = $field;
                $line[] = '"' . $link->$prop . '"';
            }

            echo implode(',', $line) . PHP_EOL;
        });

        return Command::SUCCESS;
    }
}
