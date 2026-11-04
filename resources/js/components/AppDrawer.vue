<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { VListItem } from 'vuetify/components';
import AppLogo from '@/components/AppLogo.vue';
import UserAvatar from '@/components/UserAvatar.vue';
import UserMenu from '@/components/UserMenu.vue';
import { useAppNavigation } from '@/composables/useAppNavigation';
import { dashboard } from '@/routes';

const rail = defineModel<boolean>('rail', { default: false });

const { items, isActive } = useAppNavigation();
const page = usePage();
const user = computed(() => page.props.auth.user);

const footerLinks = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: 'mdi-github',
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: 'mdi-book-open-variant-outline',
    },
];
</script>

<template>
    <v-navigation-drawer
        :rail="rail"
        permanent
        color="surface-container-low"
        width="280"
        rail-width="80"
        data-test="app-drawer"
    >
        <div class="pa-4">
            <Link :href="dashboard()" class="drawer-home" aria-label="Home">
                <AppLogo :show-name="!rail" />
            </Link>
        </div>

        <v-list nav density="comfortable" aria-label="Main">
            <template v-for="item in items" :key="item.title">
                <v-tooltip :disabled="!rail" :text="item.title" location="end">
                    <template #activator="{ props: tooltip }">
                        <v-list-item
                            v-if="item.href === null"
                            v-bind="tooltip"
                            :prepend-icon="item.icon"
                            :title="item.title"
                            disabled
                            rounded="xl"
                        />
                        <Link
                            v-else
                            v-bind="tooltip"
                            :as="VListItem"
                            :href="item.href"
                            :prepend-icon="item.icon"
                            :title="item.title"
                            :active="isActive(item)"
                            :aria-current="isActive(item) ? 'page' : undefined"
                            color="secondary"
                            rounded="xl"
                            prefetch
                        />
                    </template>
                </v-tooltip>
            </template>
        </v-list>

        <template #append>
            <v-list nav density="compact" aria-label="Resources">
                <v-list-item
                    v-for="link in footerLinks"
                    :key="link.title"
                    :href="link.href"
                    target="_blank"
                    rel="noopener"
                    :prepend-icon="link.icon"
                    :title="link.title"
                    rounded="xl"
                />
            </v-list>

            <v-divider />

            <div class="pa-2">
                <UserMenu location="end bottom">
                    <template #activator="{ props: activator }">
                        <v-list-item
                            v-bind="activator"
                            :title="rail ? undefined : user.name"
                            :subtitle="rail ? undefined : user.email"
                            rounded="xl"
                            class="drawer-user"
                            data-test="sidebar-menu-button"
                        >
                            <template #prepend>
                                <UserAvatar :user="user" :size="36" />
                            </template>
                            <template v-if="!rail" #append>
                                <v-icon icon="mdi-unfold-more-horizontal" />
                            </template>
                        </v-list-item>
                    </template>
                </UserMenu>
            </div>
        </template>
    </v-navigation-drawer>
</template>

<style scoped>
.drawer-user :deep(.v-list-item-subtitle) {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    word-break: normal;
}

.drawer-home {
    color: inherit;
    text-decoration: none;
}
</style>
