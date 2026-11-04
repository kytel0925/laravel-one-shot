<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import PasswordField from '@/components/PasswordField.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';

defineProps<{
    passwordRules: string;
}>();

defineOptions({
    layout: {
        title: 'Create an account',
        description: 'Enter your details below to create your account',
        switchLink: {
            text: 'Already have an account?',
            label: 'Log in',
            href: login(),
        },
    },
});
</script>

<template>
    <Head title="Register" />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="d-flex flex-column ga-4"
    >
        <v-text-field
            name="name"
            label="Name"
            type="text"
            :error-messages="errors.name"
            required
            v-focus
            :tabindex="1"
            autocomplete="name"
        />

        <v-text-field
            name="email"
            label="Email address"
            type="email"
            :error-messages="errors.email"
            required
            :tabindex="2"
            autocomplete="email"
        />

        <PasswordField
            name="password"
            label="Password"
            :error-messages="errors.password"
            required
            :tabindex="3"
            autocomplete="new-password"
            :passwordrules="passwordRules"
        />

        <PasswordField
            name="password_confirmation"
            label="Confirm password"
            :error-messages="errors.password_confirmation"
            required
            :tabindex="4"
            autocomplete="new-password"
            :passwordrules="passwordRules"
        />

        <v-btn
            type="submit"
            color="primary"
            size="large"
            block
            tabindex="5"
            :loading="processing"
            :disabled="processing"
            data-test="register-user-button"
        >
            Create account
        </v-btn>
    </Form>
</template>
