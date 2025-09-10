<script setup>

import { Head } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Form } from '@inertiajs/vue3';

defineProps({
    inertiaVariable: {
        type: String,
    },
});
</script>

<template>
    <Head title="Dashboard" />
    <p>I wonder if this works</p>
    <p>{{  inertiaVariable }}</p>
    <div class="flex flex-col p-2">
        <PrimaryButton>Primary Button</PrimaryButton>
        <SecondaryButton>Secondary Button</SecondaryButton>
        <Form>
            <label for="test">Test</label>
            <input type="text" name="test">
        </Form>
    </div>
</template>