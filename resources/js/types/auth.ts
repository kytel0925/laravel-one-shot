import type { InertiaLinkProps } from '@inertiajs/vue3';

export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};

export type Passkey = {
    id: number;
    name: string;
    authenticator: string | null;
    created_at_diff: string;
    last_used_at_diff: string | null;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};

/** A link passed to `AuthLayout` (`switchLink`, `footer`). */
export type AuthLink = {
    /** Text before the link ("Don't have an account?"). */
    text?: string;
    label: string;
    href: NonNullable<InertiaLinkProps['href']>;
};
