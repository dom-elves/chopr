<script setup>
import { ref, onMounted, watch } from 'vue';

const props = defineProps({
    notification: {
        type: Object,
        required: true,
    }
})

onMounted(() => {
    console.log(props.notification);
})

</script>

<template>
    <div class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none">
        <p>You have been added to a debt of £{{ notification.data.amount.amount }} for {{ notification.data.name }} in <b>{{ notification.data.group_name }}</b> by {{ notification.data.owner.name }}</p>
    </div>
</template>
<style>

</style>