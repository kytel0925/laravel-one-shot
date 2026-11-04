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
    <v-text-field
        :type="visible ? 'text' : 'password'"
        :append-inner-icon="visible ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
        :append-inner-icon-aria-label="
            visible ? 'Hide password' : 'Show password'
        "
        @click:append-inner="visible = !visible"
    />
</template>
