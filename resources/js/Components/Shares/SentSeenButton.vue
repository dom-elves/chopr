<script setup>
import { Form } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps({
    operation: {
        type: String,
        required: true,
    },
    type: {
        type: String,
        required: true,
    },
    share: {
        type: Object,
        required: true,
    },
})

onMounted(() => {
    console.log(props.share, props.type);
})

</script>
<template>
    <Form
        class=" flex flex-col mx-2"
        :action="route('share.' + operation, share)"
        method="patch"
        #default="{ errors }"
        :options="{
            preserveScroll: true,
        }"
        :transform="data => ({ 
            ...data, 
            id: props.share.id,
            [operation]: !props.share[operation],
        })"
    >
        <label :for="operation" class="text-xs font-semibold">
            {{ operation.toUpperCase() }}
        </label>
        <input 
            type="checkbox"
            :id="operation + share.id" 
            class="hidden"
            :name="operation"
            :value="share[operation]"
        >
        <button
            class="border-2 bg-gray-100 rounded border-black"
            style="height:35px;width:35px"
        >
            <i 
                class="fa-solid fa-check"
                :class="share[operation] ? '' : 'hidden'"
            >
            </i>
        </button>
    </Form>
</template>