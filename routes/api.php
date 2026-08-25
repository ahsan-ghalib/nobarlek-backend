<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\CompetitionSeasonController;
use App\Http\Controllers\Dashboard\Blogs\BlogController as DashboardBlogController;
use App\Http\Controllers\Dashboard\Blogs\BlogSeoController;
use App\Http\Controllers\Dashboard\Blogs\SearchScheduleMatchesController;
use App\Http\Controllers\Dashboard\Competitions\CompetitionController as DashboardCompetitionController;
use App\Http\Controllers\Dashboard\Competitions\CompetitionImageController as DashboardCompetitionImageController;
use App\Http\Controllers\Dashboard\Competitions\CompetitionSeoController as DashboardCompetitionSeoController;
use App\Http\Controllers\Dashboard\Footer\FooterSettingsController;
use App\Http\Controllers\Dashboard\FooterMatchLinks\FooterMatchLinkController as DashboardFooterMatchLinkController;
use App\Http\Controllers\Dashboard\Home\HomeSeoController;
use App\Http\Controllers\Dashboard\Matches\FootballMatchController as DashboardFootballMatchController;
use App\Http\Controllers\Dashboard\Matches\FootballMatchSeoController as DashboardFootballMatchSeoController;
use App\Http\Controllers\Dashboard\News\NewsController as DashboardNewsController;
use App\Http\Controllers\Dashboard\News\NewsSeoController;
use App\Http\Controllers\Dashboard\Players\PlayerAboutController as DashboardPlayerAboutController;
use App\Http\Controllers\Dashboard\Players\PlayerController as DashboardPlayerController;
use App\Http\Controllers\Dashboard\Players\PlayerImageController as DashboardPlayerImageController;
use App\Http\Controllers\Dashboard\Players\PlayerSeoController as DashboardPlayerSeoController;
use App\Http\Controllers\Dashboard\Profile\ProfileController;
use App\Http\Controllers\Dashboard\Teams\TeamAboutController as DashboardTeamAboutController;
use App\Http\Controllers\Dashboard\Teams\TeamController as DashboardTeamController;
use App\Http\Controllers\Dashboard\Teams\TeamImageController as DashboardTeamImageController;
use App\Http\Controllers\Dashboard\Teams\TeamSeoController as DashboardTeamSeoController;
use App\Http\Controllers\Dashboard\Users\PermissionController as DashboardPermissionController;
use App\Http\Controllers\Dashboard\Users\UserController as DashboardUserController;
use App\Http\Controllers\Football\CategoryController;
use App\Http\Controllers\Football\Competitions\CompetitionChampionController;
use App\Http\Controllers\Football\Competitions\CompetitionController;
use App\Http\Controllers\Football\Competitions\CompetitionNewsController;
use App\Http\Controllers\Football\Competitions\CompetitionOverviewController;
use App\Http\Controllers\Football\Competitions\CompetitionPageBootstrapController;
use App\Http\Controllers\Football\Competitions\CompetitionSchedulesController;
use App\Http\Controllers\Football\Competitions\CompetitionSeasonResultsController;
use App\Http\Controllers\Football\Competitions\CompetitionStandingController;
use App\Http\Controllers\Football\Competitions\CompetitionStatisticController;
use App\Http\Controllers\Football\Competitions\PopularCompetitionController;
use App\Http\Controllers\Football\CountryController;
use App\Http\Controllers\Football\FavoriteController;
use App\Http\Controllers\Football\Matches\CompetitionFootballMatchController;
use App\Http\Controllers\Football\Matches\FootballMatchController;
use App\Http\Controllers\Football\Matches\HeadToHeadController;
use App\Http\Controllers\Football\Matches\MatchChatController;
use App\Http\Controllers\Football\Matches\MatchLineUpController;
use App\Http\Controllers\Football\Matches\MatchOddsController;
use App\Http\Controllers\Football\Matches\MatchOverviewController;
use App\Http\Controllers\Football\Matches\MatchOverviewOddsController;
use App\Http\Controllers\Football\Matches\MatchStandingController;
use App\Http\Controllers\Football\Matches\MatchStreamsController;
use App\Http\Controllers\Football\Players\PlayerHonorController;
use App\Http\Controllers\Football\Players\PlayerInjuriesController;
use App\Http\Controllers\Football\Players\PlayerMatchesController;
use App\Http\Controllers\Football\Players\PlayerNewsController;
use App\Http\Controllers\Football\Players\PlayerOverviewController;
use App\Http\Controllers\Football\Players\PlayerSalaryController;
use App\Http\Controllers\Football\Players\PlayerStatisticController;
use App\Http\Controllers\Football\Players\PlayerStatisticsCompetitionController;
use App\Http\Controllers\Football\Players\PlayerTransferController;
use App\Http\Controllers\Football\Players\SeasonPlayerStatisticController;
use App\Http\Controllers\Football\SearchController;
use App\Http\Controllers\Football\Teams\TeamController;
use App\Http\Controllers\Football\Teams\TeamHonorController;
use App\Http\Controllers\Football\Teams\TeamInjuriesController;
use App\Http\Controllers\Football\Teams\TeamMatchController;
use App\Http\Controllers\Football\Teams\TeamMatchResultController;
use App\Http\Controllers\Football\Teams\TeamNewsController;
use App\Http\Controllers\Football\Teams\TeamOverviewController;
use App\Http\Controllers\Football\Teams\TeamSalaryController;
use App\Http\Controllers\Football\Teams\TeamScheduleController;
use App\Http\Controllers\Football\Teams\TeamScheduleStatusController;
use App\Http\Controllers\Football\Teams\TeamSquadController;
use App\Http\Controllers\Football\Teams\TeamStatisticsController;
use App\Http\Controllers\Football\Teams\TeamTopScorerController;
use App\Http\Controllers\Football\Teams\TeamTransferController;
use App\Http\Controllers\FooterController;
use App\Http\Controllers\FooterMatchLinkController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\Seo\HomeSeoController as PublicHomeSeoController;
use App\Http\Controllers\SiteSettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

