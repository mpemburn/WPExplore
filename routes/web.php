<?php

use App\Generators\BlogsCsvGenerator;
use App\Generators\PluginsCsvGenerator;
use App\Http\Controllers\BlogCrawlerController;
use App\Models\Blog;
use App\Models\BlogList;
use App\Models\DevBrokenPage;
use App\Models\LogParserCompleted;
use App\Models\Option;
use App\Models\Post;
use App\Models\PostMeta;
use App\Models\TestingBrokenPage;
use App\Models\WordpressProductionLink;
use App\Models\WordPressTestBrokenPage;
use App\Models\WordpressTestLink;
use App\Observers\BugObserver;
use App\Services\BlogCrawlerService;
use App\Services\BlogService;
use App\Services\BugScanService;
use App\Services\CloneService;
use App\Services\DatabaseService;
use App\Services\LogParserService;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use App\Observers\BlogObserver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Spatie\Crawler\Crawler;
use Spatie\Async\Pool;
use Symfony\Component\Process\Process;
use function Sentry\captureException;
use Smalot\PdfParser\Parser;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', fn() => view('welcome'));

Route::get('/csv/active', fn() => (new BlogService())->createActiveBlogsCsv());

Route::get('/csv/stale', fn() => (new BlogService())->createStaleBlogsCsv());

Route::get('/csv/mat', fn() => (new BlogService())->createMatBlogsCsv());

Route::get('/csv/active/date', fn() => (new BlogService())->createBlogsInDateRangeCsv(request('start'), request('end')));

Route::get('/csv/mat/date', fn() => (new BlogService())->createMatBlogsInDateRangeCsv(request('start'), request('end')));

Route::get('/load_blogs', function () {
    $currentSite = request('site');
    DatabaseService::setDb($currentSite . '_clarku');

    $blogs = (new BlogService())->getActiveBlogs();

    DatabaseService::setDb('server_tests');
    DB::purge('mysql');
    $blogs->each(function ($blog) use ($currentSite) {
        BlogList::create([
            'site' => $currentSite,
            'blog_id' => $blog['blog_id'],
            'blog_url' => $blog['siteurl'],
            'last_updated' => $blog['last_updated'],
            'admin_email' => $blog['admin_email'],
            'current_theme' => isset($blog['current_theme']) ? $blog['current_theme'] : '',
            'template' => $blog['template'],
        ]);
    });


});

Route::get('/func', function () {
    class Update
    {
        protected $versions = [
            '20140709',
            '20160831',
            '20170510',
            '20170511',
            '20170711',
            '20171023',
            '20171215',
            '20171219',
            '20190227',
            '20200331',
            '20201217',
            '20210624',
            '20210924',
            '20220222',
            '20220426',
            '20220506',
            '20220818',
            '20221101',
        ];

        public function __construct()
        {
            $needs_updating = false;
            $auth_version = '20220426';

            foreach ($this->versions as $version) {
                if ( false === $auth_version || intval( $auth_version ) < $version ){
                    if (method_exists($this, 'update_' . $version)) {
                        $auth_version = call_user_func_array([$this, 'update_' . $version], [$auth_version, $version]);
                        $needs_updating = true;
                    }
                }
            }
        }

        protected function update_20221101( $auth_version, $version )
        {
            echo 'Hi! ' . $auth_version . '<br>';
            echo 'Yo! ' . $version . '<br>';

            return $version;
        }
    }

    new Update();
});

