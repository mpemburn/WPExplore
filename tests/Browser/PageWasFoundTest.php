<?php

namespace Tests\Browser;

use App\Models\WordPressTestBrokenPage;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PageWasFoundTest extends DuskTestCase
{
    public function test_page_was_found(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('https://wordpress.test.clarku.edu/polypeet/the-polyporales-post/www.mycologia.org/content/early/2012/09/06/12-088.abstract')
                ->assertSee('Not Found');
        });
    }

    public function test_page_fatal_error(): void
    {
        WordPressTestBrokenPage::query()
            ->where('error', 'LIKE', '500 %')
            ->each(function ($page){
                if (stripos($page->page_url, '.pdf') !== false) {
                    return;
                }
                if (stripos($page->page_url, '.jpg') !== false) {
                    return;
                }
                if (stripos($page->page_url, '.png') !== false) {
                    return;
                }
                var_dump($page->page_url);
                $this->browse(function (Browser $browser) use ($page) {
                    $browser->visit($page->page_url)
                        ->assertDontSee('critical error');
                });
         });
    }
}
