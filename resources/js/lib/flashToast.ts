import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { FlashToast } from '@/types/ui';

export type SnackbarMessage = {
    text: string;
    color: FlashToast['type'];
    timeout: number;
};

/** Messages shown by the `<v-snackbar-queue>` in `AppRoot.vue`. */
export const snackbarQueue = ref<SnackbarMessage[]>([]);

export function showToast(toast: FlashToast): void {
    snackbarQueue.value.push({
        text: toast.message,
        color: toast.type,
        timeout: toast.type === 'error' ? 8000 : 4000,
    });
}

/** Shows `Inertia::flash('toast', [...])` messages sent by controllers. */
export function initializeFlashToast(): void {
    router.on('flash', (event) => {
        const flash = (event as CustomEvent).detail?.flash;
        const data = flash?.toast as FlashToast | undefined;

        if (data) {
            showToast(data);
        }
    });
}
