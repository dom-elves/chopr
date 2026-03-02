import { defineStore } from 'pinia';
import { usePage, router } from '@inertiajs/vue3';

export const useNotificationStore = defineStore('notificationStore', {
    state: () => ({
        // taken from HandleInertiaRequests
        notifications: usePage().props.notifications,
    }),
    actions: {
        // read an individual notification and remove it from the store
        readNotification(id) {
             router.post(
                `/notifications/read/${id}`,
                {},
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        this.notifications = this.notifications.filter((notif) => notif.id !== id);
                    }
                },
            )
        },
        // read all notifications for a user
        readAllNotifications() {
            router.post(
                '/notifications/read-all', 
                {},
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        this.notifications = [];
                    },
                },
            )
        },
    }
})