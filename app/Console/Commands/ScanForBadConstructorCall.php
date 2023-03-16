<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class ScanForBadConstructorCall extends Command
{
    protected Collection $children;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scan:constructor';

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
        $this->children = collect();

        $path = 'C:\Users\mpemburn\Documents\Sandbox\wpexplore\storage\app\wordpress_clarku_plugins';
        collect(File::allFiles($path))->each(function ($file) {
            $this->gatherChildren($file);
        });

//        collect(File::allFiles($path))->each(function ($file) {
//            $this->inspectChildren($file);
//        });
    }

    protected function gatherChildren($file)
    {
        $filePath = $file->getRelativePathname();
        if (! str_ends_with($filePath, '.php')) {
            return;
        }
        $contents = $file->getContents();
        if (stripos($contents, 'extends WP_Widget')) {
            $lines = collect(explode("\n", $contents));
            //echo $filePath . PHP_EOL;
            $echoLine = false;
            $lines->each(function ($line) use ($filePath, &$echoLine) {
                if (stripos($line, '@extends WP_Widget')) {
                    return;
                }
                if (stripos($line, 'extends WP_Widget')) {
                    $class = str_replace(['abstract ', 'class ', ' extends WP_Widget', ' {'], '', $line);
                    $this->children->push(trim($class));
                    echo '*****' . $filePath . PHP_EOL;
                    $echoLine = true;
                }
                if ($echoLine) {
                    echo $line . PHP_EOL;
                    if ($line === '}') {
                        $echoLine = false;
                    }
                }
            });
        }
    }

    protected function inspectChildren($file)
    {
        $filePath = $file->getRelativePathname();
        if (! str_ends_with($filePath, '.php')) {
            return;
        }
        $contents = $file->getContents();
        $this->children->each(function ($child) use ($contents, $filePath) {
            if (stripos($contents, 'new ' . $child)) {
                echo $filePath . PHP_EOL;
            }
        });

    }
}
