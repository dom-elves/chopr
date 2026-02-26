<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { notificationTypes } from '../../notifications';

const emit = defineEmits(['notificationRead']);

const props = defineProps({
    notification: {
        type: Object,
        required: true,
    }
})

function readNotification() {
    router.post(
        `/notifications/read/${props.notification.id}`, 
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                emit('notificationRead', props.notification.id)
            },
        },
    )
}
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

onMounted(() => {

})

</script>

<template>
    <div class="flex flex-row items-center w-full py-2 text-start text-sm leading-5 text-gray-700 border-b border-gray-100 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none">
        
        <i class="fa-solid px-4" :class="notifData.classes"></i>
        <p>
            {{ notifData.message }}
        </p>
        <svg class=" p-1 ml-2 mr-6 block shrink-0 aspect-square hover:cursor-pointer hover:bg-gray-200 rounded-full"
            width="25" height="25" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"
            @click="readNotification"
        >
            <line x1="10" y1="10" x2="90" y2="90" stroke="gray" stroke-width="15" stroke-linecap="round"/>
            <line x1="90" y1="10" x2="10" y2="90" stroke="gray" stroke-width="15" stroke-linecap="round"/>
        </svg>
    </div>
</template>
<style>

</style>