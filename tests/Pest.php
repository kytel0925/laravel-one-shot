<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
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
