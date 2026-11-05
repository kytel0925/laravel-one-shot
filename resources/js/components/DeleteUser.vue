<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import PasswordField from '@/components/PasswordField.vue';
import SettingsSection from '@/components/SettingsSection.vue';

const open = ref(false);

const focusPassword = () => {
    void nextTick(() => {
        document
            .querySelector<HTMLInputElement>('#delete-user-password')
            ?.focus();
    });
};
</script>

<template>
    <SettingsSection
        tone="danger"
        title="Delete account"
        description="Delete your account and all of its resources"
    >
        <p class="text-body-medium on-error-container">
            Warning: please proceed with caution, this cannot be undone.
        </p>

        <template #actions>
            <v-btn
                color="error"
                variant="flat"
                data-test="delete-user-button"
                @click="open = true"
            >
                Delete account
            </v-btn>
        </template>
    </SettingsSection>

    <v-dialog
        v-model="open"
        max-width="480"
        aria-labelledby="delete-account-title"
    >
        <Form
            v-bind="ProfileController.destroy.form()"
            reset-on-success
            :options="{
                preserveScroll: true,
            }"
            @error="focusPassword"
            v-slot="{ errors, processing, reset, clearErrors }"
        >
            <v-card rounded="xl">
                <v-card-title
                    id="delete-account-title"
                    tag="h2"
                    class="text-headline-small text-wrap pt-6 px-6"
                >
                    Are you sure you want to delete your account?
                </v-card-title>
                <v-card-text class="px-6">
                    <p class="text-body-medium mb-4">
                        Once your account is deleted, all of its resources and
                        data will also be permanently deleted. Please enter your
                        password to confirm you would like to permanently delete
                        your account.
                    </p>
                    <PasswordField
                        id="delete-user-password"
                        name="password"
                        label="Password"
                        placeholder="Password"
                        :error-messages="errors.password"
                    />
                </v-card-text>
                <v-card-actions class="px-6 pb-6">
                    <v-spacer />
                    <v-btn
                        variant="text"
                        @click="
                            () => {
                                clearErrors();
                                reset();
                                open = false;
                            }
                        "
                    >
                        Cancel
                    </v-btn>
                    <v-btn
                        type="submit"
                        color="error"
                        variant="flat"
                        :loading="processing"
                        data-test="confirm-delete-user-button"
                    >
                        Delete account
                    </v-btn>
                </v-card-actions>
            </v-card>
        </Form>
    </v-dialog>
</template>
