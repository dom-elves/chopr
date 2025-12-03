<script setup>
import { Form } from '@inertiajs/vue3';
import { onMounted, computed } from 'vue';

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

const emit = defineEmits(['seenError', 'sentError']);

const sentAndSeenClasses = computed(() => {

})

onMounted(() => {

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
        @error="(errors) => emit(operation + 'Error', errors)"
    >
        <label :for="operation + share.id" class="text-xs font-semibold">
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
            :disabled="props.share.sent && props.share.seen"
            :id="props.operation + '-button-' + share.id"
            class="border-2 bg-gray-100 rounded border-black"
            style="height:35px;width:35px"
        >
            <i
                :id="props.operation + '-tick-' + share.id" 
                class="fa-solid fa-check"
                :class="share[operation] ? '' : 'hidden'"
            >
            </i>
        </button>
    </Form>
</template>