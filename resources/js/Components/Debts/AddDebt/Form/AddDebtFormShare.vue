<script setup>
import { onMounted, ref } from 'vue';
import { store } from '@/debt.js';
import Slider from '@/Components/Misc/Slider.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import { useDebtStore } from '@/Stores/DebtStore';

// props
const props = defineProps({
    group_user: {
        type: Object,
    },
});

const share = ref(useDebtStore().debtForm.user_shares.find((userShare) => 
    userShare.group_user_id == props.group_user.id
));

/**
 * Chcked is toggled so that if the user switches to split even mid debt creation
 * the added users are still retained.
 *
 * Adding a number then removing it from the input defaults to and empty string
 * rather than an actual 0.
 */
function setShareAmount() {
    share.value.checked = true;

    if (share.value.amount == '') {
        share.value.checked = false;
        share.value.amount = 0;
    }

    useDebtStore().calcTotalAmount();
}

function toggleShareChecked(toggle) {
    share.value.checked = toggle;

    store.addDebtForm.user_shares.find((userShare) => 
        userShare.group_user_id == share.value.group_user_id).checked = share.value.checked;

    store.splitEven();
}

onMounted(() => {
    console.log(share.value);
});

</script>
<template>
    <div class="flex flex-col py-1 mt-2 ">
        <p>
            {{ group_user.user.name }}
        </p>
        <div class="flex flex-row justify-around">
            <div class="flex flex-row items-center w-full">
                <label 
                    :for="`share-name-${group_user.id}`"
                    class="hidden"
                >
                    Share name:
                </label>
                <TextInput
                    type="text"
                    :id="`share-name-${group_user.id}`"
                    :name="`share-name-${group_user.id}`"
                    v-model="share.name"
                    placeholder="Share name..."
      
                    class="w-full"
                >
                </TextInput>
            </div>
            <div class="flex flex-row justify-center items-center">
                <label 
                    :for="`share-amount-${group_user.id}`"
                    class="hidden"
                >
                    Amount
                </label>
                <input 
                    type="number"
                    step="0.01"
                    :id="`share-amount-${group_user.id}`"
                    :name="`share-amount-${group_user.id}`"
                    v-model="share.amount"
                    @change="setShareAmount"
                    :disabled="useDebtStore().debtForm.split_even"
           
                    class="w-1/2 md:w-24 ml-4 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mr-2"
                >
                <div :class="useDebtStore().debtForm.split_even ? '' : 'invisible'">
                    <Slider
                        @toggled="toggleShareChecked"
                        :checked="share.checked"
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