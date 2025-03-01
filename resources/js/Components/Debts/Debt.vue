<script setup>
import { computed, guardReactiveProps, onMounted, onUnmounted, ref, reactive } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
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

const formErrors = reactive({
    owner_group_user_id: null,
});

const debtCurrency = computed(() => {
    return currencies.find((currency) => currency.code === props.debt.currency)
});

const confirmDebtDeletion = () => {
    confirmingDebtDeletion.value = true;
};

function deleteDebt() {
    router.delete(route('debt.destroy', { 
        debt_id: props.debt.id,
        owner_group_user_id: props.debt.collector_group_user_id, 
    }), {
        onError: (error) => {
            formErrors.owner_group_user_id = error.owner_group_user_id;
        },
    });
}

const closeModal = () => {
    confirmingDebtDeletion.value = false;
};

onMounted(() => {
    // console.log('aa', props.debt);
});
const form = useForm({
    debt_id: props.debt.id,
    name: props.debt.name,
    amount: props.debt.amount,
    owner_group_user_id: props.debt.collector_group_user_id,
});

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
            <p 
                v-if="!isEditing" 
                class="p-2 text-xl w-full text-center w-full"
            > 
                {{ props.debt.name }}
                {{ debtCurrency.symbol }}{{ props.debt.amount }} 
                <small class="text-xs">
                    {{  debtCurrency.code }}
                </small>
            </p>
            <div v-else>
                <form> <!-- todo: style this after thinking of actual design -->
                    <div>
                    <div class="flex flex-row">
                        <label 
                            for="newDebtName" 
                            style="display:none;"
                            id="newDebtNameLabel"
                        >
                        New Name
                        </label>
                        <input
                            type="text"
                            id="newDebtName"
                            aria-labelledby="newDebtNameLabel"
                            v-model="form.name"
                            @blur="router.patch(route('debt.update', form))"
                        >
                    </div>
                    <div class="flex flex-row">
                        <label 
                            for="debtAmount"
                            style="display:none;"
                            id="newDebtAmountLabel
                        ">
                        New Amount
                        </label>
                        <input 
                            type="number"
                            step="0.01"
                            id="newDebtAmount"
                            aria-labelledby="newDebtAmountLabel"
                            v-model="form.amount"
                            @blur="router.patch(route('debt.update', form))"
                        >
                    </div>
                    </div>
                </form>
            </div>
            <div
                v-if="usePage().props.ownership.debt_ids.includes(props.debt.id)"
                class="p-2 flex flex-row justify-between">
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
                v-for="share in debt.shares"
                :share="share"
                :currency="debt.currency"
                :key="share.id"
                :debt-owner="debt.collector_group_user_id"
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
                <p class="text-red-500" v-if="formErrors.owner_group_user_id">
                        {{ formErrors.owner_group_user_id }}
                    </p>
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
