# Vuetify 4 with Material Design 3 for the UI

Status: accepted
Date: 2026-11-04

## Context

The Laravel Inertia + Vue starter kit ships Tailwind CSS with shadcn-vue components. Every screen then
needs hand-assembled utility classes, a design language chosen per component, and our own implementation of
theming, accessibility and responsive behavior. We want a complete, consistent design system with ready-made
accessible components, so feature work is composition and not styling.

## Decision

Replace Tailwind CSS and shadcn-vue with Vuetify 4 using its Material Design 3 blueprint:

- `createVuetify({ blueprint: md3, ... })` in `resources/js/app.ts`; only the components in use are
  registered (tree-shaken), icons come from Material Design Icons (`@mdi/font`).
- Colors are MD3 roles, defined once in `resources/js/lib/theme.ts` (`lightTheme`, `darkTheme`, seed
  `#6750A4`). Components use role names (`primary-container`, `surface-container`, `on-surface`), never
  hex values or custom colors. The themes are named `light` and `dark` so the `system` option follows the
  operating system.
- Window size classes follow MD3 (`xs` 0, `sm` 600, `md` 960, `lg` 1280, `xl` 1920, `xxl` 2560) with
  `mobileBreakpoint: 'md'`.
- Server-side rendering has no browser to ask, so small unencrypted cookies carry the missing facts
  (`bootstrap/app.php` excludes them from cookie encryption): `appearance` (light, dark or system),
  `system_theme` (the OS scheme the browser reported last time), `viewport` (`<width>x<height>`) and
  `sidebar_state`. `HandleInertiaRequests::share()` exposes them as the `appearance`, `systemTheme`,
  `viewport` and `sidebarOpen` props; Vuetify's `ssr.clientWidth/clientHeight` and the initial theme are
  derived from them, so server and hydrating client render the same markup.

## Consequences

- Screens are built from Vuetify components and MD3 roles; no Tailwind utility classes or shadcn-vue
  components remain, and new code must not reintroduce them.
- Bundle size: measured with `npm run build` (Vite, `public/hot` moved aside during the build), the entry
  chunk `app-*.js` is about 304 kB (98 kB gzip), plus `wayfinder-*.js` about 217 kB (73 kB gzip), and the
  stylesheet `app-*.css` about 680 kB (103 kB gzip). The Material Design Icons font adds
  `materialdesignicons-webfont` files of 0.4 to 1.3 MB per format, of which browsers load one (woff2,
  403 kB). This is heavier than the Tailwind setup it replaces; the price of a full design system.
- Upgrading Vuetify can change component APIs and theme tokens; the MD3 color roles are the stable contract
  inside the project.
- The first server render of the `system` appearance on a visitor's very first visit uses the light theme
  (no cookie yet), and the client switches to the real scheme after mount, so a brief theme flash is
  possible for first-time visitors with a dark OS scheme.
- Hard dependency on the three cookies: clearing them resets sidebar, appearance and the assumed window
  size (defaults: sidebar open, `system`, 1280x800).
