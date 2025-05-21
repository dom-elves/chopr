<script setup>
import { onMounted, ref } from 'vue';
import { store } from '@/store.js';
import InputError from '@/Components/InputError.vue';

// props
const props = defineProps({
    errors: {
        type: String,
    }
});

</script>
<template>
    <div>
        <label 
            for="debt-name" 
            class="block text-sm font-medium text-gray-700 hidden"
            id="debtName"
        >
            Debt Name
        </label>
        <input
            v-model="store.addDebtForm.name" 
            type="text" 
            id="debt-name" 
            name="debt-name" 
            class="w-full"
            placeholder="Debt Name"
            aria-labelledby="debtName"
        />
        <InputError class="mt-2" :message="errors" />
    </div>
</template>