<?php

namespace App\Console\Commands;

use App\Models\FoundImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImageFindCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'image:find';

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
        $images = FoundImage::all();

        $images->each(function (FoundImage $image) {
            $originalImage = str_replace('wordpress.test', 'wordpress', $image->image_url);
            $success = false;
            $originalExists = true;
            // If image doesn't exist on the original page, this is not a failure of the migration script.
            if (! $this->url_exists($image->$originalImage)) {
                $success = true;
                $originalExists = false;
            }
            if (! $this->url_exists($image->image_url) && ! $success) {
                $success = false;
            }

            $image->original_exists = $originalExists;
            $image->success = $success;
            $image->save();
        });
        return Command::SUCCESS;
    }

    protected function url_exists($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($code == 200);
    }

}
