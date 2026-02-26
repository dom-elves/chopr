<script setup>
import { computed } from 'vue';
import { notificationTypes } from '../../notifications';
import { useNotificationStore } from '@/Stores/NotificationStore';

const props = defineProps({
    notification: {
        type: Object,
        required: true,
    }
})

/**
 * All notif data comes in as a prop.
 * Take the type, trim it down. This acts as a key to the object in notifications.js.
 * The relative function constructs the classes & message for the notification.
 * So essentially setting 'notif' as a const is to return the value of the function relative to the key given.
 * And that value is an object with the properties of 'classes' and 'message'.
 */
const notifData = computed(() => {
    const type = props.notification.type.replace('App\\Notifications\\', '');
    
    const notif = notificationTypes[type];

    return notif(props.notification.data);
});
</script>

<template>
    <div class="flex flex-row items-center w-full py-2 text-start text-sm leading-5 text-gray-700 border-b border-gray-100 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none">
        
        <i class="fa-solid px-4" :class="notifData.classes"></i>
        <p>
            {{ notifData.message }}
        </p>
        <svg class=" p-1 ml-2 mr-6 block shrink-0 aspect-square hover:cursor-pointer hover:bg-gray-200 rounded-full"
            width="25" height="25" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"
            @click="useNotificationStore().readNotification(props.notification.id)"
        >
            <line x1="10" y1="10" x2="90" y2="90" stroke="gray" stroke-width="15" stroke-linecap="round"/>
            <line x1="90" y1="10" x2="10" y2="90" stroke="gray" stroke-width="15" stroke-linecap="round"/>
        </svg>
    </div>
</template>
<style>

</style>