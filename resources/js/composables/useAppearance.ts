import type { App, ComputedRef, InjectionKey, Ref } from 'vue';
import { computed, inject, ref } from 'vue';
import { useTheme } from 'vuetify';
import type { Appearance, ResolvedAppearance } from '@/types';

export type { Appearance, ResolvedAppearance };

export type UseAppearanceReturn = {
    appearance: Ref<Appearance>;
    resolvedAppearance: ComputedRef<ResolvedAppearance>;
    updateAppearance: (value: Appearance) => void;
};

const appearances: readonly Appearance[] = ['light', 'dark', 'system'];

export function isAppearance(value: unknown): value is Appearance {
    return appearances.includes(value as Appearance);
}

/** The appearance stored in the `appearance` cookie (HandleAppearance). */
export function initialAppearance(serverValue: unknown): Appearance {
    return isAppearance(serverValue) ? serverValue : 'system';
}

/**
 * The theme both the server and the hydrating client render first. The
 * server cannot see the OS color scheme, so `system` uses the scheme the
 * browser reported last time (the `system_theme` cookie); `AppRoot` then
 * switches to the real appearance after mount.
 */
export function renderedTheme(
    appearance: Appearance,
    systemTheme: unknown,
): ResolvedAppearance {
    if (appearance !== 'system') {
        return appearance;
    }

    return systemTheme === 'dark' ? 'dark' : 'light';
}

const AppearanceKey: InjectionKey<Ref<Appearance>> = Symbol('appearance');

/** Called once per app in app.ts with the server's appearance. */
export function provideAppearance(app: App, value: Appearance): void {
    app.provide(AppearanceKey, ref(value));
}

function setCookie(name: string, value: string, days = 365): void {
    if (typeof document === 'undefined') {
        return;
    }

    document.cookie = `${name}=${value};path=/;max-age=${days * 24 * 60 * 60};SameSite=Lax`;
}

export function useAppearance(): UseAppearanceReturn {
    const theme = useTheme();
    const appearance = inject(AppearanceKey, ref<Appearance>('system'));

    const resolvedAppearance = computed<ResolvedAppearance>(() =>
        theme.current.value.dark ? 'dark' : 'light',
    );

    function updateAppearance(value: Appearance): void {
        appearance.value = value;
        // The cookie is the single source of truth: the server renders with it.
        setCookie('appearance', value);
        void theme.change(value);
    }

    return { appearance, resolvedAppearance, updateAppearance };
}
