<?php

namespace App\Console\Commands;

use App\Models\CfLegacyApp;
use DOMDocument;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RedirectMiddleware;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestUrlsForRedirects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redirect:test {--server=} {--file=} {--baseurl=} {--path=}';

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
        $baseUrl = $this->option('baseurl');
        $path = $this->option('path');

        $baseUrl .= $path;

        $sourceFile = $this->option('file');
        $rootPath = Storage::path($sourceFile);

        $sourcePaths = explode("\n", file_get_contents($rootPath));

        collect($sourcePaths)->each(function ($rawPath) use ($baseUrl) {
            $properties = [];
            $sourcePath = str_replace(['///'], ['/'], $baseUrl . '/' . $rawPath . '/');

            $properties['server'] = $this->option('server');
            $properties['web_root'] = $rawPath;
            $properties['index_url'] = $sourcePath;

            try {

                $client = new Client(['allow_redirects' => ['track_redirects' => true]]);
                $response = $client->get($sourcePath);

                $properties['page_title'] = null;
                if ($response) {
                    $properties['page_title'] = $this->getPageTitle($response);
                }

                $redirectedUrls = $response->getHeader(RedirectMiddleware::HISTORY_HEADER);
                $redirectPath = end($redirectedUrls);
                $properties['redirect_url'] = $redirectPath;

                if ($redirectPath && $redirectPath !== $sourcePath) {
                    echo 'Redirected: ' . $sourcePath . ' -> ' . $redirectPath .  PHP_EOL;
                } else {
                    echo 'Not Redirected: ' . $sourcePath . PHP_EOL;
                }

            } catch (GuzzleException $e) {
                echo 'ERROR: ' . $sourcePath . ' -- ' . $e->getCode() . PHP_EOL;
                $properties['error_code'] = $e->getCode();
            }
//            var_dump($properties);
            CfLegacyApp::create($properties);
        });

        return Command::SUCCESS;
    }

    protected function getPageTitle($response): ?string
    {
        if ($response) {
            $rawBody = $response->getBody()->getContents();
            $endHead = stripos($rawBody, '</head>');
            $body = substr($rawBody, 0, $endHead);

            if (! $body || strlen($body) === 0) {
                return null;
            }

            $doc = new DOMDocument();
            libxml_use_internal_errors(true);
            $doc->loadHTML($body);
            libxml_clear_errors();
            $titles = $doc->getElementsByTagName("title");

            return $titles->count() > 0 ? $titles->item(0)->nodeValue : null;
        }

        return null;
    }
}
