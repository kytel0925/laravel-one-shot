# Frontend (`resources/js/`)

Full rules: [Inertia, Vue, and Vuetify](../../resources/docs/agents/frontend.md).

- Pages live in `pages/`; the Inertia component name is the path without
  `.vue`. Layouts are chosen by page name in `app.ts`: `Welcome` uses
  `AppRoot` only, `auth/*` uses `AuthLayout`, `settings/*` uses `AppLayout`
  with the settings layout, everything else uses `AppLayout`. `/` renders the
  `Welcome` page; the `auth` middleware sends guests who open a signed-in page
  to login. The signed-in shell (`AppLayout`) is described in
  [App shell](../../resources/docs/agents/frontend.md#app-shell).
- Auth pages pass `AuthLayout` its props with `defineOptions({ layout: {...} })`,
  or `layout: (pageProps) => ({...})` when a value comes from page props:
  `title`, `description`, optional `icon`, `switchLink` and `footer`
  (`AuthLink` from `types/auth.ts`, Wayfinder `href`), and `back` (defaults to
  home). The page slot holds the tonal alerts first and the full-width primary
  button last.
- Shared components go directly in `components/` (PascalCase). Components for
  one aggregate go in `components/{aggregates}/`.
- Composables are `composables/useX.ts`; helpers go in `lib/` with a colocated
  `*.test.ts`; types go in `types/` and are re-exported from `types/index.ts`.
  Keep `types/global.d.ts` in sync with `HandleInertiaRequests::share()`
  (shared props, including the SSR cookies).
- Use `<script setup lang="ts">`, typed props, and the `@/` alias.
- Vuetify components are registered globally in `app.ts`; import any other
  component locally from `vuetify/components` or add it to that list. Use theme
  colors and `mdi-*` icons.
- Call the backend only through Wayfinder helpers from `@/routes/` or
  `@/actions/`. Run `npm run build` to regenerate them; never edit them.
- Check with `npm run check`, `npm run types:check`, and `npm run test`.
