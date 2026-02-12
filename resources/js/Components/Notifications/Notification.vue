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
    console.log(iconClass.value);
})

</script>

<template>
    <div class="flex flex-row items-center w-full py-2 text-start text-sm leading-5 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none">
        
        <i class="fa-solid px-4" :class="iconClass"></i>
        <p>
            You have been added to a debt of <b>£{{ notification.data.amount.amount }}</b> for <b>{{ notification.data.name }}</b> in {{ notification.data.group_name }} by <b>{{ notification.data.owner.name }}</b>
        </p>
        <i class="fa-solid fa-x px-2 hover:cursor-pointer hover:bg-gray-200 rounded-full text-center" style="height:20px;width:20px" @click="readNotification"></i>
    </div>
</template>
<style>

</style>