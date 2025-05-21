<script setup>
import { computed, onMounted, onUnmounted, ref, reactive, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import CurrencyPicker from '@/Components/CurrencyPicker.vue';
import GroupPicker from '@/Components/Groups/GroupPicker.vue';
import InputError from '@/Components/InputError.vue';
import Slider from '@/Components/Slider.vue';
import AddDebtShare from './Form/AddDebtFormShare.vue';
import AddDebtForm from './Form/AddDebtForm.vue';

// props
const props = defineProps({
    groups: {
        type: Object,
    }
});

</script>

<template>
    <div class="py-4 my-2 border-solid border-2 border-green-600 bg-white flex flex-col">
        <AddDebtForm
            :groups="groups"
        >
        </AddDebtForm>
    </div>
</template>
<style>

</style>