Route::get('/dev', function () {
    $blogs = [];

    $blogs[0]="79"; // https://wordpress.clarku.edu/mat-mat/
    $blogs[1]="90"; // https://wordpress.clarku.edu/mat-gstultz/
    $blogs[2]="99"; // https://wordpress.clarku.edu/mat-fpalmer/
    $blogs[3]="139"; // https://wordpress.clarku.edu/mat-hdolan/
    $blogs[4]="210"; // https://wordpress.clarku.edu/mat13-jkennelly/
    $blogs[5]="212"; // https://wordpress.clarku.edu/mat13-skorunow/
    $blogs[6]="218"; // https://wordpress.clarku.edu/mat13-emurphy/
    $blogs[7]="223"; // https://wordpress.clarku.edu/mat13-isantner/
    $blogs[8]="224"; // https://wordpress.clarku.edu/mat13-sshepro/
    $blogs[9]="226"; // https://wordpress.clarku.edu/mat13-mstern/
    $blogs[10]="345"; // https://wordpress.clarku.edu/mat15-jzarou
    $blogs[11]="346"; // https://wordpress.clarku.edu/mat15-embaker
    $blogs[12]="348"; // https://wordpress.clarku.edu/mat15-emcross
    $blogs[13]="349"; // https://wordpress.clarku.edu/mat15-zfellows
    $blogs[14]="354"; // https://wordpress.clarku.edu/mat15-nhoey
    $blogs[15]="357"; // https://wordpress.clarku.edu/mat15-skanoon
    $blogs[16]="359"; // https://wordpress.clarku.edu/mat15-rkoerner
    $blogs[17]="361"; // https://wordpress.clarku.edu/mat15-mmuna
    $blogs[18]="363"; // https://wordpress.clarku.edu/mat15-kpinard
    $blogs[19]="366"; // https://wordpress.clarku.edu/mat15-kring
    $blogs[20]="369"; // https://wordpress.clarku.edu/mat15-cspencer
    $blogs[21]="370"; // https://wordpress.clarku.edu/mat15-laspiegler
    $blogs[22]="371"; // https://wordpress.clarku.edu/mat15-hasullivan
    $blogs[23]="373"; // https://wordpress.clarku.edu/mat15-hweinsaft
    $blogs[24]="395"; // https://wordpress.clarku.edu/mat16-handerson
    $blogs[25]="396"; // https://wordpress.clarku.edu/mat16-echen
    $blogs[26]="397"; // https://wordpress.clarku.edu/mat16-hcunningham
    $blogs[27]="398"; // https://wordpress.clarku.edu/mat16-sdonnellan
    $blogs[28]="399"; // https://wordpress.clarku.edu/mat16-grgallagher
    $blogs[29]="400"; // https://wordpress.clarku.edu/mat16-shazelkorn
    $blogs[30]="401"; // https://wordpress.clarku.edu/mat16-lhua
    $blogs[31]="403"; // https://wordpress.clarku.edu/mat16-cojohnson
    $blogs[32]="404"; // https://wordpress.clarku.edu/mat16-tajohnson
    $blogs[33]="405"; // https://wordpress.clarku.edu/mat16-tjung
    $blogs[34]="406"; // https://wordpress.clarku.edu/mat16-mlueke
    $blogs[35]="407"; // https://wordpress.clarku.edu/mat16-mmainuli
    $blogs[36]="408"; // https://wordpress.clarku.edu/mat16-jmanglass
    $blogs[37]="409"; // https://wordpress.clarku.edu/mat16-lmatthew
    $blogs[38]="410"; // https://wordpress.clarku.edu/mat16-enally
    $blogs[39]="411"; // https://wordpress.clarku.edu/mat16-anye
    $blogs[40]="412"; // https://wordpress.clarku.edu/mat16-joliveras
    $blogs[41]="413"; // https://wordpress.clarku.edu/mat16-jolson
    $blogs[42]="414"; // https://wordpress.clarku.edu/mat16-jpacillo
    $blogs[43]="415"; // https://wordpress.clarku.edu/mat16-kpahigian
    $blogs[44]="416"; // https://wordpress.clarku.edu/mat16-nporcella
    $blogs[45]="417"; // https://wordpress.clarku.edu/mat16-strichardson
    $blogs[46]="418"; // https://wordpress.clarku.edu/mat16-crothenberg
    $blogs[47]="419"; // https://wordpress.clarku.edu/mat16-jshepro
    $blogs[48]="420"; // https://wordpress.clarku.edu/mat16-bwilhelms
    $blogs[49]="421"; // https://wordpress.clarku.edu/mat16-kwynja
    $blogs[50]="422"; // https://wordpress.clarku.edu/mat16-cayacino
    $blogs[51]="447"; // https://wordpress.clarku.edu/mat16-bberman
    $blogs[52]="448"; // https://wordpress.clarku.edu/mat16-rblackmer
    $blogs[53]="449"; // https://wordpress.clarku.edu/mat16-jbrien
    $blogs[54]="450"; // https://wordpress.clarku.edu/mat16-scramer
    $blogs[55]="451"; // https://wordpress.clarku.edu/mat16-mdeininger
    $blogs[56]="452"; // https://wordpress.clarku.edu/mat16-vdopke
    $blogs[57]="453"; // https://wordpress.clarku.edu/mat16-repstein
    $blogs[58]="454"; // https://wordpress.clarku.edu/mat16-jofeinberg
    $blogs[59]="455"; // https://wordpress.clarku.edu/mat16-lgaufberg
    $blogs[60]="456"; // https://wordpress.clarku.edu/mat16-stgrabowski
    $blogs[61]="457"; // https://wordpress.clarku.edu/mat16-hhenneberry
    $blogs[62]="458"; // https://wordpress.clarku.edu/mat16-hholway
    $blogs[63]="459"; // https://wordpress.clarku.edu/mat16-nhopley
    $blogs[64]="460"; // https://wordpress.clarku.edu/mat16-chuynen
    $blogs[65]="461"; // https://wordpress.clarku.edu/mat16-ejaskoviak
    $blogs[66]="462"; // https://wordpress.clarku.edu/mat16-jlumsden
    $blogs[67]="463"; // https://wordpress.clarku.edu/mat16-emaclean
    $blogs[68]="464"; // https://wordpress.clarku.edu/mat16-jmerlos
    $blogs[69]="465"; // https://wordpress.clarku.edu/mat16-mamoore
    $blogs[70]="466"; // https://wordpress.clarku.edu/mat16-dostreicher
    $blogs[71]="467"; // https://wordpress.clarku.edu/mat16-caphillips
    $blogs[72]="468"; // https://wordpress.clarku.edu/mat16-dpratt
    $blogs[73]="469"; // https://wordpress.clarku.edu/mat16-kreeser
    $blogs[74]="470"; // https://wordpress.clarku.edu/mat16-rrobichaud
    $blogs[75]="471"; // https://wordpress.clarku.edu/mat16-asqualli
    $blogs[76]="472"; // https://wordpress.clarku.edu/mat16-awalkup
    $blogs[77]="473"; // https://wordpress.clarku.edu/mat16-cwiercimok
    $blogs[78]="474"; // https://wordpress.clarku.edu/mat16-bxiang
    $blogs[79]="475"; // https://wordpress.clarku.edu/mat16-jyorke
    $blogs[80]="476"; // https://wordpress.clarku.edu/mat16-celwell
    $blogs[81]="518"; // https://wordpress.clarku.edu/mat18-eabelson
    $blogs[82]="519"; // https://wordpress.clarku.edu/mat18-abailey
    $blogs[83]="520"; // https://wordpress.clarku.edu/mat18-dbarnes
    $blogs[84]="521"; // https://wordpress.clarku.edu/mat18-jboyar
    $blogs[85]="522"; // https://wordpress.clarku.edu/mat18-mcarleton
    $blogs[86]="523"; // https://wordpress.clarku.edu/mat18-cencarnacionrivera
    $blogs[87]="524"; // https://wordpress.clarku.edu/mat18-aespinoza
    $blogs[88]="525"; // https://wordpress.clarku.edu/mat18-zfishman
    $blogs[89]="526"; // https://wordpress.clarku.edu/mat18-efitzpatrick
    $blogs[90]="527"; // https://wordpress.clarku.edu/mat18-nfriedler
    $blogs[91]="528"; // https://wordpress.clarku.edu/mat18-tgallagher
    $blogs[92]="529"; // https://wordpress.clarku.edu/mat18-jgluck
    $blogs[93]="530"; // https://wordpress.clarku.edu/mat18-agrace
    $blogs[94]="531"; // https://wordpress.clarku.edu/mat18-kgreer
    $blogs[95]="532"; // https://wordpress.clarku.edu/mat18-jkahn
    $blogs[96]="533"; // https://wordpress.clarku.edu/mat18-slapin
    $blogs[97]="534"; // https://wordpress.clarku.edu/mat18-nlew
    $blogs[98]="535"; // https://wordpress.clarku.edu/mat18-jlewitt
    $blogs[99]="537"; // https://wordpress.clarku.edu/mat18-jmusto
    $blogs[100]="538"; // https://wordpress.clarku.edu/mat18-khphan
    $blogs[101]="540"; // https://wordpress.clarku.edu/mat18-treichart
    $blogs[102]="541"; // https://wordpress.clarku.edu/mat18-aschramm
    $blogs[103]="542"; // https://wordpress.clarku.edu/mat18-mascott
    $blogs[104]="543"; // https://wordpress.clarku.edu/mat18-ysternberg
    $blogs[105]="544"; // https://wordpress.clarku.edu/mat18-sestewart
    $blogs[106]="545"; // https://wordpress.clarku.edu/mat18-ethompson
    $blogs[107]="546"; // https://wordpress.clarku.edu/mat18-mtighe
    $blogs[108]="547"; // https://wordpress.clarku.edu/mat18-lwellen
    $blogs[109]="549"; // https://wordpress.clarku.edu/mat18-mwyatt
    $blogs[110]="563"; // https://wordpress.clarku.edu/mat18samplesite
    $blogs[111]="564"; // https://wordpress.clarku.edu/mat19-template
    $blogs[112]="588"; // https://wordpress.clarku.edu/mat19-vingmann
    $blogs[113]="590"; // https://wordpress.clarku.edu/mat19-ejoseph
    $blogs[114]="604"; // https://wordpress.clarku.edu/mat19-ushrestha

    foreach ($blogs as $blog) {

    }
    // Do what thou wilt
});

