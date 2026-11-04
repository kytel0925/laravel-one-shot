<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import {
    index as confirmOptions,
    store as confirmStore,
} from '@/actions/Laravel/Passkeys/Http/Controllers/PasskeyConfirmationController';
import PasskeyVerify from '@/components/PasskeyVerify.vue';
import PasswordField from '@/components/PasswordField.vue';
import { store } from '@/routes/password/confirm';

defineOptions({
    layout: {
        icon: 'mdi-shield-lock-outline',
        title: 'Confirm password',
        description:
            'This is a secure area of the application. Please confirm your password before continuing.',
    },
});
</script>

<template>
    <Head title="Confirm password" />

    <PasskeyVerify
        :routes="{
            options: confirmOptions(),
            submit: confirmStore(),
        }"
        label="Confirm with passkey"
        loading-label="Confirming..."
        separator="Or confirm with password"
    />

    <Form
        v-bind="store.form()"
        reset-on-success
        v-slot="{ errors, processing }"
        class="d-flex flex-column ga-4"
    >
        <PasswordField
            name="password"
            label="Password"
            :error-messages="errors.password"
            required
            autocomplete="current-password"
            v-focus
        />

        <v-btn
            type="submit"
            color="primary"
            size="large"
            block
            :loading="processing"
            :disabled="processing"
            data-test="confirm-password-button"
        >
            Confirm password
        </v-btn>
    </Form>
</template>
