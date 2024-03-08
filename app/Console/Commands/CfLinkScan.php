<?php

namespace App\Console\Commands;

use App\Facades\Curl;
use App\Models\CfLinks;
use Illuminate\Console\Command;

class CfLinkScan extends Command
{
    public const VALID_FILE_EXTENSIONS = [
        '.3g2',
        '.3gp',
        '.avchd',
        '.avi',
        '.avi',
        '.bin',
        '.doc',
        '.docx',
        '.flv',
        '.gif',
        '.jpeg',
        '.jpg',
        '.key',
        '.m4a',
        '.m4p',
        '.m4v',
        '.mid',
        '.midi',
        '.mov',
        '.mp2',
        '.mp3',
        '.mp4',
        '.mpe',
        '.mpeg',
        '.mpg',
        '.mpv',
        '.odt',
        '.ogg',
        '.ogv',
        '.pdf',
        '.png',
        '.pps',
        '.ppsx',
        '.ppt',
        '.pptx',
        '.pub',
        '.qt',
        '.swf',
        '.txt',
        '.wav',
        '.webm',
        '.wmv',
        '.xls',
        '.xlsx',
        '.zip',
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cf:scan';

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
        CfLinks::all()->each(function ($cfLink) {
            $redirect = null;
            if (empty($cfLink)) {
                return;
            }
            if ($this->flagAsInvalid($cfLink->url)) {
                $redirect = 'DOCUMENT';
            } else {
                $redirect = Curl::getRedirect($cfLink->url);
            }
            $cfLink->update([
                'redirect' => $redirect
            ]);
            echo $cfLink->url . PHP_EOL;
            echo 'Redirected to: ' . $redirect . PHP_EOL;
        });

        return Command::SUCCESS;
    }

    protected function flagAsInvalid(string $url): bool
    {
        foreach (self::VALID_FILE_EXTENSIONS as $ext) {
            if (str_contains($url, $ext)) {
                return true;
            }
        }

        return false;
    }
}
