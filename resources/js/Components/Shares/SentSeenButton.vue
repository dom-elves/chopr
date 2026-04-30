<script setup>
import { Form } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const props = defineProps({
    operation: {
        type: String,
        required: true,
    },
    share: {
        type: Object,
        required: true,
    },
})

// emitting errors up to parent is just so the error can appear in the right place
const emit = defineEmits(['seenError', 'sentError']);

// for changing the status on the frontend
// as using partial reloads we don't get the updated share back from the db
// so traffic is only going one way until we perform a refresh
const status = ref(props.share[props.operation]);

onMounted(() => {})

</script>
<template>
    <Form
        class=" flex flex-col mx-2"
        :action="route('share.' + operation, share)"
        method="patch"
        #default="{ errors }"
        :options="{
            preserveScroll: true,
            // this is to prevent full reloads
            only: ['auth.user'],
        }"
        :transform="data => ({ 
            ...data, 
            [operation]: !status,
        })"
        @error="(errors) => emit(operation + 'Error', errors)"
        @success="status = !status;"
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
            type="submit"
            :disabled="props.share.sent && props.share.seen"
            :id="props.operation + '-button-' + share.id"
            class="border-2 bg-gray-100 rounded border-black"
            style="height:35px;width:35px"
        >
            <i
                :id="props.operation + '-tick-' + share.id" 
                class="fa-solid fa-check"
                :class="status ? '' : 'invisible'"
            >
            </i>
        </button>
    </Form>
</template>