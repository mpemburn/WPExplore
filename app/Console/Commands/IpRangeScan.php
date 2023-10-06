<?php

namespace App\Console\Commands;

use App\Services\UrlService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Process\Process;

class IpRangeScan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scan:ip {--range=} {--tld=} {--test}';
    protected $tld;
    protected $test;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan a range of IP address with nslookup and return names';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $range = $this->option('range');
        $this->tld = $this->option('tld') ?: 'edu';
        $this->test = $this->option('test');
        $this->verbose = $this->option('verbose');

        $range = str_ends_with($range, '.') ? $range : $range . '.';

        for ($octet = 0; $octet < 256; $octet++) {
            $ip = $range . $octet;
            $process = new Process(['nslookup', $ip]);
            $process->run();

            if ($process->isSuccessful()) {
                $output = $process->getOutput();
                $this->getNamesFromOutput($output)->each(function ($name) use ($ip) {
                    if ($this->test) {
                        $this->testUrl($name);
                    } else {
                        echo $name . ',' . $ip . PHP_EOL;
                    }
                });
            }
        }

        return Command::SUCCESS;
    }

    protected function testUrl(string $name): void
    {
        $url = 'https://' . $name;
        if ($this->verbose) {
            echo 'Testing: ' . $url . PHP_EOL;
        }

        $code = (new UrlService())->testUrl($url);
        if ($code === 200) {
            echo $url . ' -- Found'. PHP_EOL;
        } else {
            if ($this->verbose) {
                echo '-- Returned code: ' . $code . PHP_EOL;
            }
        }
    }

    protected function getNamesFromOutput(string $output): Collection
    {
        $names = collect();

        $split = explode('name = ', $output);
        if (count($split) > 2) {
            array_shift($split);
            foreach ($split as $item) {
                if (($pos = strpos($item, $this->tld)) !== FALSE) {
                    $names->push(substr($item, 0, $pos + 4));
                }
            }
        } else {
            $name = trim(end($split));
            $names->push(substr($name, 0, strlen($name) - 1));
        }

        return $names;
    }
}
