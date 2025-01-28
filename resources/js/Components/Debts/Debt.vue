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
const isEditing = ref(false);

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

function editDebt() {
    router.post(route('debt.update', { 
        debt_id: props.debt.id,
        new_debt_amount: props.debt.amount,
    }), {preserveScroll: true})
}

</script>

<template>
    <div class="my-2 border-solid border-2 border-amber-600">
        <div class="flex flex-row items-center">
            <i 
                class="fa-solid fa-chevron-up p-2"
                @click="showShares = !showShares"
                :class="showShares ? 'rotate180' : 'rotateback'"
            >
            </i>
            <p class="p-2 text-xl w-full text-center w-full" v-if="!isEditing"> 
                {{ props.debt.name }}
                {{ debtCurrency.symbol }}{{ props.debt.amount }} 
                <small class="text-xs">
                    {{  debtCurrency.code }}
                </small>
            </p>
            <div class="w-full flex flex-row" v-else>
                <label for="debtAmount">New Amount:</label>
                <input 
                    type="number"
                    step="0.01"
                    id="debtAmount"
                    v-model="props.debt.amount"
                    @blur="editDebt(props.debt.amount)"
                >
            </div>
            <div class="p-2 flex flex-row justify-between">
                <i 
                    class="fa-solid fa-gear mx-1"
                    @click="isEditing = !isEditing"
                >
                </i>
                <i 
                    class="fa-solid fa-x mx-1"
                    @click="confirmDebtDeletion"
                >
                </i>
            </div>
        </div>
        <div class="p-2 md:grid-cols-2 lg:flex lg:flex-row lg:justify-evenly" v-show="showShares">
            <Share
                v-for="shares in debt.shares"
                :share="shares"
                :currency="debt.currency"
            >
            </Share>
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
<style>

/* todo: put this in scss or find some tailwind/vue ootb for it */
.rotate180 {
    transform: rotate(180deg);
    transition: transform 0.5s ease;
}

.rotateback {
    transition: transform 0.5s ease;
}
</style>
