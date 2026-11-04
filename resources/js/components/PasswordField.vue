<script setup lang="ts">
import { ref } from 'vue';

/**
 * Outlined password field with a show/hide toggle. Attributes such as
 * `name`, `label`, `autocomplete` and `error-messages` pass through to
 * `<v-text-field>`, so it works inside Inertia's `<Form>`.
 */
const visible = ref(false);
</script>

<template>
    <v-text-field :type="visible ? 'text' : 'password'">
        <template #append-inner>
            <v-btn
                :icon="visible ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                :aria-label="visible ? 'Hide password' : 'Show password'"
                :aria-pressed="visible"
                variant="text"
                color="on-surface-variant"
                density="comfortable"
                @click="visible = !visible"
            />
        </template>
    </v-text-field>
</template>
