<script setup>
import { computed, onMounted, ref, watch, reactive, inject } from 'vue';
import { currencies } from '@/currencies.js';
import { router, useForm, usePage } from '@inertiajs/vue3';
import Controls from '@/Components/Misc/Controls.vue';
import Modal from '@/Components/Forms/Modal.vue';
import InputError from '@/Components/Forms/InputError.vue';
import { Form } from '@inertiajs/vue3';
import DangerButton from '@/Components/Misc/DangerButton.vue';
import PrimaryButton from '@/Components/Misc/PrimaryButton.vue';
import SecondaryButton from '@/Components/Misc/SecondaryButton.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import SentSeenButton from '@/Components/Shares/SentSeenButton.vue';

const props = defineProps({
    share: {
        type: Object,
    },
    currency: {
        type: String,
    },
    debt: {
        type: Object,
    },
});


const confirmingShareDeletion = ref(false);
const isEditing = ref(false);
const refresh = inject('collapsibleRefresh');
const sentSeenMessage = ref(null);

// todo: figure out a way to stop having to use this function in multiple places
const debtCurrency = computed(() => {
    return currencies.find((currency) => currency.code === props.currency)
});

/**
 * Whether or not a user can make changes to a share is set by SharePolicy.
 * This is just to determine whether or not to show the controls to the user.
 * It's mostly the same, just essentially when a share is sent & seen it's finally "locked"
 */
const showShareControls = computed(() => {
    switch (true) {
        case props.share.can.update_name:
        case props.share.can.update_amount:
        case props.share.can.delete:
        case props.share.sent && props.share.seen:
            return true;
        default:
            return false;
    }
})

function setSentSeenMessage(message) {
    sentSeenMessage.value = message.sent ?? message.seen;
    refresh & refresh();
}

onMounted(() => {

})

</script>

<template>
    <div class="plate flex flex-col">
        <div v-if="!isEditing" class="flex flex-row justify-between w-full">
            <!-- group user, share name, amount -->
            <div class="flex flex-col">
                <p class="font-semibold text-lg mr-2">{{ share.group_user.user.name }}</p>
                <p>{{ share.name ? share.name : ' ' }}</p>
                <p>{{ debtCurrency.symbol }}{{ share.amount.amount }}</p>
            </div>
            <!-- sent/seen/controls/owner badge container -->
            <div class="flex flex-row items-center" >
                <!-- sent & seen -->
                <div v-if="props.share.group_user.id !== props.debt.group_user_id" class="flex flex-row items-center">
                    <SentSeenButton
                        operation="sent"
                        :share="share"
                        @sentError="setSentSeenMessage($event)"
                    />
                    <SentSeenButton
                        operation="seen"
                        :share="share"
                        @seenError="setSentSeenMessage($event)"
                    />
                </div>
                <!-- owner badge -->
                <div v-else class="flex flex-col items-center" style="width:103px">
                    <p class="text-xs font-semibold bg-black text-white p-1 border rounded">
                        OWNER
                    </p>
                </div>
                <Controls
                    item="Share"
                    :key="props.share.id"
                    :visible="showShareControls"
                    :updatable="props.share.can.update_name"
                    :deletable="props.share.can.delete"
                    @edit="isEditing = !isEditing;refresh & refresh()"
                    @destroy="confirmingShareDeletion = true"
                >
                </Controls>
            </div>
        </div>
        <!-- editing form -->
        <Form
            v-else
            class="w-full"
            :action="route('share.update', props.share)" 
            method="patch" 
            #default="{ errors }"
            @success="isEditing = false"
            :options="{
                preserveScroll: true,
            }"
            :transform="data => ({
                ...data,
                // same as in Debt, needs minor units
                amount: data.amount * 100,
            })"
        >
            <div class="flex flex-col">
                <div v-if="props.share.can.update_name" class="flex flex-row">
                    <label 
                        for="newShareName" 
                        style="display:none;"
                        id="newShareNameLabel"
                    >
                    New Name
                    </label>
                    <TextInput
                        name="name"
                        v-model="props.share.name"
                        type="text"
                        id="newShareName"
                        aria-labelledby="newShareNameLabel"
                        placeholder="Enter a new share name..."
                        class="w-full"
                        style="height:48px"
                    />
                </div>
                <InputError class="mt-2" :message="errors.name" />
                <div v-if="props.share.can.update_amount && !props.debt.split_even" class="flex flex-row">
                    <label 
                        for="ShareAmount"
                        style="display:none;"
                        id="newShareAmountLabel
                    ">
                    New Amount
                    </label>
                    <input
                        name="amount"
                        v-model="props.share.amount"
                        type="number"
                        step="0.01"
                        id="newShareAmount"
                        placeholder="Enter a new amount..."
                        aria-labelledby="newShareAmountLabel"
                        class="w-full mt-2"
                        style="height:48px"
                    />
                </div>
                <InputError class="mt-2" :message="errors.amount" />
                <div class="flex flex-row mt-2 sm:justify-end">
                    <SecondaryButton
                        type="button"
                        class="mr-2"
                        @click="isEditing = false;refresh & refresh()"
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
        <InputError v-if="sentSeenMessage" class="mt-2" :message="sentSeenMessage" />
        <Modal :show="confirmingShareDeletion">
            <div class="p-6">
                <h2
                    class="text-lg font-medium text-gray-900"
                >
                    Are you sure you want to delete this Share?
                </h2>
                <Form
                    class="mt-6 flex justify-end"
                    :action="route('share.destroy', props.share)"
                    method="delete"
                    #default="{ errors }"
                    @success="confirmingShareDeletion = false;refresh & refresh()"
                    :options="{
                        preserveScroll: true,
                    }"
                >
                    <div class="flex flex-row mt-4 justify-center sm:justify-end w-full">
                        <SecondaryButton 
                            @click="confirmingShareDeletion = false;"
                        >
                            Cancel
                        </SecondaryButton>
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
