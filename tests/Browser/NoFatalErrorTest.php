<?php

namespace Tests\Browser;

use App\Models\DevBrokenPage;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class NoFatalErrorTest extends DuskTestCase
{
    public function test_page_fatal_error(): void
    {
        DevBrokenPage::query()->where('error', 'LIKE', '%500%')
            ->each(function ($page) {
                if (preg_match('/.*\.(?:jpe?g|png|gif|pdf)(?:\?\S+)?$/', $page->page_url)) {
                    return;
                }
                var_dump($page->page_url);
                $this->browse(function (Browser $browser) use ($page) {
                    $browser->visit($page->page_url)
                        ->assertDontSee('critical error');
                    $browser->visit($page->page_url)
                        ->assertDontSee('Non-existent changeset UUID.');
                });
            });
    }

}
