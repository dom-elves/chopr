import { defineStore } from 'pinia';

export const useNotificationStore = defineStore('notificationStore', {
    state: () => ({
        notifications: ['test notif'],
    })
})