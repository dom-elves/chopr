<script setup>
import { onMounted, ref } from 'vue';
import { store } from '@/store.js';
import InputError from '@/Components/InputError.vue';

// props
const props = defineProps({
    errors: {
        type: String,
    }
});

const name = ref('');

function setDebtName() {
    store.addDebtForm.name = name.value;
    console.log('form', store.addDebtForm);
}

onMounted(() => console.log(props.split_even));

</script>
<template>
    <div>
        <label 
            for="debt-name" 
            class="block text-sm font-medium text-gray-700 hidden"
            id="debtName"
        >
            Debt Name
        </label>
        <input
            v-model="name" 
            type="text" 
            id="debt-name" 
            name="debt-name" 
            class="w-full"
            placeholder="Debt Name"
            aria-labelledby="debtName"
            @change=setDebtName
        />
        <InputError class="mt-2" :message="errors" />
    </div>
</template>