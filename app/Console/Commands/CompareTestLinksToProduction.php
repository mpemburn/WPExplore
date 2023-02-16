<?php

namespace App\Console\Commands;

use App\Models\WordPressTestBrokenPage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class CompareTestLinksToProduction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'links:compare';

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
        Config::set('database.default', 'mysql_remote');
        $records = WordPressTestBrokenPage::where('error', '<>', 'success')
            ->whereRaw("`page_url` NOT REGEXP 'doc$|docx$|jpg$|jpeg$|gif$|mp3$|png$|pdf$|txt$|wav$|wmv$|xls$|xlsx$|zip$'");

        $rows = $records->get()->toArray();
        foreach ($rows as $row) {
            $prodUrl = str_replace('.test', '', $row['page_url']);
            $code = $this->testUrl($prodUrl);
            if ($code === 200) {
                echo $prodUrl . PHP_EOL;
                echo '-- Found' . PHP_EOL;
            }
        }

        return Command::SUCCESS;
    }

    protected function testUrl(string $url): int
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return (int) $code;
    }

}
