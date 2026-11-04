import '@mdi/font/css/materialdesignicons.css';
import 'vuetify/styles';
import '../css/app.css';
import { createInertiaApp } from '@inertiajs/vue3';
import { createVuetify } from 'vuetify';
import { md3 } from 'vuetify/blueprints';
import {
    VAlert,
    VApp,
    VAppBar,
    VAppBarNavIcon,
    VAppBarTitle,
    VAvatar,
    VBottomNavigation,
    VBreadcrumbs,
    VBreadcrumbsDivider,
    VBreadcrumbsItem,
    VBtn,
    VBtnToggle,
    VCard,
    VCardActions,
    VCardItem,
    VCardSubtitle,
    VCardText,
    VCardTitle,
    VCheckbox,
    VChip,
    VCol,
    VDialog,
    VDivider,
    VEmptyState,
    VIcon,
    VImg,
    VList,
    VListItem,
    VListItemSubtitle,
    VListItemTitle,
    VListSubheader,
    VMain,
    VMenu,
    VNavigationDrawer,
    VOtpInput,
    VProgressCircular,
    VProgressLinear,
    VRow,
    VSheet,
    VSkeletonLoader,
    VSnackbarQueue,
    VSpacer,
    VTextField,
    VToolbar,
    VToolbarTitle,
    VTooltip,
} from 'vuetify/components';
import * as directives from 'vuetify/directives';
import { en } from 'vuetify/locale';
import AppRoot from '@/components/AppRoot.vue';
import {
    initialAppearance,
    renderedTheme,
    syncAppearance,
} from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';
import { darkTheme, lightTheme } from '@/lib/theme';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    // Every page renders inside AppRoot, which provides the one <v-app>.
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
                return AppRoot;
            case name.startsWith('auth/'):
                return [AppRoot, AuthLayout];
            case name.startsWith('settings/'):
                return [AppRoot, AppLayout, SettingsLayout];
            default:
                return [AppRoot, AppLayout];
        }
    },
    withApp: (app, { page }) => {
        const appearance = initialAppearance(page.props.appearance);
        syncAppearance(appearance);

        app.use(
            createVuetify({
                // Server and hydrating client start from the same window size
                // (the `viewport` cookie); Vuetify measures the real one after mount.
                ssr: {
                    clientWidth: page.props.viewport.width,
                    clientHeight: page.props.viewport.height,
                },
                blueprint: md3,
                components: {
                    VAlert,
                    VApp,
                    VAppBar,
                    VAppBarNavIcon,
                    VAppBarTitle,
                    VAvatar,
                    VBottomNavigation,
                    VBreadcrumbs,
                    VBreadcrumbsDivider,
                    VBreadcrumbsItem,
                    VBtn,
                    VBtnToggle,
                    VCard,
                    VCardActions,
                    VCardItem,
                    VCardSubtitle,
                    VCardText,
                    VCardTitle,
                    VCheckbox,
                    VChip,
                    VCol,
                    VDialog,
                    VDivider,
                    VEmptyState,
                    VIcon,
                    VImg,
                    VList,
                    VListItem,
                    VListItemSubtitle,
                    VListItemTitle,
                    VListSubheader,
                    VMain,
                    VMenu,
                    VNavigationDrawer,
                    VOtpInput,
                    VProgressCircular,
                    VProgressLinear,
                    VRow,
                    VSheet,
                    VSkeletonLoader,
                    VSnackbarQueue,
                    VSpacer,
                    VTextField,
                    VToolbar,
                    VToolbarTitle,
                    VTooltip,
                },
                directives,
                locale: { locale: 'en', messages: { en } },
                // Material Design 3 window size classes.
                display: {
                    mobileBreakpoint: 'md',
                    thresholds: {
                        xs: 0,
                        sm: 600,
                        md: 960,
                        lg: 1280,
                        xl: 1920,
                        xxl: 2560,
                    },
                },
                theme: {
                    defaultTheme: renderedTheme(
                        appearance,
                        page.props.systemTheme,
                    ),
                    themes: { light: lightTheme, dark: darkTheme },
                },
                defaults: {
                    VTextField: { variant: 'outlined', density: 'comfortable' },
                    VAlert: { variant: 'tonal', density: 'comfortable' },
                },
            }),
        );

        app.directive('focus', {
            mounted: (el: HTMLElement, shouldFocus) => {
                if (shouldFocus.value === false) {
                    return;
                }

                // Vuetify fields wrap the native input; focus that input.
                const target =
                    el instanceof HTMLInputElement
                        ? el
                        : el.querySelector<HTMLInputElement>('input, textarea');

                target?.focus();
            },
        });
    },
    progress: {
        color: '#6750A4',
    },
});

// Shows Inertia::flash('toast', ...) messages from the server.
initializeFlashToast();
