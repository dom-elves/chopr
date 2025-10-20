<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    // this will have to be optional for now until i change all the ootb text inputs to
    // use the new inertia form from 2.1
    // required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        v-model="model"
        ref="input"
    />
</template>
