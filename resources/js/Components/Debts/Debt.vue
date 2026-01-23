<script setup>
import { computed, onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { currencies } from '@/currencies.js';
import Share from '@/Components/Shares/Share.vue';
import Modal from '@/Components/Forms/Modal.vue';
import Comment from '@/Components/Comments/Comment.vue';
import AddComment from '@/Components/Comments/AddComment.vue';
import Controls from '@/Components/Misc/Controls.vue';
import InputError from '@/Components/Forms/InputError.vue';
import { Form } from '@inertiajs/vue3';
import DangerButton from '@/Components/Misc/DangerButton.vue';
import PrimaryButton from '@/Components/Misc/PrimaryButton.vue';
import SecondaryButton from '@/Components/Misc/SecondaryButton.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import AddShare from '@/Components/Shares/AddShare.vue';
import Collapsible from '@/Components/Misc/Collapsible.vue';

const props = defineProps({
    debt: {
        type: Object,
    },
});

const confirmingDebtDeletion = ref(false);
const isEditing = ref(false);
const showShares = ref(false);
const showComments = ref(false);

// misc
const debtCurrency = computed(() => {
    return currencies.find((currency) => currency.code === props.debt.currency)
});

const debtDiscrepancy = computed(() => {
    return props.debt.amount.amount - props.debt.shares.reduce((total, share) => total + Number(share.amount.amount), 0);
});

onMounted(() => {

});

</script>

<template>
    <div class="card">
        <!-- front facing card -->
        <div class="flex flex-row items-center">
            <div class="flex flex-col items-center">
                <p v-if="debt.comments.length > 0"class="flex flex-row" @click="showComments = !showComments">
                    <i class="fa-solid fa-comments"></i>
                    <small class="font-bold">
                        {{ debt.comments.length }}
                    </small>
                </p>
                <i 
                    class="fa-solid fa-chevron-up p-2"
                    @click="showShares = !showShares"
                    :class="showShares ? 'rotate180' : 'rotateback'"
                >
                </i>
            </div>
            <div v-if="!isEditing" class="flex flex-col w-full">
                <h2 class="h3"> 
                    {{ props.debt.name }}
                </h2>
                <h2 class="h3">
                    {{ debtCurrency.symbol }}{{ props.debt.amount.amount }}
                </h2>
                <h2 class="h3">
                    {{ props.debt.group.name }}
                </h2>
                <!-- <h3 v-if="debtDiscrepancy" class= text-red-600">
                    Discrepancy: {{ debtCurrency.symbol }}{{ debtDiscrepancy }}
                </h3> -->
                <!-- <h3 class="h4">{{ props.debt.group.name }}</h3> -->
            </div>
            <div v-else class="w-full">
                <Form
                    :action="route('debt.update', props.debt)" 
                    method="patch" 
                    #default="{ errors }"
                    @success="isEditing = false"
                    :options="{
                        preserveScroll: true,
                    }"
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
                                v-model="props.debt.name"
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
                                id="newDebtAmountLabel"
                            >
                                New Amount
                            </label>
                            <TextInput
                                v-model="props.debt.amount.amount" 
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
                        <InputError class="mt-2" :message="errors.id" />
                        <InputError class="mt-2" :message="errors.amount" />
                        <div class="flex flex-row mt-2">
                            <SecondaryButton
                                type="button"
                                class="mr-2"
                                @click="isEditing = false"
                            >
                                Cancel
                            </SecondaryButton>
                            <PrimaryButton
                                type="submit"
                            >
                                Save
                            </PrimaryButton>
                        </div>
                    </div>
                </Form>
            </div>
            <Controls
                item="Debt"
                :visible="props.debt.can.update || props.debt.can.delete"
                :updatable="props.debt.can.update"
                :deletable="props.debt.can.delete"
                class="p-2 flex flex-row justify-between"
                @edit="isEditing = !isEditing"
                @destroy="confirmingDebtDeletion = true"
            >
            </Controls>
        </div>
        <!-- shares -->
        <Collapsible v-model="showShares" class="flex-flex-col">
            <Share
                v-for="share in debt.shares"
                :share="share"
                :currency="debt.currency"
                :debt="debt"
            >
            </Share>
            <AddShare
                v-if="props.debt.can.delete && showShares"
                :debt="debt"
                :group_users="debt.group.group_users"
            >
            </AddShare>
        </Collapsible>
        <!-- comments -->
        <Collapsible v-model="showComments" >
            <div style="max-height:50vh;overflow-y:scroll;">
                <Comment
                    v-for="comment in debt.comments"
                    :comment="comment"
                >
                </Comment>
            </div>
            <AddComment
                :debt="debt"
                :user="usePage().props.auth.user"
                @closeAddComment="showComments = !showComments"
            >
            </AddComment>
        </Collapsible>
        <Modal :show="confirmingDebtDeletion" @close="confirmingDebtDeletion = false">
            <div class="p-6 flex flex-col">
                <h2
                    class="text-lg font-medium text-gray-900"
                >
                    Are you sure you want to delete this debt?
                </h2>   
                <Form
                    class="mt-6 flex flex-col justify-end"
                    :action="route('debt.destroy', props.debt)"
                    method="delete"
                    #default="{ errors }"
                    @success="confirmingDebtDeletion = false"
                    :options="{
                        preserveScroll: true,
                    }"
                >
                    <div class="flex flex-row mt-4 justify-center sm:justify-end w-full">
                        <SecondaryButton 
                            @click="confirmingDebtDeletion = false"
                        >
                            Cancel
                        </SecondaryButton>
                        <DangerButton
                        >
                            Delete
                        </DangerButton>
                    </div>
                    <InputError class="mt-2 flex sm:justify-end" :message="errors.id" />
                </Form>
            </div>
        </Modal>
    </div>
</template>
