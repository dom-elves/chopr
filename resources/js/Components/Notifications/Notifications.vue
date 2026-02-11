<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useEchoNotification } from "@laravel/echo-vue";
import Notification from '@/Components/Notifications/Notification.vue';
import Dropdown from '@/Components/Forms/Dropdown.vue';

const notifications = ref(usePage().props.notifications);

useEchoNotification(
    `App.Models.User.${usePage().props.auth.user.id}`,
    (notification) => {
        // structure the notification the same way they are taken from db
        const dataNotification = {
            'data' : notification
        };

        notifications.value.push(dataNotification);
    },
);
</script>
<template>
    <Dropdown 
        align="right"
        width="80"
    >
        <template #trigger>
            <button
                type="button"
                class="text-center inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
            >
                <i v-if="notifications.length > 0" class="fa-solid fa-circle-exclamation text-2xl text-gray-400"></i>
                <p class="flex justify-center text-white bg-red-500 p-1 rounded-full text-center" style="height:25px;width:25px;position:relative;right:10px;bottom:10px">{{ notifications.length }}</p>
            </button>
        </template>
        <template #content v-if="notifications.length === 0">
            <p class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none">
                You have no new notifications
            </p>
        </template>
        <template #content v-else>
            <Notification v-for="notification in notifications"
                :notification="notification"
            >
            </Notification>
        </template>
    </Dropdown>
</template>