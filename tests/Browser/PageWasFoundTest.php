<?php

namespace Tests\Browser;

use App\Models\DevBrokenPage;
use App\Models\TestingBrokenPage;
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
}
