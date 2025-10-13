<script setup>
import { computed, onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { currencies } from '@/currencies.js';
import Share from '@/Components/Shares/Share.vue';
import Modal from '@/Components/Modal.vue';
import Comment from '@/Components/Comments/Comment.vue';
import AddComment from '@/Components/Comments/AddComment.vue';
import Controls from '@/Components/Controls.vue';
import InputError from '@/Components/InputError.vue';
import { Form } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    debt: {
        type: Object,
    },
});

const confirmingDebtDeletion = ref(false);
const showShares = ref(false);
const showComments = ref(false);
const isEditing = ref(false);
// if the logged in user owners the debt, display the controls
const owns_debt = usePage().props.auth.user.id === props.debt.user_id ? true : false;

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
    console.log(props.debt.group.name);
    // if (debtDiscrepancy.value != props.debt.amount.amount) {
    //     const discrepancy = props.debt.amount.amount - debtDiscrepancy.value;
    //     debtForm.errors.amount = `There is a discrepancy of ${debtCurrency.value.symbol}${discrepancy.toFixed(2)}.`;
    // }
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
            <div v-if="!isEditing" class="flex flex-col w-full">
                <h2 class="h3 bold text-center"> 
                    {{ props.debt.name }}
                </h2>
                <h2 class="h3 text-center">
                    {{ debtCurrency.symbol }}{{ props.debt.amount.amount }}
                </h2>
                <h3 class="h4 text-center">{{ props.debt.group.name }}</h3>
            </div>
            <div v-else class="w-full">
                <Form
                    :action="route('debt.update')" 
                    method="patch" 
                    #default="{ errors }"
                    :transform="data => ({
                        ...data,
                        id: props.debt.id, 
                    })"
                    @success="isEditing = false"
                >
                    <div class="flex flex-col">
                        <div class="flex flex-row">
                            <label 
                                for="newDebtName" 
                                style="display:none;"
                                id="newDebtNameLabel"
                            >
                            New Name
                            </label>
                            <TextInput
                                name="name"
                                type="text"
                                id="newDebtName"
                                aria-labelledby="newDebtNameLabel"
                                placeholder="Enter a new debt name..."
                                class="w-full"
                                style="height:48px"
                            />
                        </div>
                        <InputError class="mt-2" :message="errors.name" />
                        <div class="flex flex-row">
                            <label 
                                for="debtAmount"
                                style="display:none;"
                                id="newDebtAmountLabel
                            ">
                            New Amount
                            </label>
                            <TextInput 
                                name="amount"
                                type="number"
                                step="0.01"
                                id="newDebtAmount"
                                placeholder="Enter a new amount..."
                                aria-labelledby="newDebtAmountLabel"
                                class="w-full mt-2"
                                style="height:48px"
                            />
                        </div>
                        <InputError class="mt-2" :message="errors.amount" />
                        <div class="flex flex-row mt-2">
                            <SecondaryButton
                                type="button"
                                class="w-1/2 justify-center mr-2"
                                @click="isEditing = false"
                            >
                                Cancel
                            </SecondaryButton>
                            <PrimaryButton
                                type="submit"
                                class="w-1/2 justify-center"
                            >
                                Save
                            </PrimaryButton>
                        </div>
                    </div>
                </Form>
            </div>
            <Controls
                :class="owns_debt && !isEditing ? '' : 'invisible'"
                item="Debt"
                class="p-2 flex flex-row justify-between"
                @edit="isEditing = !isEditing"
                @destroy="confirmingDebtDeletion = true"
            >
            </Controls>
        </div>
        <!-- <InputError class="mt-2" :message="debtForm.errors.amount" /> -->
        <div class="flex flex-col" v-show="showShares">
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
                   
                    <div class="flex flex-row mt-4 justify-center sm:justify-end w-full">
                        <SecondaryButton 
                            @click="confirmingDebtDeletion = false;"
                        >
                            Cancel
                        </SecondaryButton>
                        <input
                            type="hidden"
                            name="id"
                            :value="props.debt.id"
                        />
                        <DangerButton
                        >
                            Delete
                        </DangerButton>
                        <InputError class="mt-2 content-end" :message="errors.id" />
                    </div>
                </Form>
            </div>
        </Modal>
    </div>
</template>
