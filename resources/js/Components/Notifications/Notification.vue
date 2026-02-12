<script setup>
import { ref, onMounted, watch, computed } from 'vue';
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

const iconClass = computed(() => {
    const type = props.notification.type;
    
    if (type.includes('Debt')) {
        return 'fa-list text-green-300';
    } else if (type.includes('Group')) {
        return 'fa-users text-blue-300';
    } else if (type.includes('Share')) {
        return 'fa-chart-pie text-orange-300';
    }
});

onMounted(() => {

})

</script>

<template>
    <div class="flex flex-row items-center w-full py-2 text-start text-sm leading-5 text-gray-700 border-b border-gray-100 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none">
        
        <i class="fa-solid px-4" :class="iconClass"></i>
        <p>
            You have been added to a debt of <b>£{{ notification.data.amount.amount }}</b> for <b>{{ notification.data.name }}</b> in {{ notification.data.group_name }} by <b>{{ notification.data.owner.name }}</b>
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