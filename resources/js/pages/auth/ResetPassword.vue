<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import PasswordField from '@/components/PasswordField.vue';
import { update } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Reset password',
        description: 'Please enter your new password below',
    },
});

const props = defineProps<{
    token: string;
    email: string;
    passwordRules: string;
}>();

const inputEmail = ref(props.email);
</script>

<template>
    <Head title="Reset password" />

    <Form
        v-bind="update.form()"
        :transform="(data) => ({ ...data, token, email })"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="d-flex flex-column ga-4"
    >
        <v-text-field
            v-model="inputEmail"
            name="email"
            label="Email"
            type="email"
            :error-messages="errors.email"
            autocomplete="email"
            readonly
        />

        <PasswordField
            name="password"
            label="Password"
            :error-messages="errors.password"
            autocomplete="new-password"
            v-focus
            :passwordrules="passwordRules"
        />

        <PasswordField
            name="password_confirmation"
            label="Confirm password"
            :error-messages="errors.password_confirmation"
            autocomplete="new-password"
            :passwordrules="passwordRules"
        />

        <v-btn
            type="submit"
            color="primary"
            size="large"
            block
            :loading="processing"
            :disabled="processing"
            data-test="reset-password-button"
        >
            Reset password
        </v-btn>
    </Form>
</template>
