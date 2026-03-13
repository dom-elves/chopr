<script setup>

import { computed, onMounted, onUnmounted, ref, reactive, inject } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Forms/Modal.vue';
import { Form } from '@inertiajs/vue3';
import InputError from '@/Components/Forms/InputError.vue';
import Controls from '@/Components/Misc/Controls.vue';
import PrimaryButton from '@/Components/Misc/PrimaryButton.vue';
import SecondaryButton from '@/Components/Misc/SecondaryButton.vue';
import DangerButton from '@/Components/Misc/DangerButton.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import UserProfileIcon from '../Users/UserProfileIcon.vue';
import UserPicker from '@/Components/Forms/UserPicker.vue';

const props = defineProps({
    group_user: {
        type: Object,
    },

    group: {
        type: Object,
    }
});

const refresh = inject('collapsibleRefresh');
const confirmingGroupUserDeletion = ref(false);
const isEditing = ref(false);
const newOwner = ref(null);

/**
 * Initially, all aliases are loaded with the user, so the correct one needs to be
 * paired to the user as a computed property, based on who is logged in.
 */
const alias = computed({
    get() {
        return props.group_user.aliases.find(
            alias => alias.user_id === Number(usePage().props.auth.user.id)
        ) || { alias: '' };
    },
    set(value) {
        this.alias = value;
    }
});


function setGroupOwner(userId) {
    newOwner.value = userId;
    console.log(newOwner.value);
}

onMounted(() => {

})
</script>

<template>
    <div class="flex flex-row plate justify-between" style="width:inherit">
        <!-- don't understand why this squashes without a wrapper div -->
        <div :class="!isEditing ? 'visible' : 'invisible'" style="width:50px">
            <UserProfileIcon />
        </div>
        <div v-if="!isEditing" class="flex flex-col items-center w-full">
            <h3 class="text-xl text-center font-semibold">
                {{ group_user.user.name }}
            </h3>
            <small>{{ alias ? alias.alias : '' }}</small>
        </div>
        <div v-else class="flex flex-col w-full">
            <Form
                :action="alias.id
                    ? route('alias.update', alias.id) 
                    : route('alias.store')
                " 
                :method="alias.id? 'patch' : 'post'"
                #default="{ errors }"
                @success="isEditing = false;refresh & refresh()"
                :options="{
                        preserveScroll: true,
                    }"
                :transform="data => ({
                    user_id: usePage().props.auth.user.id, 
                    group_user_id: props.group_user.id,
                    ...data,
                })"
            >
                <div class="flex flex-col">
                    <label 
                        for="newGroupUserAlias" 
                        style="display:none;"
                        id="newGroupUserAliasLabel"
                    >
                    New Alias
                    </label>
                    <TextInput
                        v-model="alias.alias"
                        name="alias"
                        type="text"
                        id="newGroupUserAlias"
                        aria-labelledby="newGroupUserAliasLabel"
                        placeholder="Enter an alias for this group user"
                        class="w-full mr-2"
                        style="height:48px"
                    />
                    <small class="mt-2 text-gray-600">Group User Aliases are unique to you. No one else will be able to see the alias you have assigned to this group user.</small>
                    <InputError class="mt-2 text-center lg:text-left" :message="errors.alias" />
                    <div class="flex flex-row mt-4 justify-center sm:justify-end w-full">
                        <SecondaryButton
                            type="button"
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
        </div>
        <div class="flex justify-end" style="width:50px">
            <Controls
                item="Group User"
                :visible="true"
                :updatable="true"
                :deletable="props.group_user.can.delete"
                @edit="isEditing = !isEditing;refresh & refresh()"
                @destroy="confirmingGroupUserDeletion = true;refresh & refresh()"
            >
            </Controls>
        </div>
        <Modal :show="confirmingGroupUserDeletion" @close="confirmingGroupUserDeletion = false">
            <div class="p-6 flex flex-col">
                <h2 class="text-lg font-medium text-gray-900 mb-2">
                    Are you sure you want remove {{ usePage().props.auth.user.id === props.group_user.user_id ? 'yourself' : group_user.user.name }} from "<i>{{ group.name }}</i>"? Deleting this user will remove their debts & shares.
                </h2>
                <!-- pass in all users but self, feels silly to set it as a variable when it's not always used -->
                <UserPicker
                    v-if="props.group.can.delete && usePage().props.auth.user.id === props.group_user.user_id"
                    :group_users="props.group.group_users.filter((group_user) => group_user.user_id !== usePage().props.auth.user.id)"
                    label="You are the owner of this group, please select a new user to be the owner:"
                    @userSelected="setGroupOwner"
                >
                </UserPicker>
                <Form
                    class="mt-6 flex flex-col justify-end"
                    :action="route('group-users.destroy', props.group_user)"
                    method="delete"
                    #default="{ errors }"
                    :transform="data => ({
                        ...data,
                        new_owner: newOwner,
                    })"
                    @success="confirmingGroupUserDeletion = false;refresh & refresh();newOwner.value = null"
                    :options="{
                        preserveScroll: true,
                    }"
                >
                    <div class="flex flex-row mt-4 justify-center sm:justify-end w-full">
                        <SecondaryButton 
                            @click="confirmingGroupUserDeletion = false"
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