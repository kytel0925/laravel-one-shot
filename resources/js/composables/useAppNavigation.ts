import type { InertiaLinkProps } from '@inertiajs/vue3';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { dashboard } from '@/routes';
import { edit as editProfile } from '@/routes/profile';
import type { NavItem } from '@/types';

/**
 * Destinations of the signed-in shell: the drawer (md and up) and the bottom
 * navigation (below md). An item with `href: null` has no feature yet and
 * renders disabled.
 */
export const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: 'mdi-view-dashboard-outline',
    },
    {
        title: 'Settings',
        href: editProfile(),
        icon: 'mdi-cog-outline',
        activePrefix: '/settings',
    },
];

export type UseAppNavigationReturn = {
    items: NavItem[];
    isActive: (item: NavItem) => boolean;
};

export function useAppNavigation(): UseAppNavigationReturn {
    const { isCurrentUrl, isCurrentOrParentUrl } = useCurrentUrl();

    function isActive(item: NavItem): boolean {
        if (item.href === null) {
            return false;
        }

        if (item.activePrefix) {
            return isCurrentOrParentUrl(item.activePrefix);
        }

        return isCurrentUrl(item.href as NonNullable<InertiaLinkProps['href']>);
    }

    return { items: mainNavItems, isActive };
}
