<script setup>
import { computed, guardReactiveProps, onMounted, onUnmounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { currencies } from '@/currencies.js';
import Share from '@/Components/Shares/Share.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    debt: {
        type: Object,
    },
});
const confirmingDebtDeletion = ref(false);
const showShares = ref(false);

const debtCurrency = computed(() => {
    return currencies.find((currency) => currency.code === props.debt.currency)
});

const confirmDebtDeletion = () => {
    confirmingDebtDeletion.value = true;
};

function deleteDebt() {
    router.delete(route('debt.destroy', { debt_id: props.debt.id }));
}

const closeModal = () => {
    confirmingDebtDeletion.value = false;
};

</script>

<template>
    <div class="m-2 border-solid border-2 border-amber-600">
        <p 
            class="p-2 text-xl w-100 text-center"
            @click="showShares = !showShares"
        > 
            {{ props.debt.name }}
            {{ debtCurrency.symbol }}{{ props.debt.amount }} 
            <small class="text-xs">
                {{  debtCurrency.code }}
            </small>
        </p>
        <div v-show="showShares">
            <div class="flex flex-row flex-wrap justify-evenly">
                <Share
                    v-for="shares in debt.shares"
                    :share="shares"
                >
                </Share>
            </div>
            <div class="p-2 flex flex-row justify-between">
                <!-- <i 
                    class="fa-solid fa-gear mx-1"
                    @click="editDebt"
                >
                </i>
                <i 
                    class="fa-solid fa-x mx-1"
                    @click="confirmDebtDeletion"
                >
                </i> -->
                <button 
                    class="w-1/2 border-solid border-2 border-indigo-600 mr-1"
                    @click=""
                >
                    Edit Debt
                </button>
                <button 
                    class="w-1/2 border-solid border-2 border-indigo-600 ml-1"
                    @click="confirmDebtDeletion"
                >
                    Delete Debt
                </button>
            </div>
        </div>
        <Modal :show="confirmingDebtDeletion" @close="closeModal">
            <div class="p-6">
                <h2
                    class="text-lg font-medium text-gray-900"
                >
                    Are you sure you want to delete this debt?
                </h2>
                <div class="mt-6 flex justify-end">
                    <button 
                        @click="closeModal"
                    >
                        Cancel
                    </button>
                    <button
                        class="ms-3"
                        @click="deleteDebt"
                    >
                        Delete Debt
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>
