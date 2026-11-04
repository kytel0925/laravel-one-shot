import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { defineConfig, lazyPlugins } from 'vite-plus';

export default defineConfig({
    plugins: lazyPlugins(() => [
        laravel({
            input: ['resources/js/app.ts'],
            refresh: true,
            fonts: [
                bunny('Roboto', {
                    weights: [400, 500, 700],
                }),
                bunny('Roboto Mono', {
                    weights: [400, 500],
                }),
            ],
        }),
        inertia(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        wayfinder({
            formVariants: true,
        }),
    ]),
    // Vuetify ships CSS imports inside its ES modules; bundle it for SSR so
    // Node never tries to import a .css file.
    ssr: {
        noExternal: ['vuetify'],
    },
    server: {
        // In Docker the dev server listens on 0.0.0.0; browsers must load it
        // through the published host name instead (docker-compose.yml).
        hmr: process.env.VITE_HMR_HOST
            ? {
                  host: process.env.VITE_HMR_HOST,
                  clientPort: Number(process.env.VITE_PORT ?? 5173),
              }
            : undefined,
        watch: {
            ignored: [
                '**/.agents/**',
                '**/.claude/**',
                '**/.cursor/**',
                '**/.junie/**',
                '**/vendor/**',
            ],
        },
    },
    test: {
        // Pure TypeScript helpers only; pages and components are covered by
        // the Pest browser tests. Keeps vendor/ specs out of the run.
        include: ['resources/js/**/*.test.ts'],
    },
    lint: {
        ignorePatterns: [
            'vendor/**',
            'node_modules/**',
            'public/**',
            'bootstrap/ssr/**',
            'resources/js/actions/**',
            'resources/js/routes/**',
            'resources/js/wayfinder/**',
        ],
        options: {
            denyWarnings: true,
            typeAware: true,
        },
    },
    fmt: {
        printWidth: 80,
        tabWidth: 4,
        singleQuote: true,
        semi: true,
        singleAttributePerLine: false,
        htmlWhitespaceSensitivity: 'css',
        ignorePatterns: [
            '.github/**',
            'composer.json',
            'resources/views/mail/*',
        ],
    },
});
