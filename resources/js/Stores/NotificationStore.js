import { defineStore } from 'pinia';
import { usePage } from '@inertiajs/vue3';

export const useNotificationStore = defineStore('notificationStore', {
    state: () => ({
        // taken from HandleInertiaRequests
        notifications: usePage().props.notifications,
    })
})