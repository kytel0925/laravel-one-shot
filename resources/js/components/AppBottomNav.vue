<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { VBtn } from 'vuetify/components';
import { useAppNavigation } from '@/composables/useAppNavigation';

const { items, isActive } = useAppNavigation();
</script>

<template>
    <v-bottom-navigation
        color="secondary"
        bg-color="surface-container"
        grow
        aria-label="Main"
        data-test="app-bottom-nav"
    >
        <template v-for="item in items" :key="item.title">
            <v-btn v-if="item.href === null" disabled>
                <v-icon :icon="item.icon" />
                <span>{{ item.title }}</span>
            </v-btn>
            <Link
                v-else
                :as="VBtn"
                :href="item.href"
                :active="isActive(item)"
                :aria-current="isActive(item) ? 'page' : undefined"
                prefetch
            >
                <v-icon :icon="item.icon" />
                <span>{{ item.title }}</span>
            </Link>
        </template>
    </v-bottom-navigation>
</template>
