<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import SettingsSection from '@/components/SettingsSection.vue';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Profile settings',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const name = ref(user.value.name);
const email = ref(user.value.email);
</script>

<template>
    <Head title="Profile settings" />

    <Form
        v-bind="ProfileController.update.form()"
        v-slot="{ errors, processing }"
    >
        <SettingsSection
            title="Profile"
            description="Update your name and email address"
        >
            <v-text-field
                v-model="name"
                id="name"
                name="name"
                label="Name"
                required
                autocomplete="name"
                placeholder="Full name"
                :error-messages="errors.name"
            />

            <v-text-field
                v-model="email"
                id="email"
                type="email"
                name="email"
                label="Email address"
                required
                autocomplete="username"
                placeholder="Email address"
                :error-messages="errors.email"
            />

            <div v-if="page.props.mustVerifyEmail && !user.email_verified_at">
                <p class="text-body-medium text-medium-emphasis">
                    Your email address is unverified.
                    <Link
                        :href="send()"
                        as="button"
                        class="text-primary text-body-medium"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <v-alert
                    v-if="page.props.status === 'verification-link-sent'"
                    type="success"
                    class="mt-3"
                >
                    A new verification link has been sent to your email address.
                </v-alert>
            </div>

            <template #actions>
                <v-btn
                    type="submit"
                    color="primary"
                    variant="flat"
                    :loading="processing"
                    data-test="update-profile-button"
                >
                    Save
                </v-btn>
            </template>
        </SettingsSection>
    </Form>

    <DeleteUser />
</template>
