<script setup>
import { computed, onMounted, onUnmounted, ref, reactive, watch } from 'vue';
import Debt from '@/Components/Debts/Debt.vue';
import AddDebt from '@/Components/Debts/AddDebt.vue';
import { router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    group: {
        type: Object,
    },
});

const showDebts = ref(false);
const showAddDebts = ref(false);

onMounted(() => {
    // console.log('group', props.group);
})

</script>

<template>
    <div class="p-4 m-2 border-solid border-2 border-indigo-600">
        <h3 class="text-3xl text-center mb-4"> {{ group.name }}</h3>
        <div class="flex flex-row">
            <button 
                class="w-1/2 border-solid border-2 border-indigo-600 mr-1"
                @click="showAddDebts = !showAddDebts"
            >
                Add Debt
            </button>
            <button 
                class="w-1/2 border-solid border-2 border-indigo-600 ml-1"
                @click="showDebts = !showDebts"
            >
                View Debts
            </button>
        </div>
        <AddDebt
            v-show="showAddDebts"
            :group-users="group.group_users"
            :group-id="group.id"
          >
        </AddDebt>
        <div v-show="showDebts">
            <Debt
                v-if="group.debts.length > 0"
                v-for="debt in group.debts"
                :debt="debt"
                :group="group"
            >
            </Debt>
            <h3 
                class="text-3xl text-center my-4"
                v-else
            >
                No debts to show!
            </h3>
        </div>
    </div>
</template>
