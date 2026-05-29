<script setup>
import { onMounted, ref, computed } from 'vue';
import InputError from '@/Components/Forms/InputError.vue';
import { useDebtStore } from '@/Stores/DebtStore';

const props = defineProps({
    errors: {
        type: String,
    },
});

const debtStore = useDebtStore();

/**
 * Very similar to how the get/set works in AddDebtFormShare.
 * Just have to initially return 0 as amount can be calced without user input.
 */
const debtAmount = computed({
    get() {
        return debtStore.debtForm.amount ? debtStore.debtForm.amount / 100 : 0;
    },
    set(value) {
        if (debtStore.debtForm.split_even) {
            debtStore.debtForm.amount = Math.round(value * 100);
            debtStore.splitEven();
        } else {
            debtStore.debtForm.amount = value;
        }
    }
});

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
            v-model="debtAmount"
            type="number"
            step="0.01" 
            id="debt-amount" 
            name="debt-amount"
            aria-labelledby="debtAmount"
            :disabled="!debtStore.debtForm.split_even"
            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        />
        <InputError class="mt-2" :message="errors" />
    </div>
</template>