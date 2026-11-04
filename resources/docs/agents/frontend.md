# Inertia, Vue, and Vuetify

Directory layout and file placement are in
[resources/js/AGENTS.md](../../../resources/js/AGENTS.md).

## Inertia

- The Inertia component name is the page path under `resources/js/pages/`
  without `.vue` (`auth/Login`, `settings/Profile`).
- Layout selection by page name and the `AuthLayout` props are in
  [resources/js/AGENTS.md](../../../resources/js/AGENTS.md).
- `components/AppRoot.vue` provides the single `<v-app>` and the
  `<v-snackbar-queue>` that shows flash toasts (`lib/flashToast.ts`, sent by
  the server with `Inertia::flash('toast', ...)`). The snackbar is the MD3
  inverse surface with an inverse-primary action and a leading icon per toast
  type (`success`, `info`, `warning`, `error`).
- Shared Inertia props are typed in `resources/js/types/global.d.ts` (see
  [resources/js/AGENTS.md](../../../resources/js/AGENTS.md)).

## Backend calls

- Use Wayfinder functions from `@/routes/` (named routes) or `@/actions/`
  (controllers) for every link, form, and request. `vite.config.ts` enables
  form variants.
- `resources/js/actions/`, `routes/`, and `wayfinder/` are generated and
  ignored by Git. Regenerate them with `npm run build` or the dev server;
  never edit them.
- A missing helper or a "Unable to locate file in Vite manifest" error means
  the build is stale: run `npm run build`.

## Copy and formatting

- UI copy is English, hard-coded in the components. There is no i18n library.
  Identifiers, `data-test` values, routes, and code comments are English too.
- `<html lang>` comes from the app locale in `resources/views/app.blade.php`;
  every page sets a `<Head title>`. Vuetify's own strings use its `en` locale
  (`app.ts`).
- Messages that come from the server (validation, Fortify, mail, error
  pages) are translated with Laravel's language files (`lang/`) when a
  language is added, not in Vue.

## Vuetify

- `resources/js/app.ts` creates Vuetify (the `md3` blueprint, the globally
  registered components, directives, the `en` locale, display thresholds, the
  `light` and `dark` themes, and defaults for `VTextField` and `VAlert`).
  Import any component that is not in its list locally from
  `vuetify/components` or add it to the list.
- Build new UI from Vuetify components with theme colors and `mdi-*` icons:
  `<v-btn color="primary">`, `<v-icon icon="mdi-check" />`.
- Theme tokens live in `resources/js/lib/theme.ts`: the Material Design 3
  baseline color roles of the `light` and `dark` themes, including the
  `on-surface-container-*` roles. The themes keep these names so Vuetify's
  `system` theme works. Change colors there only.
  Components use theme colors (`color="primary-container"`,
  `rgb(var(--v-theme-surface-container))`), never raw hex values.
- Text uses MD3 role classes or the components' defaults:
  `text-headline-small` for screen titles, `text-title-medium` for card
  titles, `text-body-medium` for body, `text-label-small` for captions, and
  `text-mono` (defined in `resources/css/app.css`) for codes and keys. Never
  set `font-size` or use MD2 classes (`text-h5`, `text-body-2`,
  `text-caption`); Vuetify 4 does not ship them. Spacing uses Vuetify spacing
  utilities or 4 dp multiples. App pages get the `.page-container` (1200 max
  width, side margins 16 / 24 / 32, defined in `resources/css/app.css`) from
  the app layouts, so pages add no `v-container` of their own.
- `useDisplay()` follows the thresholds set in `app.ts` (sm 600, md 960,
  lg 1280, xl 1920, xxl 2560; `mobileBreakpoint: 'md'`). Vuetify's compiled
  CSS utilities (`pa-md-*`, `d-lg-flex`) switch at 600 / 840 / 1145 / 1545 /
  2138 instead. For layout switches at the contract widths, avoid `d-md-*` and
  `v-col md` and write a scoped `@media (min-width: 960px)` rule (as
  `layouts/AuthLayout.vue` and `layouts/settings/Layout.vue` do);
  `useDisplay()` flashes the wrong layout on server-rendered pages.
- A region on a theme surface uses its `bg-<token>` class (`bg-surface`,
  `bg-background`), which also sets the paired `on-<token>` text color; keep
  text on it at 4.5:1 contrast in both themes. Supporting text uses
  `text-medium-emphasis`, whose opacity `lib/theme.ts` raises to 0.72 (theme
  `variables`) so it stays above 4.5:1; do not lower it.
- Light and dark themes follow the appearance setting through
  `composables/useAppearance.ts` and the `HandleAppearance` middleware. The
  `appearance` cookie is the single source of truth. Change theme behavior
  there, not in pages.
- Check component APIs with the Vuetify MCP server (configured in `.mcp.json`
  and `.codex/config.toml`).

## Server-side rendering

- Inertia SSR runs in development, and `vite.config.ts` bundles Vuetify for it
  (`ssr.noExternal`).
- The server cannot see the OS color scheme or the window size, so the inline
  script in `resources/views/app.blade.php` stores them in the `system_theme`
  and `viewport` cookies (unencrypted; see `bootstrap/app.php`).
  `HandleInertiaRequests` shares `appearance`, `systemTheme`, and `viewport`;
  `app.ts` passes `viewport` to Vuetify's `ssr` option and the rendered theme,
  and `AppRoot` switches to the real appearance after mount.

## App shell

- `layouts/AppLayout.vue` is the signed-in shell. It swaps at md (960) with
  `useDisplay().mdAndUp`, so it changes on resize without a reload: from md up
  the permanent `AppDrawer` (the rail toggle in `AppTopBar` collapses it to a
  rail that hides the labels; the state is stored in the `sidebar_state`
  cookie and arrives as the `sidebarOpen` prop) and `AppTopBar` (rail toggle,
  breadcrumbs, and user menu); below md `AppMobileBar` with a nav icon
  (`data-test="mobile-drawer-toggle"`) that opens the same `AppDrawer` as a
  modal drawer (its `modal` prop). There is no bottom navigation bar: MD3
  asks for 3 to 5 destinations and the template has two. The shell wraps the
  page in the one `.page-container py-6`. The user avatar and menu read
  `auth.user`.
- Shared components live in `components/`: the shell (`AppRoot`, `AppDrawer`,
  `AppTopBar`, `AppMobileBar`, `AppBreadcrumbs`, `AppLogo`,
  `AppLogoIcon`, `UserMenu`, `UserAvatar`), page building blocks
  (`PageHeading`, `SettingsSection` with an `h2` title, `PasswordField` with a
  real icon button in the `append-inner` slot that sets `aria-label` and
  `aria-pressed`, `TextLink`), and the
  two-factor, passkey, appearance, and delete-account components. Dialogs set
  `aria-labelledby` to their title. Check them before writing a new one.
- Destinations live in `composables/useAppNavigation.ts`. An item with
  `href: null` has no feature yet and renders disabled, with no `href`.
- Vuetify sets `aria-current` only for vue-router links, so the shell binds
  `aria-current="page"` on the active item itself. Inertia links render
  Vuetify components with `<Link :as="VListItem">` or `<Link :as="VBtn">`.

## Checks

`npm run check` (Oxlint and Oxfmt through Vite Plus), `npm run types:check`
(vue-tsc), and `npm run test` (frontend unit tests). `npm run build` does not
replace them.
