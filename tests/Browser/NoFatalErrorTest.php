<?php

namespace Tests\Browser;

use App\Models\DevBrokenPage;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class NoFatalErrorTest extends DuskTestCase
{
    const ERRORS_FOUND = [
        'https://www.clarku.edu/critical-data-studies/collaborative-glossary/data-colonialism.php',
        'https://www.clarku.edu/report/authors/report.authors.php',
        'https://www.clarku.edu/sharer/sharer.php',
        'https://www.clarku.edu/sysadmin/login.php',
        'http://www.clarku.edu/users/login.php',
        'https://www.clarku.edu/course/view.php',
        'http://www.clarku.edu/web/index.php',
        'http://www.clarku.edu/s/t-facts.php',
        //'https://www.clarku.edu/clark-together/video-categories/',
        'https://www.clarku.edu/medicine/about/student-resources/financial-aid/international-student-info.php',
        'http://www.clarku.edu/Admissions/mdadmissionsrequirements.php',
        'http://www.clarku.edu/com/admissions/international.php',
        //'https://www.clarku.edu/admissions/graduate-admissions/nyu-grad-admissions-blog/international-students/navigating-the-job-search-as-an-international-student.html',
        //'https://www.clarku.edu/contact-us/',
        //'https://www.clarku.edu/career-experience/internships-volunteer-work-and-employment/',
        //'https://www.clarku.edu/life-at-clark/meet-our-student-life-team/',
        'https://www.clarku.edu/undergraduate-admissions/apply/international-students/',
    ];

    public function test_page_fatal_error(): void
    {
        collect(self::ERRORS_FOUND)
            ->each(function ($page) {
                if (preg_match('/.*\.(?:jpe?g|png|gif|pdf)(?:\?\S+)?$/', $page)) {
                    return;
                }
                echo 'Testing: ' . $page . PHP_EOL;
                $this->browse(function (Browser $browser) use ($page) {
                    $browser->visit($page)
                        ->assertSee('Internal Server Error');
//                    $browser->visit($page)
//                        ->assertDontSee('Non-existent changeset UUID.');
                });
            });
    }

}
