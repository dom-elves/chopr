<script setup>
import { computed, guardReactiveProps, onMounted, onUnmounted, ref, reactive, nextTick } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { currencies } from '@/currencies.js';
import Share from '@/Components/Shares/Share.vue';
import AddShare from '@/Components/Shares/AddShare.vue';
import Modal from '@/Components/Modal.vue';
import Comment from '@/Components/Comments/Comment.vue';
import AddComment from '@/Components/Comments/AddComment.vue';
import Controls from '@/Components/Controls.vue';
import InputError from '@/Components/InputError.vue';
import { Form } from '@inertiajs/vue3';

const props = defineProps({
    debt: {
        type: Object,
    },
    group: {
        type: Object,
    },
});
const confirmingDebtDeletion = ref(false);
const showShares = ref(false);
const showComments = ref(false);
const isEditing = ref(false);
// if the logged in user owners the debt, display the controls
const owns_debt = usePage().props.auth.user.id === props.debt.user_id ? true : false;

// debt update
const debtForm = useForm({
    id: props.debt.id,
    name: props.debt.name,
    amount: props.debt.amount.amount,
});

function updateDebt() {
    debtForm.patch(route('debt.update'), {
        preserveScroll: true,
        onSuccess: (data) => {
            isEditing.value = !isEditing.value;
        },
        onError: (error) => {
            // as mentioned in update() in DebtController, frontend handling of the error
            // as we need to warn the user, but still save the amount update
            if (error.amount && props.debt.split_even === 0) {
                const message = `There is a discrepancy of ${debtCurrency.value.symbol}${error.amount.toFixed(2)}.`;
                debtForm.errors.amount = message;
                isEditing.value = !isEditing.value;
            }
        }
    });
}

// misc
const debtCurrency = computed(() => {
    return currencies.find((currency) => currency.code === props.debt.currency)
});

const debtDiscrepancy = computed(() => {
    console.log(props.debt.shares[0].amount.amount);
    return props.debt.shares.reduce((total, share) => total + Number(share.amount.amount), 0);
});

const closeModal = () => {
    confirmingDebtDeletion.value = false;
};

onMounted(() => {
    if (debtDiscrepancy.value != props.debt.amount.amount) {
        const discrepancy = props.debt.amount.amount - debtDiscrepancy.value;
        debtForm.errors.amount = `There is a discrepancy of ${debtCurrency.value.symbol}${discrepancy.toFixed(2)}.`;
    }
});

</script>

<template>
    <div class="card">
        <div class="flex flex-row items-center">
            <i 
                class="fa-solid fa-chevron-up p-2"
                @click="showShares = !showShares"
                :class="showShares ? 'rotate180' : 'rotateback'"
            >
            </i>
            <h2 
                v-if="!isEditing" 
                class="h2 text-center mb-4"
            > 
                {{ props.debt.name }}
                {{ debtCurrency.symbol }}{{ props.debt.amount.amount }}
            </h2>
            <div v-else class="w-full">
                <form>
                    <div class="flex flex-col">
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
                                v-model="debtForm.name"
                                @blur="updateDebt"
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
                                v-model="debtForm.amount"
                                @blur="updateDebt"
                            >
                        </div>
                        <InputError class="mt-2" :message="debtForm.errors.id" />
                    </div>
                </form>
            </div>
            <Controls
                v-if="owns_debt && !isEditing"
                item="Debt"
                @edit="isEditing = !isEditing"
                @destroy="confirmingDebtDeletion = true"
            >
            </Controls>
        </div>
        <InputError class="mt-2" :message="debtForm.errors.amount" />
        <div class="p-2 md:grid-cols-2 lg:flex lg:flex-row lg:justify-evenly" v-show="showShares">
            <Share
                v-for="share in debt.shares"
                :share="share"
                :currency="debt.currency"
                :debt="debt"
            >
            </Share>
            <!-- <AddShare
                v-if="owns_debt"
                :debt="debt"
                :group_users="group.group_users"
            >
            </AddShare> -->
            <div class="flex flex-row items-center">
                <p>View Comments</p>
                <i 
                    class="fa-solid fa-chevron-up p-2"
                    @click="showComments = !showComments"
                    :class="showComments ? 'rotate180' : 'rotateback'"
                >
                </i>
            </div>
            <div v-show="showComments">
                <div style="height:50vh;overflow-y:scroll;">
                    <Comment
                        v-for="comment in debt.comments"
                        :comment="comment"
                    >
                    </Comment>
                </div>
                <AddComment
                    :debt="debt"
                    :user="usePage().props.auth.user"
                >
                </AddComment>
            </div>
        </div>
        <Modal :show="confirmingDebtDeletion" @close="closeModal">
            <div class="p-6 flex flex-col">
                <h2
                    class="text-lg font-medium text-gray-900"
                >
                    Are you sure you want to delete this debt?
                </h2>   
                <Form
                    class="mt-6 flex justify-end"
                    :action="route('debt.destroy')"
                    method="delete"
                    #default="{ errors }"
                    @success="closeModal"
                    :options="{
                        preserveScroll: true,
                    }"
                >
                    <div>
                        <div class="flex justify-end">
                            <button 
                                @click="closeModal"
                            >
                                Cancel
                            </button>
                            <input
                                type="hidden"
                                name="id"
                                :value="props.debt.id"
                            />
                            <button
                                class="ms-3"
                            >
                                Delete Debt
                            </button>
                        </div>
                        <InputError class="mt-2 content-end" :message="errors.id" />
                    </div>
                </Form>
            </div>
        </Modal>
    </div>
</template>
