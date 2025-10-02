<script setup>
import { computed, onMounted, onUnmounted, ref, reactive, watch } from 'vue';
import Debt from '@/Components/Debts/Debt.vue';
import AddDebt from '@/Components/Debts/AddDebt/AddDebt.vue';
import { router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    group: {
        type: Object,
    },
});

const showDebts = ref(false);

onMounted(() => {

})

</script>

<template>
    <div class="card">
        <h2 class="h2 text-center mb-4">{{ group.name }}</h2>
        <div v-if="group.debts.length > 0">
            <button
                class="w-full border-solid border-2 border-indigo-600 ml-1 bg-gray-100"
                @click="showDebts = !showDebts"
            >
            View Debts
            </button>
            <div v-show="showDebts">
                <Debt
                    v-for="debt in group.debts"
                    :debt="debt"
                    :group="group"
                >
                </Debt>
                
            </div>
        </div>
        <h3 
            class="text-3xl text-center my-4"
            v-else
        >
            No debts to show!
        </h3>
    </div>
</template>
