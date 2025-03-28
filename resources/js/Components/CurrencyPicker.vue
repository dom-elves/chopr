<script setup>
import { computed, onMounted, onUnmounted, ref, reactive, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { currencies } from '@/currencies.js';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    errors: {
        type: String,
    }
});

</script>
<template>
    <div>
        <label 
            for="currency" 
            class="block text-sm font-medium text-gray-700 hidden"
            id="currencyType"
        >
            Currency
        </label>
        <select 
            @change="$emit('currencySelected', $event.target.value)" 
            id="currency"
            aria-labelledby="currencyType"
        >
            <option value="" disabled selected>Select a currency</option>
            <option v-for="currency in currencies"
                :key="currency.code"
                :value="currency.code"
            >
                {{  currency.name }}
            </option>>
            <!-- <option key="GBP" value="GBP">British Pound Sterling</option> -->
        </select>
        <InputError :message="errors" />
    </div>
</template>