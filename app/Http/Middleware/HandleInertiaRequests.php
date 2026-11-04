<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'appearance' => in_array($request->cookie('appearance'), ['light', 'dark', 'system'], true)
                ? $request->cookie('appearance')
                : 'system',
            'systemTheme' => $request->cookie('system_theme') === 'dark' ? 'dark' : 'light',
            'viewport' => $this->viewport($request),
        ];
    }

    /**
     * The browser window size reported by the `viewport` cookie, so server
     * rendering picks the same Vuetify breakpoints as the browser. Defaults
     * to a desktop window on the first visit.
     *
     * @return array{width: int, height: int}
     */
    private function viewport(Request $request): array
    {
        if (preg_match('/^(\d{2,5})x(\d{2,5})$/', (string) $request->cookie('viewport'), $matches) === 1) {
            return ['width' => (int) $matches[1], 'height' => (int) $matches[2]];
        }

        return ['width' => 1280, 'height' => 800];
    }
}
