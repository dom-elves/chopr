<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import Notification from '@/Components/Notifications/Notification.vue';
import Dropdown from '@/Components/Forms/Dropdown.vue';
import { useNotificationStore } from '@/Stores/NotificationStore.js';

// set notifs as a computed property so they are reactive
const notifications = computed(() => useNotificationStore().notifications);

function readAllNotifications() {
    router.post(
        '/notifications/read-all', 
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                notifications.value = [];
            },
        },
    )
}

</script>
<template>
    <Dropdown 
        align="right"
        width="96"
    >
        <template #trigger>
            <button
                type="button"
                class="text-center inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
            >
                <i class="fa-solid fa-circle-exclamation text-2xl text-gray-400"></i>
                <div
                    class="bg-red-500 rounded-full flex items-center justify-center" 
                    :class="notifications.length > 0 ? 'visible' : 'invisible'" 
                    style="position:relative;right:10px;bottom:10px;height:25px;width:25px"
                >
                    <p v-if="notifications.length < 100" class="text-white text-center">{{ notifications.length }}</p>
                    <p v-else class="text-white text-center">100+</p>
                </div>
            </button>
        </template>
        <template #content v-if="notifications.length === 0">
            <p class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none">
                You have no new notifications
            </p>
        </template>
        <template #content v-else>
            <div style="max-height:50vh;overflow-y:scroll;">
                <Notification v-for="notification in notifications"
                    :notification="notification"
                >
                </Notification>
            </div>
            <button 
                class="block w-full px-4 py-2 text-center text-sm leading-5 text-blue-700 transition duration-150 ease-in-out hover:underline hover:cursor-pointer hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
                @click="readAllNotifications"
            >
                Mark all as read
            </button>
        </template>
    </Dropdown>
</template>