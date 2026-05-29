<script setup>
import { onMounted, ref, watch, computed } from 'vue';
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
 * Having this as a computed so minor units are displayed as decimals e.g. 3300 is £3.30
 *
 * Chcked is toggled so that if the user switches to split even mid debt creation
 * the added users are still retained.
 */
const displayAmount = computed({
    get() {
        return debtStore.debtForm.user_shares[props.index].amount / 100;
    },
    set(value) {
        const share = debtStore.debtForm.user_shares[props.index];

        if (!value) {
            share.amount = 0;
            share.checked = false;
        } else {
            share.amount = Math.round(value * 100);
            share.checked = true;
        }

        debtStore.calcTotalAmount();
    }
});

/**
 * Just a simple get/set for the name of a share.
 */
const shareName = computed({
    get() {
        return debtStore.debtForm.user_shares[props.index].name
    },
    set(value) {
        return debtStore.debtForm.user_shares[props.index].name = value
    }
});

/**
 * Recalculates debt amount with splitEven() if a user is toggled
 */
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

</style>