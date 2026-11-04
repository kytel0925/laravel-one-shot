<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { login } from '@/routes';
import { email } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Forgot password',
        description: 'Enter your email to receive a password reset link',
        switchLink: {
            text: 'Or, return to',
            label: 'log in',
            href: login(),
        },
    },
});

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head title="Forgot password" />

    <v-alert v-if="status" type="success">{{ status }}</v-alert>

    <Form
        v-bind="email.form()"
        v-slot="{ errors, processing }"
        class="d-flex flex-column ga-4"
    >
        <v-text-field
            name="email"
            label="Email address"
            type="email"
            :error-messages="errors.email"
            autocomplete="off"
            v-focus
        />

        <v-btn
            type="submit"
            variant="flat"
            color="primary"
            size="large"
            block
            :loading="processing"
            :disabled="processing"
            data-test="email-password-reset-link-button"
        >
            Email password reset link
        </v-btn>
    </Form>
</template>
