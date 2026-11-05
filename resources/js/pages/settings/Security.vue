<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import PasswordField from '@/components/PasswordField.vue';
import SettingsSection from '@/components/SettingsSection.vue';
import { edit } from '@/routes/security';
import type { Props as ManagePasskeysProps } from '@/components/ManagePasskeys.vue';
import ManagePasskeys from '@/components/ManagePasskeys.vue';
import type { Props as ManageTwoFactorProps } from '@/components/ManageTwoFactor.vue';
import ManageTwoFactor from '@/components/ManageTwoFactor.vue';

// oxfmt-ignore
type Props = {
    passwordRules: string;
} & ManagePasskeysProps &
    ManageTwoFactorProps;

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Security settings',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Security settings" />

    <Form
        v-bind="SecurityController.update.form()"
        :options="{
            preserveScroll: true,
        }"
        reset-on-success
        :reset-on-error="[
            'password',
            'password_confirmation',
            'current_password',
        ]"
        v-slot="{ errors, processing }"
    >
        <SettingsSection
            title="Update password"
            description="Ensure your account is using a long, random password to stay secure"
        >
            <PasswordField
                id="current_password"
                name="current_password"
                label="Current password"
                autocomplete="current-password"
                placeholder="Current password"
                :error-messages="errors.current_password"
            />

            <PasswordField
                id="password"
                name="password"
                label="New password"
                autocomplete="new-password"
                placeholder="New password"
                :passwordrules="props.passwordRules"
                :error-messages="errors.password"
            />

            <PasswordField
                id="password_confirmation"
                name="password_confirmation"
                label="Confirm password"
                autocomplete="new-password"
                placeholder="Confirm password"
                :passwordrules="props.passwordRules"
                :error-messages="errors.password_confirmation"
            />

            <template #actions>
                <v-btn
                    type="submit"
                    color="primary"
                    variant="flat"
                    :loading="processing"
                    data-test="update-password-button"
                >
                    Save
                </v-btn>
            </template>
        </SettingsSection>
    </Form>

    <ManageTwoFactor
        :canManageTwoFactor="canManageTwoFactor"
        :requiresConfirmation="requiresConfirmation"
        :twoFactorEnabled="twoFactorEnabled"
    />

    <ManagePasskeys
        :canManagePasskeys="canManagePasskeys"
        :passkeys="passkeys"
    />
</template>
