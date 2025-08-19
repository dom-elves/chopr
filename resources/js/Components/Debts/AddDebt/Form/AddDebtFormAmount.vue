<script setup>
import { onMounted, ref } from 'vue';
import { store } from '@/debt.js';
import InputError from '@/Components/InputError.vue';

// props
const props = defineProps({
    errors: {
        type: String,
    },
});

onMounted(() => {

})

</script>
<template>
    <div>
        <label 
            for="debt-amount" 
            class="block text-sm font-medium text-gray-700 hidden"
            id="debtamount"
        >
            Debt Amount
        </label>
        <input
            v-model="store.addDebtForm.amount" 
            type="number"
            step="0.01" 
            id="debt-amount" 
            amount="debt-amount" 
            aria-labelledby="debtAmount"
            @change=store.splitEven()
            :disabled="!store.addDebtForm.split_even"
        />
        <InputError class="mt-2" :message="errors" />
    </div>
</template>