Route::get('/shortcode', function () {
    $shortCode = $_REQUEST['shortcode'] ?? null;

    if (! $shortCode) {
        echo 'No shortcode specified. Syntax: ' . URL::to('/shortcode') . '?shortcode=';
        return;
    }
    $blogList = (new BlogService())->findShortCodeInPosts($shortCode);

    echo '<div style="font-family: sans-serif">';
    echo '<table>';
    echo '   <tr style="background-color: #e2e8f0;">';
    echo '      <td>';
    echo 'Page';
    echo '      </td>';
    echo '      <td>';
    echo 'Title';
    echo '      </td>';
    echo '      <td>';
    echo 'Created';
    echo '      </td>';
    echo '   </tr>';
    $blogList->each(function ($page) {
        $url = $page['blog_url'] . $page['post_name'];
        echo '   <tr>';
        echo '      <td>';
        echo '<a href="' . $url . '" target="_blank">' . $url . '</a><br>';
        echo '      </td>';
        echo '      <td>';
        echo $page['title'];
        echo '      </td>';
        echo '      <td>';
        echo Carbon::parse($page['date'])->format('F j, Y');
        echo '      </td>';
        echo '   </tr>';
    });
    echo '<div>';
    echo '<table>';

});

Route::get('/parse_log', function () {
    $parser = new LogParserService();
    $parser->setCodePath('/Users/mpemburn/Dev/clarku-wordpress')
        ->setAppPath('dom28151\\')
        ->run('28151_04_24', ['wp-content/themes/clarku'], []);

    return view('logparser', [
        'logPrefix' => $parser->getLogPrefix(),
        'basePath' => $parser->getCodePath(),
        'appPath' => $parser->getAppPath(),
        'data' => $parser->display()
    ]);
});

