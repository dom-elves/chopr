<script setup>
import { onMounted, ref } from 'vue';
import { store } from '@/debt.js';
import InputError from '@/Components/Forms/InputError.vue';
import { useDebtStore } from '@/Stores/DebtStore';

// props
const props = defineProps({
    errors: {
        type: String,
    },
});

const debtStore = useDebtStore();

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
            v-model="debtStore.debtForm.amount"
            type="number"
            step="0.01" 
            id="debt-amount" 
            amount="debt-amount" 
            aria-labelledby="debtAmount"
            @change=store.splitEven()
            :disabled="!debtStore.debtForm.split_even"
            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        />
        <InputError class="mt-2" :message="errors" />
    </div>
</template>