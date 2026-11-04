<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-appearance="{{ $appearance ?? 'system' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Paint the theme background before Vuetify boots, so a dark appearance does not flash white,
             and remember what the server cannot see (OS color scheme, window size) for SSR. --}}
        <script>
            (function () {
                const appearance = '{{ $appearance ?? "system" }}';
                const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const dark = appearance === 'dark' || (appearance === 'system' && systemDark);

                // Lets the server render the `system` appearance in the OS color scheme next time.
                document.cookie = 'system_theme=' + (systemDark ? 'dark' : 'light') + ';path=/;max-age=31536000;SameSite=Lax';
                // Lets the server render the layout for this window width (Vuetify's useDisplay()).
                document.cookie = 'viewport=' + window.innerWidth + 'x' + window.innerHeight + ';path=/;max-age=31536000;SameSite=Lax';

                document.documentElement.dataset.theme = dark ? 'dark' : 'light';
            })();
        </script>

        {{-- Background roles from resources/js/lib/theme.ts (light / dark). --}}
        <style>
            html {
                background-color: #FEF7FF;
            }

            html[data-theme="dark"] {
                background-color: #141218;
                color-scheme: dark;
            }
        </style>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body>
        <x-inertia::app />
    </body>
</html>
