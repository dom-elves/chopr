<script setup>
import { onMounted, ref, watch } from 'vue';
import { store } from '@/debt.js';
import InputError from '@/Components/Forms/InputError.vue';
import Slider from '@/Components/Misc/Slider.vue';

// props
const props = defineProps({
    errors: {
        type: String,
    },
});

// same principle as in AddDebtFormShare, make total a string for user's benefit
const userFacingTotalValue = ref('');

function setAmount() {
    if (store.addDebtForm.split_even) {
        store.splitEven(userFacingTotalValue.value);
    } else if (!store.addDebtForm.split_even) {
        store.calcTotalAmount();
    }
}

/**
 * Toggles between the debt being split even/custom shares. Basically looks at total_amount if split, 
 * and then indiviudal fields if not. 
 */
function toggleSplitEven(toggle) {
    store.addDebtForm.split_even = toggle;

    if (store.addDebtForm.split_even) {
        store.splitEven();
    } else {
        store.calcTotalAmount()
    }
}

onMounted(() => {
    console.log('f', store.addDebtForm);
})

// sort of inverse to how it works on shares
// as shares take a string and make a number for the sake of the form
// the total takes the number from the form and makes a string with decimal point
watch(() => store.addDebtForm.amount, (newAmount) => {
    const majorUnits = String(newAmount).slice(0, -2) || '0';
    const minorUnits = String(newAmount).slice(-2) || '00';
    userFacingTotalValue.value = majorUnits + '.' + minorUnits;
});

</script>
<template>
    <div class="flex flex-row justify-between mt-2">
        <div>
            <label 
                for="debt-amount" 
                class="block text-sm font-medium text-gray-700"
                id="debt-amount"
            >
                Amount:
            </label>
            <input
                v-model="userFacingTotalValue" 
                type="text"
                id="debt-amount" 
                amount="debt-amount" 
                aria-labelledby="debt-amount"
                @change="setAmount"
                :disabled="!store.addDebtForm.split_even"
                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            />
        </div>
        <Slider
            label="Split even"
            alignment="end"
            size="xs"
            @toggled="toggleSplitEven"
        >
        </Slider>
        <InputError class="mt-2" :message="errors" />
    </div>
</template>