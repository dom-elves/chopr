<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import Debt from '@/Components/Debts/Debt.vue';

const props = defineProps({
    group: {
        type: Object,
    },
});



</script>

<template>
    <div class="py-4 m-2 border-solid border-2 border-indigo-600">
        <h3> {{ group.name }}</h3>
        <Debt
            v-for="debt in group.debts"
            :debt="debt"
        >
        </Debt>
    </div>
</template>
