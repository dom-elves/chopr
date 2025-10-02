<script setup>
import AddDebtForm from './Form/AddDebtForm.vue';

// props
const props = defineProps({
    groups: {
        type: Object,
    }
});

</script>

<template>
    <div class="p-4 my-2 border-solid border-2 border-green-600 bg-white flex flex-col">
        <AddDebtForm
            :groups="groups"
        >
        </AddDebtForm>
    </div>
</template>
<style>

</style>