import type { InertiaLinkProps } from '@inertiajs/vue3';

export type BreadcrumbItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
};

export type NavItem = {
    title: string;
    /** `null` while the destination has no feature yet: rendered disabled. */
    href: NonNullable<InertiaLinkProps['href']> | null;
    /** An `mdi-*` icon name. */
    icon: string;
    /** Path prefix that also marks the item active (`/settings`). */
    activePrefix?: string;
};
