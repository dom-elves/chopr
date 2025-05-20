<script setup>
import { onMounted, ref } from 'vue';
import { store } from '@/store.js';
import InputError from '@/Components/InputError.vue';

// props
const props = defineProps({
    errors: {
        type: String,
    },
});

const amount = ref(0);

const emit = defineEmits(['debtAmountEntered']);

function updateDebtAmount() {
    console.log(amount.value);
    emit('debtAmountEntered', amount.value);
}

onMounted(() => {
    console.log(props.split_even, 'aaaa');
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
            ste="0.01" 
            id="debt-amount" 
            amount="debt-amount" 
            class="w-full"
            aria-labelledby="debtAmount"
            @change=updateDebtAmount
            :disabled="!store.addDebtForm.split_even"
        />
        <InputError class="mt-2" :message="errors" />
    </div>
</template>