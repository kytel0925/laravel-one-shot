<script setup lang="ts">
/**
 * A settings block: an outlined MD3 card with a title, a supporting line,
 * the content and optional actions.
 */
const {
    title,
    description,
    tone = 'default',
} = defineProps<{
    title: string;
    description?: string;
    /** `danger` uses the error color roles (delete account). */
    tone?: 'default' | 'danger';
}>();
</script>

<template>
    <v-card
        :color="tone === 'danger' ? 'error-container' : 'surface-container-low'"
        variant="flat"
        rounded="xl"
    >
        <v-card-item>
            <v-card-title tag="h2" class="text-title-medium">{{
                title
            }}</v-card-title>
            <v-card-subtitle
                v-if="description"
                class="text-body-medium text-wrap"
            >
                {{ description }}
            </v-card-subtitle>
        </v-card-item>

        <v-card-text class="d-flex flex-column ga-4">
            <slot />
        </v-card-text>

        <v-card-actions v-if="$slots.actions" class="px-4 pb-4">
            <slot name="actions" />
        </v-card-actions>
    </v-card>
</template>