Route::get('/to_archive', function () {
    $mats = (new BlogService())->getActiveBlogs(['/mat'], '2013/01/10', '2018/12/31');

    $exclude = ['210', '212', '405', '400', '416', '413', '396', '409', '398', '450', '664'];

    $additions = (new BlogService())->getBlogsById(['539', '101', '536', '402', '548']);

    $mats = $mats->merge($additions);

    $count = 0;
    $mats->each(function ($blog) use (&$count, $exclude) {
        if (in_array($blog['blog_id'], $exclude)) {
            return;
        }
        echo "blogs[{$count}]=\"{$blog['blog_id']}\" # {$blog['siteurl']}" . '<br>';

        $count++;
    });
});

Route::get('/where_active', function () {
    $currentSite = request('site');
    $notFoundOnly = request('not_found');

    DatabaseService::setDb($currentSite . '_clarku');

    $rootPath = Storage::path('plugins_json');
    $file =  $rootPath . "/{$currentSite}_plugins.json";

    if (file_exists($file)) {
        $json = file_get_contents($file);
        collect(json_decode($json, true))->each(function ($row) use ($notFoundOnly) {
            if ($row['status'] !== 'inactive') {
                return;
            }
            $pluginName = $row['name'];
            $title = $row['title'];
            echo (new BlogService())->findPluginInSubsite($pluginName, $title, $notFoundOnly);
        });
    }
});

