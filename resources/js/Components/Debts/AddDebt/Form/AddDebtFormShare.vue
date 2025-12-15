<script setup>
import { onMounted, ref, watch } from 'vue';
import { store } from '@/debt.js';
import Slider from '@/Components/Misc/Slider.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import Dinero from 'dinero.js'
import InputError from '@/Components/Forms/InputError.vue';

// props
const props = defineProps({
    group_user: {
        type: Object,
    },
    errors: {
        type: Object,
        required: false,
    },
});

// because we have to mess around with string/int casting
// show the user a string of the share with a decimal point
const userFacingShareValue = ref('');
// ref for changing css on share input element
// const isShareValid = ref(true);
// the share for the user, taken from the store
const share = ref(store.addDebtForm.user_shares.find((userShare) => 
    userShare.user_id == props.group_user.user_id
));

// check validity of share
// if invalid, set it back to 0 and uncheck
// otherwise, check & cast to number so it can be calced
function setShareAmount() {
  
    
    if (!isShareValid.value) {

        share.value.checked = false;
        share.value.amount = 0;
    } else {

        share.value.checked = true;
        share.value.amount = Number(userFacingShareValue.value.replace('.', '')); 
    }

    store.calcTotalAmount();
}

// // check the validity of user input when attempting to add a share
// // regex is for a 9 digit number with 2 decimal places
// // specifically including the decimal point
// function validateShare() {
//     const regex = new RegExp("^\\d{1,9}\\.\\d{2}$");
//     return regex.test(userFacingShareValue.value);
// }

// just toggles if a split even share is checked
function toggleShareChecked(toggle) {
    share.value.checked = toggle;

    store.addDebtForm.user_shares.find((userShare) => 
        userShare.user_id == share.value.user_id).checked = share.value.checked;

    store.splitEven();
}

watch(() => share.value.amount, (newValue) => {
    console.log('nv', newValue);
    const majorUnits = String(newValue).slice(0, -2) || '0';
    const minorUnits = String(newValue).slice(-2) || '00';
    userFacingShareValue.value = majorUnits + '.' + minorUnits;
})

onMounted(() => {

});

</script>
<template>
    <div class="flex flex-col py-1 mt-2 ">
        <p>
            {{ group_user.user.name }}
        </p>
        <div class="flex flex-row justify-around">
            <div class="flex flex-row items-center w-full mr-2">
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
                    :for="`share-major-amount-${group_user.id}`"
                   class="mr-1"
                >
                    £
                </label>
                <input 
                    type="text"
                    :id="`share-major-amount-${group_user.id}`"
                    :name="`share-major-amount-${group_user.id}`"
                    v-model="userFacingShareValue"
                    @change="setShareAmount"
                    :disabled="store.addDebtForm.split_even"
                    class="w-1/2 md:w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mr-1"
                    :class="isShareValid ? '' : 'bg-red-200'"
                    maxlength="9"
                    inputmode="decimal"
                    placeholder="0.00"
                >
                <div :class="store.addDebtForm.split_even ? '' : 'invisible'">
                    <Slider
                        @toggled="toggleShareChecked"
                        :checked="share.checked"
                        alignment="end"
                    >
                    </Slider>
                </div>
            </div>
        </div>
        <InputError class="mt-2" :message="errors" />
    </div>
</template>
<style scoped>

</style>