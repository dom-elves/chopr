<script setup>
import { ref, onMounted, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

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

onMounted(() => {

})

</script>

<template>
    <div class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none">
        <p
            @click="readNotification"
        >
            You have been added to a debt of <b>£{{ notification.data.amount.amount }}</b> for <b>{{ notification.data.name }}</b> in {{ notification.data.group_name }} by <b>{{ notification.data.owner.name }}</b>
        </p>
    </div>
</template>
<style>

</style>