require __DIR__.'/auth.php';

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

//Route::get('test', fn() => (new \App\Services\DataScrapping\TeamInjurycrapingService())->scrapTeamInjuryData(['page' => 1]));
Route::get('/timezones', function () {
    $timezones = timezone_identifiers_list();

    return response()->json($timezones);
});

Route::apiResource('news', NewsController::class)->only(['index', 'show']);
Route::apiResource('blogs', BlogController::class)->only(['index', 'show']);
Route::get('footer-match-links', FooterMatchLinkController::class);
Route::get('site-settings', SiteSettingController::class);

Route::middleware(['scraping.api.scope'])->group(function () {
    Route::get('footer', FooterController::class);
    Route::get('seo/home', PublicHomeSeoController::class);
    Route::get('countries', CountryController::class);
    Route::post('search', SearchController::class);

    Route::controller(FavoriteController::class)
        ->middleware(['auth:sanctum'])
        ->group(function () {
            Route::get('favorites', 'index');
            Route::post('favorites', 'store');
            Route::post('un-favorite', 'destroy');
        });

    Route::prefix('football')->group(function () {
        Route::get('categories', CategoryController::class);

        Route::prefix('competitions')->group(function () {
            Route::get('/popular', PopularCompetitionController::class);
            Route::get('/{competition}/page-bootstrap', CompetitionPageBootstrapController::class);
            Route::get('/{date?}', CompetitionController::class);
            Route::get('/{competition}/overview/{season?}', CompetitionOverviewController::class);
            Route::get('/{competition}/results/{season?}', CompetitionSeasonResultsController::class);
            Route::controller(CompetitionStandingController::class)
                ->prefix('/{competition}/standings')
                ->group(function () {
                    Route::get('/teams/{season?}', 'teamStandings');
                    Route::get('/full/{season?}', 'fullTeamStandings');
                    Route::get('/player/{season?}', 'playerStandings');
                });
            Route::post('{competition}/schedules/{season?}', CompetitionSchedulesController::class);
            Route::get('{competition}/statistics/{statsTypeEnum}/{column}/{season}', CompetitionStatisticController::class);
            Route::get('{competition}/honors', CompetitionChampionController::class);
            Route::get('{competition}/news', CompetitionNewsController::class);
            Route::get('{competition}/seasons', CompetitionSeasonController::class);
        });

        Route::prefix('matches')->group(function () {
            Route::post('/', FootballMatchController::class);
            Route::get('/streams', MatchStreamsController::class);
            Route::get('/{footballMatch}/overview', MatchOverviewController::class);
            Route::get('{footballMatch}/head-to-head', HeadToHeadController::class);
            Route::controller(HeadToHeadController::class)->prefix('{footballMatch}/head-to-head')->group(function () {
                Route::get('/home-h2h', 'homeH2h');
                Route::get('/home', 'home');
                Route::get('/away', 'away');
            });
            Route::get('{footballMatch}/lineups', MatchLineUpController::class);
            Route::get('{footballMatch}/standings', MatchStandingController::class);
            Route::controller(MatchChatController::class)->group(function () {
                Route::get('{footballMatch}/chats', 'index');
                Route::post('{footballMatch}/chats', 'store')->middleware(['auth:sanctum']);
            });

            Route::get('/{footballMatch}/overview/odds/{companyId}', MatchOverviewOddsController::class);
            Route::controller(MatchOddsController::class)
                ->prefix('{footballMatch}/odds')
                ->group(function () {
                    Route::get('/', 'index');
                    Route::get('{companyId}/{type}', 'show');
                });

            Route::get('/{competition}/competition/by-date/{matchDate}', CompetitionFootballMatchController::class)
                ->where('matchDate', '[0-9]{4}-[0-9]{2}-[0-9]{2}');
            Route::get('/{competition}/competition/{matchDate}', CompetitionFootballMatchController::class)
                ->where('matchDate', '[0-9]{4}-[0-9]{2}-[0-9]{2}');
            Route::get('/{competition}/competition', CompetitionFootballMatchController::class);
        });

        Route::prefix('teams')->group(function () {
            Route::get('/', [TeamController::class, 'index']);
            Route::get('/{team}/overview', TeamOverviewController::class);
            Route::get('/{team}/matches/{date?}', TeamMatchController::class);
            Route::get('{team}/schedules/{date?}', TeamScheduleController::class);
            Route::get('{team}/results/{date?}', TeamMatchResultController::class);
            Route::get('{team}/schedules/{statusEnum}/status', TeamScheduleStatusController::class);
            Route::get('{team}/salaries', TeamSalaryController::class);
            Route::get('{team}/top-scorer', TeamTopScorerController::class);
            Route::get('{team}/squad', TeamSquadController::class);
            Route::get('{team}/transfer/{year?}', TeamTransferController::class);
            Route::get('{team}/honors', TeamHonorController::class);
            Route::get('{team}/statistics', TeamStatisticsController::class);
            Route::get('{team}/injuries', TeamInjuriesController::class);
            Route::get('{team}/news', TeamNewsController::class);
        });

        Route::prefix('players')->group(function () {
            Route::get('/{player}/overview', PlayerOverviewController::class);
            Route::get('/{player}/matches', PlayerMatchesController::class);
            Route::get('{player}/salary', PlayerSalaryController::class);
            Route::get('{player}/transfers', PlayerTransferController::class);
            Route::get('{player}/statistics/competitions', PlayerStatisticsCompetitionController::class);
            Route::get('{player}/seasons/statistics', SeasonPlayerStatisticController::class);
            Route::get('{player}/statistics/{season}/season', PlayerStatisticController::class);
            Route::get('{player}/honors', PlayerHonorController::class);
            Route::get('{player}/injuries', PlayerInjuriesController::class);
            Route::get('{player}/news', PlayerNewsController::class);
        });
    });
});

