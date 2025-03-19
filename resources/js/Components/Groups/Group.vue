<script setup>
import { computed, onMounted, onUnmounted, ref, reactive, watch } from 'vue';
import Debt from '@/Components/Debts/Debt.vue';
import AddDebt from '@/Components/Debts/AddDebt.vue';
import { router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    group: {
        type: Object,
    },
    // debts: {
    //     type: Array,
    //     default: [],
    // },
});

const showDebts = ref(false);
const showAddDebts = ref(false);
const debts = ref([]);

function getDebts() {
    showDebts.value = !showDebts.value;
    router.get(route('debt.index'), {
        'id' : props.group.id,
    },
    {
        preserveScroll: true,
        onSuccess: (response) => {
            // console.log('r', response.props.debts);
            debts.value = response.props.debts;
            console.log(debts.value);
        },
        onError: (error) => {
            console.log('e', error);
        }
    });
}

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
                @click="getDebts()"
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
                v-if="debts.length > 0"
                v-for="debt in debts"
                :debt="debt"
            >
            </Debt>
            <h3 
                class="text-3xl text-center my-4"
      
            >
                No debts to show!
            </h3>
        </div>
    </div>
</template>
