<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { VListItem } from 'vuetify/components';
import AppLogo from '@/components/AppLogo.vue';
import UserAvatar from '@/components/UserAvatar.vue';
import UserMenu from '@/components/UserMenu.vue';
import { useAppNavigation } from '@/composables/useAppNavigation';
import { dashboard } from '@/routes';

/**
 * Navigation drawer. From md (960) up it is permanent and collapses to a
 * rail; below md AppLayout renders it as a modal drawer (`modal`) opened
 * from the app bar, since there are fewer than three destinations for an
 * MD3 navigation bar.
 */
const { modal = false } = defineProps<{ modal?: boolean }>();
const rail = defineModel<boolean>('rail', { default: false });
const open = defineModel<boolean>('open', { default: true });

const { items, isActive } = useAppNavigation();
const showLabels = computed(() => modal || !rail.value);
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
        v-model="open"
        :rail="!modal && rail"
        :permanent="!modal"
        :temporary="modal"
        color="surface-container-low"
        width="280"
        rail-width="80"
        data-test="app-drawer"
    >
        <div class="pa-4">
            <Link :href="dashboard()" class="drawer-home" aria-label="Home">
                <AppLogo :show-name="modal || !rail" />
            </Link>
        </div>

        <v-list nav density="comfortable" aria-label="Main">
            <template v-for="item in items" :key="item.title">
                <v-tooltip
                    :disabled="modal || !rail"
                    :text="item.title"
                    location="end"
                >
                    <template #activator="{ props: tooltip }">
                        <v-list-item
                            v-if="item.href === null"
                            v-bind="tooltip"
                            :prepend-icon="item.icon"
                            :title="showLabels ? item.title : undefined"
                            :aria-label="item.title"
                            disabled
                            rounded="xl"
                        />
                        <Link
                            v-else
                            v-bind="tooltip"
                            :as="VListItem"
                            :href="item.href"
                            :prepend-icon="item.icon"
                            :title="showLabels ? item.title : undefined"
                            :aria-label="item.title"
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
                    :title="showLabels ? link.title : undefined"
                    :aria-label="link.title"
                    rounded="xl"
                />
            </v-list>

            <v-divider />

            <div class="pa-2">
                <UserMenu location="end bottom">
                    <template #activator="{ props: activator }">
                        <v-list-item
                            v-bind="activator"
                            :title="showLabels ? user.name : undefined"
                            :subtitle="showLabels ? user.email : undefined"
                            rounded="xl"
                            class="drawer-user"
                            data-test="sidebar-menu-button"
                        >
                            <template #prepend>
                                <UserAvatar :user="user" :size="36" />
                            </template>
                            <template v-if="showLabels" #append>
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
