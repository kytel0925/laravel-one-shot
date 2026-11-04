<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { VListItem } from 'vuetify/components';
import PageHeading from '@/components/PageHeading.vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';

defineOptions({ inheritAttrs: false });

const items = [
    { title: 'Profile', href: editProfile(), icon: 'mdi-account-outline' },
    {
        title: 'Security',
        href: editSecurity(),
        icon: 'mdi-shield-lock-outline',
    },
    {
        title: 'Appearance',
        href: editAppearance(),
        icon: 'mdi-palette-outline',
    },
];

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <PageHeading
        title="Settings"
        description="Manage your profile and account settings"
    />

    <div class="settings-layout">
        <nav class="settings-nav" aria-label="Settings">
            <v-list
                nav
                density="comfortable"
                bg-color="transparent"
                class="pa-0"
            >
                <Link
                    v-for="item in items"
                    :key="toUrl(item.href)"
                    :as="VListItem"
                    :href="item.href"
                    :prepend-icon="item.icon"
                    :title="item.title"
                    :active="isCurrentOrParentUrl(item.href)"
                    :aria-current="
                        isCurrentOrParentUrl(item.href) ? 'page' : undefined
                    "
                    color="secondary"
                    rounded="xl"
                    prefetch
                />
            </v-list>
        </nav>

        <section class="settings-content d-flex flex-column ga-6">
            <slot />
        </section>
    </div>
</template>

<style scoped>
.settings-layout {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.settings-content {
    min-width: 0;
    max-width: 672px;
}

@media (min-width: 960px) {
    .settings-layout {
        flex-direction: row;
        gap: 48px;
    }

    .settings-nav {
        width: 220px;
        flex-shrink: 0;
    }

    .settings-content {
        flex: 1;
    }
}
</style>
