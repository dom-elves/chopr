<script setup>
import { onMounted, ref } from 'vue';
import InputError from '@/Components/InputError.vue';

// props
const props = defineProps({
    group_user: {
        type: Object,
    },
    errors: {
        type: String,
    }
});

const name = ref('');

const emit = defineEmits(['debtNameEntered']);

function updateDebtName() {
    console.log(name.value);
    emit('debtNameEntered', name.value);
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
            @change=updateDebtName
        />
        <InputError class="mt-2" :message="errors" />
    </div>
</template>