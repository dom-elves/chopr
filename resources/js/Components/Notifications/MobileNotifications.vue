<script setup>
import { computed, ref } from 'vue';
import Notification from '@/Components/Notifications/Notification.vue';
import Notifications from './Notifications.vue';
import { useNotificationStore } from '@/Stores/NotificationStore.js';

const notifications = ref(useNotificationStore().notifications);

const active = ref(false);


// stolen from ResponsiveNavLink
const classes = computed(() =>
    active.value
        ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-indigo-400 text-start text-base font-medium text-indigo-700 bg-indigo-50 focus:outline-none focus:text-indigo-800 focus:bg-indigo-100 focus:border-indigo-700 transition duration-150 ease-in-out'
        : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out',
);

</script>
<template>
    <div>
        <p @click="active = !active" :class="classes">Notifications</p>
        <div v-if="active" class="ps-3 pe-4 py-2">
            <div v-if="Notifications.length === 0">
                <p>No notifications to show!</p>
            </div>
            <!-- todo: calc height of this or something -->
            <div v-else>
                <Notification v-for="notification in notifications"
                    :notification="notification"
                    @notificationRead="readNotification"
                >
                </Notification>
                <button 
                    class="block w-full px-4 py-2 text-center text-sm leading-5 text-blue-700 transition duration-150 ease-in-out hover:underline hover:cursor-pointer hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
                    @click="readAllNotifications"
                >
                    Mark all as read
                </button>
            </div>
        </div>
    </div>
</template>