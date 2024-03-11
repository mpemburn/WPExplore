<?php
/*
 * Thanks to MogulChris (https://gist.github.com/MogulChris)
 * Based on the Gist: MogulChris/screenshot.php (https://gist.github.com/MogulChris/6f2facf768ac3f280e9ad765e531dd55)
 */
namespace App\Services;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverDimension;
use Illuminate\Support\Facades\Storage;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Chrome\ChromeProcess;
use Symfony\Component\Process\Exception\RuntimeException;
use Facebook\WebDriver\Exception\UnknownErrorException;

class BrowserService
{
    const DEFAULT_BROWSER_WIDTH = 1920;

    protected $browser;

    public function __construct()
    {
        try {
            //Make a Chrome browser
            $process = (new ChromeProcess)->toProcess();
            $process->start();
            $options = (new ChromeOptions)->addArguments(['--disable-gpu', '--headless']);
            $capabilities = DesiredCapabilities::chrome()->setCapability(ChromeOptions::CAPABILITY, $options);
            $driver = retry(5, function () use($capabilities) {
                return RemoteWebDriver::create('http://localhost:9515', $capabilities);
            }, 50);

            $this->browser = new Browser($driver);
        } catch (RuntimeException $rune) {
            echo 'RuntimeException: Failed to start a new screenshot process.' . PHP_EOL;
        } catch (UnknownErrorException $unke) {
            echo 'UnknownErrorException: Failed to start a new screenshot process.' . PHP_EOL;
        }
    }

    public function scrapeElement(string $url, string $element): string
    {
        $this->browser->visit($url);
        $element = $this->browser->element($element);

        if ($element) {
            return $element->getDomProperty('innerHTML');
        }

        return 'Not found';
    }

    public function screenshot(string $url, string $title): bool
    {
        $filename = str_replace(' ', '', $title) . '.png';
        $filePath = Storage::path($this->saveDirectory . '/screenshots/') . '/' . $filename;

        // If we've created the file already, no need to redo
        if (file_exists($filePath)) {
            return true;
        }

        $this->browser->visit($url);

        try {
            // Calculate screen height
            $screenHeight = $this->getScreenHeight();

            $this->browser->pause(1000);
            // Set dimensions for screenshot
            $size = new WebDriverDimension(self::DEFAULT_BROWSER_WIDTH, $screenHeight);
            $this->browser->driver->manage()->window()->setSize($size);

            $this->browser->pause(3000);

            $image = $this->browser->driver->TakeScreenshot();

            // Save the image
            Storage::disk('local')->put($this->saveDirectory . '/screenshots/' . $filename, $image);

            return file_exists($filePath);
        } catch (\Exception $e) {
            echo 'Exception occurred creating screenshot for ' . $url . PHP_EOL;

            return false;
        }
    }

    protected function getScreenHeight(): int
    {
        $dims = $this->browser->script([
            'let body = document.body;
                let html = document.documentElement;
                let totalHeight = Math.max(
                    body.scrollHeight,
                    body.offsetHeight,
                    html.clientHeight,
                    html.scrollHeight,
                    html.offsetHeight
                );

                return {height: totalHeight};'
        ]);

        return current($dims)['height'];
    }
}
