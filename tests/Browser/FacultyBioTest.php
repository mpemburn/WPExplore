<?php

namespace Tests\Browser;

use App\Models\CfLinks;
use App\Models\DevBrokenPage;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class FacultyBioTest extends DuskTestCase
{
    const ERRORS_FOUND = [
        'https://www2.clarku.edu/faculty/facultybio.cfm?id=256',
    ];

    public function test_page_fatal_error(): void
    {
        $bios = CfLinks::whereNull('redirect')
            ->where('url', 'LIKE', '%facultybio%')
            ->get();
        //DevBrokenPage::query()->where('error', 'LIKE', '%500%')
        $bios->each(function ($cfLink) {
            $url = $cfLink->url;
            echo 'Testing: ' . $url . PHP_EOL;
            $this->browse(function (Browser $browser) use ($url) {
                $browser->visit($url)
                    ->assertSee('Email:');
                echo $browser->element('[href^="mailto:"]')
                        ->getDomProperty('innerHTML') . PHP_EOL;
            });
        });
    }

}
