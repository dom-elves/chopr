<script setup>
import { computed, guardReactiveProps, onMounted, onUnmounted, ref, reactive, nextTick } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { currencies } from '@/currencies.js';
import Share from '@/Components/Shares/Share.vue';
import AddShare from '@/Components/Shares/AddShare.vue';
import Modal from '@/Components/Modal.vue';
import Comment from '@/Components/Comments/Comment.vue';
import Controls from '@/Components/Controls.vue';
import InputError from '@/Components/InputError.vue';

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
const displayControls = usePage().props.auth.user.id === props.debt.user_id ? true : false;

// debt update
const debtForm = useForm({
    id: props.debt.id,
    name: props.debt.name,
    amount: props.debt.amount,
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

// debt deletion
const deleteDebtForm = useForm({
    id: props.debt.id,
});

function deleteDebt() {
    deleteDebtForm.delete(route('debt.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingDebtDeletion.value = false;
        },
        onError: (error) => {

        },
    });
}

// comments
const commentForm = useForm({
    debt_id: props.debt.id,
    content: '',
    user_id: usePage().props.auth.user.id,
});

function postComment() {
    commentForm.post(route('comment.store'), {  
        preserveScroll: true, 
        onSuccess: () => {
            commentForm.reset('content');
        },
        onError: (error) => {

        }
    });
}

// misc
const debtCurrency = computed(() => {
    return currencies.find((currency) => currency.code === props.debt.currency)
});

const debtDiscrepancy = computed(() => {
    return props.debt.shares.reduce((total, share) => total + share.amount, 0).toFixed(2);
});

const closeModal = () => {
    confirmingDebtDeletion.value = false;
};

onMounted(() => {
    if (debtDiscrepancy.value != props.debt.amount) {
        const discrepancy = props.debt.amount - debtDiscrepancy.value;
        debtForm.errors.amount = `There is a discrepancy of ${debtCurrency.value.symbol}${discrepancy.toFixed(2)}.`;
    }
});

</script>

<template>
    <div class="my-2 border-solid border-2 border-amber-600">
        <div class="flex flex-row">
            <div class="flex flex-row items-center w-full">
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
            </div>
            <Controls
     
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
            <AddShare
                v-if="displayControls"
                :debt="debt"
                :group_users="group.group_users"
            >
            </AddShare>
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
                <form>
                    <label for="post-a-comment" class="hidden">Post a comment</label>
                    <textarea 
                        id="post-a-comment" 
                        name="comment"
                        class="w-full"
                        placeholder="Post a comment..."
                        v-model="commentForm.content"
                        @keydown.enter.prevent="postComment"
                    >
                    </textarea>
                </form>
            </div>
        </div>
        <Modal :show="confirmingDebtDeletion" @close="closeModal">
            <div class="p-6">
                <h2
                    class="text-lg font-medium text-gray-900"
                >
                    Are you sure you want to delete this debt?
                </h2>
                <InputError class="mt-2" :message="deleteDebtForm.errors.id" />
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
