<script setup>
import { onMounted, ref, watch, computed } from 'vue';
import { store } from '@/debt.js';
import Slider from '@/Components/Misc/Slider.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import { useDebtStore } from '@/Stores/DebtStore';

const props = defineProps({
    index: {
        type: Number,
    },
});

const debtStore = useDebtStore();

/**
 * Chcked is toggled so that if the user switches to split even mid debt creation
 * the added users are still retained.
 *
 * Adding a number then removing it from the input defaults to and empty string
 * rather than an actual 0.
 */
// function setShareAmount() {
//     share.value.checked = true;

//     if (share.value.amount == '') {
//         share.value.checked = false;
//         share.value.amount = 0;
//     }

//     debtStore.calcTotalAmount();
// }

// function toggleShareChecked(toggle) {
//     debtStore.debtForm.user_shares[props.index].checked = toggle;
//     console.log(debtStore.debtForm.user_shares);
//     debtStore.splitEven();
// }

const displayAmount = computed({
    get() {
        return debtStore.debtForm.user_shares[props.index].amount / 100;
    },
    set(value) {
        const share = debtStore.debtForm.user_shares[props.index];

        if (value === '' || value === null) {
            share.amount = 0;
            share.checked = false;
        } else {
            share.amount = Math.round(value * 100);
            share.checked = true;
        }

        debtStore.calcTotalAmount();
    }
});

const shareName = computed({
    get() {
        return debtStore.debtForm.user_shares[props.index].name
    },
    set(value) {
        return debtStore.debtForm.user_shares[props.index].name = value
    }
});

function toggleShareChecked(toggle) {
    debtStore.debtForm.user_shares[props.index].checked = toggle;
    debtStore.splitEven();
}

</script>
<template>
    <div class="flex flex-col py-1 mt-2 ">
        <p>
            {{ debtStore.debtForm.user_shares[props.index].user_name }}
        </p>
        <div class="flex flex-row justify-around">
            <div class="flex flex-row items-center w-full">
                <label 
                    :for="`share-name-${index}`"
                    class="hidden"
                >
                    Share name:
                </label>
                <TextInput
                    type="text"
                    :id="`share-name-${index}`"
                    :name="`share-name-${index}`"
                    v-model="shareName"
                    placeholder="Share name..."
      
                    class="w-full"
                >
                </TextInput>
            </div>
            <div class="flex flex-row justify-center items-center">
                <label 
                    :for="`share-amount-${index}`"
                    class="hidden"
                >
                    Amount
                </label>
                <input 
                    type="number"
                    step="0.01"
                    :id="`share-amount-${index}`"
                    :name="`share-amount-${index}`"
                    v-model="displayAmount"
                    :disabled="debtStore.debtForm.split_even"
                    class="w-1/2 md:w-24 ml-4 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mr-2"
                >
                <div :class="debtStore.debtForm.split_even ? '' : 'invisible'">
                    <Slider
                        @toggled="toggleShareChecked"
                        :checked="debtStore.debtForm.user_shares[props.index].checked"
                        alignment="end"
                    >
                    </Slider>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>

textarea {
    resize: none;
}

textarea:invalid {
  border: 2px solid red;
}
</style>