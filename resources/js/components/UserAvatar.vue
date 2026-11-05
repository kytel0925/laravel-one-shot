<script setup lang="ts">
import { computed } from 'vue';
import { getInitials } from '@/composables/useInitials';
import type { User } from '@/types';

const { user, size = 36 } = defineProps<{ user: User; size?: number }>();

const initials = computed(() => getInitials(user.name));
</script>

<template>
    <v-avatar :size="size" color="secondary-container">
        <v-img v-if="user.avatar" :src="user.avatar" :alt="user.name" />
        <span v-else class="text-label-large">{{ initials }}</span>
    </v-avatar>
</template>
