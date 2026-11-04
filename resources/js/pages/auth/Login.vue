<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import PasskeyVerify from '@/components/PasskeyVerify.vue';
import PasswordField from '@/components/PasswordField.vue';
import TextLink from '@/components/TextLink.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Log in to your account',
        description: 'Enter your email and password below to log in',
        switchLink: {
            text: "Don't have an account?",
            label: 'Sign up',
            href: register(),
        },
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <Head title="Log in" />

    <v-alert v-if="status" type="success">{{ status }}</v-alert>

    <PasskeyVerify />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="d-flex flex-column ga-4"
    >
        <v-text-field
            name="email"
            label="Email address"
            type="email"
            :error-messages="errors.email"
            required
            v-focus
            :tabindex="1"
            autocomplete="email"
        />

        <div>
            <PasswordField
                name="password"
                label="Password"
                :error-messages="errors.password"
                required
                :tabindex="2"
                autocomplete="current-password"
            />
            <div v-if="canResetPassword" class="text-end text-body-medium">
                <TextLink :href="request()" :tabindex="5">
                    Forgot your password?
                </TextLink>
            </div>
        </div>

        <v-checkbox
            name="remember"
            value="1"
            label="Remember me"
            hide-details
            density="compact"
            :tabindex="3"
        />

        <v-btn
            type="submit"
            color="primary"
            size="large"
            block
            :tabindex="4"
            :loading="processing"
            :disabled="processing"
            data-test="login-button"
        >
            Log in
        </v-btn>
    </Form>
</template>
