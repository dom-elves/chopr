<script setup>
import { computed, guardReactiveProps, onMounted, onUnmounted, ref, reactive } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { currencies } from '@/currencies.js';
import Share from '@/Components/Shares/Share.vue';
import Modal from '@/Components/Modal.vue';
import Comment from '@/Components/Comments/Comment.vue';
import Controls from '@/Components/Controls.vue';

const props = defineProps({
    debt: {
        type: Object,
    },
});
const confirmingDebtDeletion = ref(false);
const showShares = ref(false);
const showComments = ref(false);
const isEditing = ref(false);

// deletion
const debtForm = useForm({
    id: props.debt.id,
    name: props.debt.name,
    amount: props.debt.amount,
    owner_group_user_id: props.debt.collector_group_user_id,
});


const debtFormErrors = reactive({
    owner_group_user_id: null,
});

function updateDebt() {
    debtForm.patch(route('debt.update'), {
        preserveScroll: true,
        onSuccess: () => {
            isEditing = !isEditing;
        }
    });
}

function deleteDebt() {
    debtForm.delete(route('debt.destroy'), {
        preserveScroll: true,
        onError: (error) => {
            console.log(error);
            debtFormErrors.owner_group_user_id = error.owner_group_user_id;
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
    console.log(commentForm);
    commentForm.post(route('comment.store'), {  
        preserveScroll: true, 
        onSuccess: () => {
            commentForm.reset('content');
        },
    });
}

const debtCurrency = computed(() => {
    return currencies.find((currency) => currency.code === props.debt.currency)
});

const closeModal = () => {
    confirmingDebtDeletion.value = false;
};

onMounted(() => {
    console.log(props.debt);
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
                            @blur="router.patch(route('debt.update', debtForm))"
                        >
                    </div>
                    </div>
                </form>
            </div>
            <div
                v-if="usePage().props.ownership.debt_ids.includes(props.debt.id)"
                class="p-2 flex flex-row justify-between"
            >
                <Controls
                    item="Debt"
                    @editDebt="isEditing = !isEditing"
                    @deleteDebt="confirmingDebtDeletion = true"
                >
                </Controls>
            </div>
        </div>
        <div class="p-2 md:grid-cols-2 lg:flex lg:flex-row lg:justify-evenly" v-show="showShares">
            <Share
                v-for="share in debt.shares"
                :share="share"
                :currency="debt.currency"
                :debt-owner-id="debt.collector_group_user_id"
            >
            </Share>
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
                <p class="text-red-500" v-if="debtFormErrors.id">
                        {{ debtFormErrors.id }}
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
