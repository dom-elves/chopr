<script setup>

 import { onMounted, ref, watch } from 'vue';

 const props = defineProps({
    type: {
        type: String,
        required: true,
    },
 })

 const isShareValid = ref(true);

 // check the validity of user input when attempting to add a share
// regex is for a 9 digit number with 2 decimal places
// specifically including the decimal point
function validateShare() {
    const regex = new RegExp("^\\d{1,9}\\.\\d{2}$");
    isShareValid.value = regex.test(userFacingShareValue.value);
}

</script>
<template>
    <div>
        <input 
            type="text"
            @change="validateShare"
            class="w-1/2 md:w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mr-1"
            :class="isShareValid ? '' : 'bg-red-200'"
            maxlength="9"
            inputmode="decimal"
            placeholder="0.00"
        />
    </div>
</template>