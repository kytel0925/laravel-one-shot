<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { VBtn, VListItem } from 'vuetify/components';
import UserAvatar from '@/components/UserAvatar.vue';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';

const { location = 'bottom end' } = defineProps<{
    location?: 'bottom end' | 'top end' | 'end bottom';
}>();

const page = usePage();
const user = computed(() => page.props.auth.user);

function handleLogout(): void {
    router.flushAll();
}
</script>

<template>
    <v-menu :location="location" min-width="260">
        <template #activator="{ props: activator }">
            <slot name="activator" :props="activator" :user="user">
                <v-btn
                    v-bind="activator"
                    icon
                    variant="text"
                    color="default"
                    aria-label="Open user menu"
                    data-test="user-menu-button"
                >
                    <UserAvatar :user="user" :size="36" />
                </v-btn>
            </slot>
        </template>

        <v-card rounded="lg">
            <v-list density="comfortable" nav>
                <v-list-item :title="user.name" :subtitle="user.email">
                    <template #prepend>
                        <UserAvatar :user="user" :size="40" class="me-3" />
                    </template>
                </v-list-item>

                <v-divider class="my-2" />

                <Link
                    :as="VListItem"
                    :href="edit()"
                    prefetch
                    prepend-icon="mdi-cog-outline"
                    title="Settings"
                />

                <Link
                    :as="VListItem"
                    :href="logout()"
                    method="post"
                    prepend-icon="mdi-logout"
                    title="Log out"
                    data-test="logout-button"
                    @click="handleLogout"
                />
            </v-list>
        </v-card>
    </v-menu>
</template>
