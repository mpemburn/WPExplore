<?php

namespace Tests\Browser;

use App\Models\DevBrokenPage;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class NoFatalErrorTest extends DuskTestCase
{
    const ERRORS_FOUND = [
        'https://www.testing.clarku.edu/critical-data-studies/collaborative-glossary/data-colonialism.php',
        'https://www.testing.clarku.edu/report/authors/report.authors.php',
        'https://www.testing.clarku.edu/sharer/sharer.php',
        'https://www.testing.clarku.edu/sysadmin/login.php',
        'http://www.testing.clarku.edu/users/login.php',
        'https://www.testing.clarku.edu/course/view.php',
        'http://www.testing.clarku.edu/web/index.php',
        'http://www.testing.clarku.edu/s/t-facts.php',
        'https://www.testing.clarku.edu/medicine/about/student-resources/financial-aid/international-student-info.php',
        'http://www.testing.clarku.edu/Admissions/mdadmissionsrequirements.php',
        'http://www.testing.clarku.edu/com/admissions/international.php',
    ];

    public function test_page_fatal_error(): void
    {
        //DevBrokenPage::query()->where('error', 'LIKE', '%500%')
        collect(self::ERRORS_FOUND)
            ->each(function ($page) {
                if (preg_match('/.*\.(?:jpe?g|png|gif|pdf)(?:\?\S+)?$/', $page)) {
                    return;
                }
                echo 'Testing: ' . $page . PHP_EOL;
                $this->browse(function (Browser $browser) use ($page) {
                    $browser->visit($page)
                        ->assertSee('Internal Server Error');
                });
            });
    }

}
