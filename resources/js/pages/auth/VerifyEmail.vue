<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import TextLink from '@/components/TextLink.vue';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

defineOptions({
    layout: {
        icon: 'mdi-email-check-outline',
        title: 'Email verification',
        description:
            'Please verify your email address by clicking on the link we just emailed to you.',
    },
});

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head title="Email verification" />

    <v-alert v-if="status === 'verification-link-sent'" type="success">
        A new verification link has been sent to the email address you provided
        during registration.
    </v-alert>

    <Form v-bind="send.form()" v-slot="{ processing }">
        <v-btn
            type="submit"
            variant="flat"
            color="primary"
            size="large"
            block
            :loading="processing"
            :disabled="processing"
        >
            Resend verification email
        </v-btn>
    </Form>

    <div class="text-center text-body-medium">
        <TextLink :href="logout()" method="post" as="button">Log out</TextLink>
    </div>
</template>
