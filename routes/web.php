<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';

// Each aggregate under src/ registers its own routes (prefix, name prefix and
// middleware) in src/{Aggregate}/routes.php, inside the `web` group.
foreach (glob(base_path('src/*/routes.php')) ?: [] as $aggregateRoutes) {
    require $aggregateRoutes;
}
