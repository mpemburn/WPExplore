<?php

namespace App\Console\Commands;

use App\Models\LinksOnCurrentSites;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PopulateSiteLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:links';

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
        $links = Storage::path('public/cf_links/charlotte_links.csv');
        $file = fopen($links, "r");
        $count = 0;
        while (($data = fgetcsv($file, 200, ",")) !== false) {
            if ($count === 0 || ! isset($data[1])) {
                $count++;
                continue;
            }
            $url = $data[0] . $data[1];
            $date = $data[2];
            $linkData = $data[3];
            $links = explode(' | ', $linkData);
            foreach ($links as $link) {
                LinksOnCurrentSites::create([
                    'url' => $url,
                    'post_date' => $date,
                    'link' => $link
                ]);
                echo $link . PHP_EOL;
            }
            $count++;
        }
        fclose($file);

        return Command::SUCCESS;
    }
}