Route::prefix('/dashboard')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        // Homepage SEO
        Route::get('home/seo', [HomeSeoController::class, 'show']);
        Route::put('home/seo/update', [HomeSeoController::class, 'update']);

        // Profile
        Route::get('profile', [ProfileController::class, 'show']);
        Route::put('profile', [ProfileController::class, 'update']);
        Route::put('profile/password', [ProfileController::class, 'updatePassword']);

        Route::get('permissions', DashboardPermissionController::class);
        Route::apiResource('users', DashboardUserController::class);

        Route::get('footer/settings', [FooterSettingsController::class, 'show']);
        Route::post('footer/settings/update', [FooterSettingsController::class, 'update']);

        Route::apiResource('footer-match-links', DashboardFooterMatchLinkController::class);

        Route::apiResource('news', DashboardNewsController::class);
        Route::controller(DashboardNewsController::class)
            ->prefix('/news/{news}')
            ->group(function () {
                Route::get('/is-top/{isTop}', 'isTop');
                Route::get('/is-published/{isPublished}', 'isPublished');
            });
        Route::put('news/{news}/seo/update', NewsSeoController::class);

        // Football entities SEO & image update
        Route::apiResource('teams', DashboardTeamController::class)->only(['index', 'show']);
        Route::put('teams/{team}/seo/update', DashboardTeamSeoController::class);
        Route::put('teams/{team}/about/update', DashboardTeamAboutController::class);
        Route::post('teams/{team}/image/update', DashboardTeamImageController::class);

        Route::apiResource('players', DashboardPlayerController::class)->only(['index', 'show']);
        Route::put('players/{player}/seo/update', DashboardPlayerSeoController::class);
        Route::put('players/{player}/about/update', DashboardPlayerAboutController::class);
        Route::post('players/{player}/image/update', DashboardPlayerImageController::class);

        Route::apiResource('competitions', DashboardCompetitionController::class)->only(['index', 'show']);
        Route::put('competitions/{competition}/seo/update', DashboardCompetitionSeoController::class);
        Route::post('competitions/{competition}/image/update', DashboardCompetitionImageController::class);

        Route::apiResource('matches', DashboardFootballMatchController::class)->only(['index', 'show']);
        Route::put('matches/{footballMatch}/seo/update', DashboardFootballMatchSeoController::class);

        // Blogs
        Route::apiResource('blogs', DashboardBlogController::class);
        Route::prefix('blogs')
            ->group(function () {
                Route::post('schedules-matches/search', SearchScheduleMatchesController::class);
                Route::get('{blog}/is-published/{isPublished}', [DashboardBlogController::class, 'isPublished']);
                Route::put('{blog}/seo/update', BlogSeoController::class);
            });
    });
