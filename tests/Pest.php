<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\ExpectationFailedException;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Feature and Browser tests boot the application and refresh the `testing`
| PostgreSQL database (phpunit.xml), including aggregate subfolders such as
| tests/Feature/{Aggregate}/Features. Unit tests stay framework-free.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Browser');

/*
|--------------------------------------------------------------------------
| Browser testing
|--------------------------------------------------------------------------
|
| Pest's browser plugin drives Playwright's Chromium against an in-process
| server, so pages read the same test database as the test itself.
|
*/

pest()->browser()->timeout(10_000);

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

/**
 * Waits up to five seconds for text to become visible. Pest's assertSee()
 * (and waitForText()) check once, but an Inertia form submit updates the
 * page over XHR without a navigation to wait for. The page's wait() keeps
 * the in-process server answering requests, unlike sleep().
 */
function seeEventually(mixed $page, string $text): mixed
{
    for ($attempt = 1; ; $attempt++) {
        try {
            return $page->assertSee($text);
        } catch (ExpectationFailedException $exception) {
            if ($attempt === 20) {
                throw $exception;
            }

            $page->wait(0.25);
        }
    }
}