Route::get('/themes', function () {
    $currentSite = request('site');
    $notFoundOnly = request('not_found');

    DatabaseService::setDb($currentSite . '_clarku');

    $rootPath = Storage::path('themes_json');
    $file =  $rootPath . "/{$currentSite}_themes.json";
    if (file_exists($file)) {
        $json = file_get_contents($file);
        collect(json_decode($json, true))->each(function ($row) use ($notFoundOnly) {
            if ($row['status'] !== 'inactive') {
                return;
            }
            $themeName = $row['title'];
            echo (new BlogService())->findThemesInSubsite($themeName, $notFoundOnly);
        });
    }
});

Route::get('/active', function () {
    $currentSite = request('site');
    if ($currentSite) {
        DatabaseService::setDb($currentSite . '_clarku');
    }

    $blogs = (new BlogService())->getActiveBlogs();

    !d($blogs->toArray());
});

Route::get('/sentry', function () {
    \Sentry\init(['dsn' => env('WP_SENTRY_PHP_DSN')]);
    try {
        $this->functionBogus();
    } catch (\Throwable $exception) {
        $result = captureException($exception);

        !d($result);
    }

});

Route::get('/phpdd', function () {
    $pluginPath = "C:/Users/mpemburn/Documents/Dev/www.clarku.edu/wp-content/plugins/";
    $json = file_get_contents('C:/Users/mpemburn/Documents/Sandbox/wpexplore/storage/app/public/PluginJSON/wordfence.json');
    $data = collect(json_decode($json, true));

    !d($data);
});

Route::get('/distill', function () {
    $rootPath = Storage::path('plugins_json');
    $distilled = collect();
    collect(File::allFiles($rootPath))->each(function ($file) use (&$distilled) {
        $info = pathinfo($file);
        $site = str_replace('_plugins', '', $info['filename']);

        $json = file_get_contents($file);
        collect(json_decode($json, true))->each(function ($row) use ($site, &$distilled){
            if ($row['status'] !== 'active' && $row['status'] !== 'active-network') {
                return;
            }
            $plugin = $row['name'];
            if (! $distilled->contains($plugin)) {
                $distilled->push($plugin);
                $sites = collect();
                $sites->push($site);
                $distilled->put($plugin, ['data' => $row, 'sites' => $sites]);
            } else {
                $data = $distilled->get($plugin);
                $data['sites']->push($site);
                $distilled->put($plugin, $data);
            }
        });
    });
    $rows = collect();
    $distilled->each(function ($plugin) use (&$rows) {
        if (is_array($plugin)) {
            $data = $plugin['data'];
            unset($data['version'], $data['status'], $data['update']);
            $data['sites'] = implode(',', $plugin['sites']->sortDesc()->toArray());
            $rows->push($data);
        }
    });
    return (new PluginsCsvGenerator('distilled_plugin_list.csv'))
        ->setData($rows)
        ->run();

});

