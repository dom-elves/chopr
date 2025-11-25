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

function setSentSeenMessage(message) {
    sentSeenMessage.value = message[0];
    refresh & refresh();
}

onMounted(() => {
    console.log('share', props.errors);
})

</script>

<template>
    <div class="plate flex flex-col">
        <div v-if="!isEditing" class="flex flex-row justify-between w-full">
            <!-- group user, share name, amount -->
            <div class="flex flex-col">
                <p class="font-semibold text-lg mr-2">{{ share.group_user.user.name }}</p>
                <p>{{ debtCurrency.symbol }}{{ share.amount.amount }}</p>
                <p>{{ share.name ? share.name : ' ' }}</p>
            </div>
            <!-- sent/seen/controls/owner badge container -->
            <div class="flex flex-row items-center" >
                <!-- sent & seen -->
                <div v-if="props.share.user_id !== props.debt.user_id" class="flex flex-row items-center">
                    <SentSeenButton
                        operation="sent"
                        type="submit"
                        :share="share"
                        @sentError="setSentSeenMessage($event)"
                    />
                    <SentSeenButton
                        operation="seen"
                        type="submit"
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
                    :visible="props.share.can_update_name || props.share.can_delete"
                    :updatable="props.share.can_update_name"
                    :deletable="props.share.can_delete"
                    @edit="isEditing = !isEditing;refresh & refresh()"
                    @destroy="confirmingShareDeletion = true"
                >
                </Controls>
            </div>
        </div>
        <!-- editing form -->
        <div v-else class="w-full">
            <Form
                :action="route('share.update', props.share)" 
                method="patch" 
                #default="{ errors }"
                :transform="data => ({
                    ...data,
                    id: props.share.id,
                    debt_id: props.debt.id, 
                })"
                @success="isEditing = false"
                :options="{
                    preserveScroll: true,
                }"
            >
                <div class="flex flex-col">
                    <div v-if="props.share.can_update_name" class="flex flex-row">
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
                    <div v-if="props.share.can_update_amount" class="flex flex-row">
                        <label 
                            for="ShareAmount"
                            style="display:none;"
                            id="newShareAmountLabel
                        ">
                        New Amount
                        </label>
                        <TextInput 
                            name="amount"
                            v-model="props.share.amount.amount"
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
                    :transform="data => ({ 
                        ...data, 
                        debt_id: props.share.debt_id,
                    })"
                >
                    <div class="flex flex-row mt-4 justify-center sm:justify-end w-full">
                        <SecondaryButton 
                            @click="confirmingShareDeletion = false;"
                        >
                            Cancel
                        </SecondaryButton>
                        <input
                            type="hidden"
                            name="id"
                            :value="props.share.id"
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
