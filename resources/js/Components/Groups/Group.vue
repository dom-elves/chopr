<script setup>

import { computed, onMounted, onUnmounted, ref, reactive } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import GroupUser from '@/Components/GroupUsers/GroupUser.vue';
import SearchUser from '@/Components/Users/SearchUser.vue';
import Modal from '@/Components/Modal.vue';
import Controls from '@/Components/Controls.vue';
import InputError from '@/Components/InputError.vue';
import InviteToGroup from '@/Components/InviteToGroup.vue';
import { Form } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    group: {
        type: Object,
    },
});

const owns_group = ref(usePage().props.ownership.group_ids.includes(props.group.id));
const showGroupUsers = ref(false);
const isEditing = ref(false);
const confirmingGroupDeletion = ref(false);

</script>

<template>
    <div class="card">
        <div class="flex flex-row items-center">
            <i 
                class="fa-solid fa-chevron-up p-2"
                @click="showGroupUsers = !showGroupUsers"
                :class="showGroupUsers ? 'rotate180' : 'rotateback'"
            >
            </i>
            <h2 v-if="!isEditing" class="h2 p-2 w-full"> 
                {{ group.name }}
            </h2>
            <div v-else class="w-full">
                <Form
                    :action="route('group.update')" 
                    method="patch" 
                    #default="{ errors }"
                    :transform="data => ({
                        ...data,
                        id: props.group.id, 
                        user_id: usePage().props.auth.user.id 
                    })"
                    @success="isEditing = false"
                >
                    <div class="flex flex-col">
                        <label 
                            for="newgroupName" 
                            style="display:none;"
                            id="newgroupNameLabel"
                        >
                        New Name
                        </label>
                        <TextInput
                            name="name"
                            type="text"
                            id="newgroupName"
                            aria-labelledby="newgroupNameLabel"
                            placeholder="Enter a new group name..."
                            class="w-full mr-2"
                            style="height:48px"
                        />
                        <InputError class="mt-2" :message="errors.name" />
                        <div class="flex flex-row mt-2 justify-between sm:justify-end">
                            <SecondaryButton
                                type="button"
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
                :class="owns_group && !isEditing ? '' : 'invisible'"
                class="p-2 flex flex-row justify-between"
                item="Group"
                @edit="isEditing = !isEditing"
                @destroy="confirmingGroupDeletion = true"
            >
            </Controls>
        </div>
        <div v-show="showGroupUsers" class="flex flex-col">
            <GroupUser 
                v-for="group_user in group.group_users"
                :group_user="group_user"
                :owns_group="owns_group"
                :group="group"
            >
            </GroupUser>
            <InviteToGroup
                v-if="owns_group"
                :group="group"
            >
            </InviteToGroup>
        </div>
        <Modal :show="confirmingGroupDeletion" @close="confirmingGroupDeletion = false;">
            <div class="p-6 flex flex-col">
                <h2
                    class="text-lg font-medium text-gray-900"
                >
                    Are you sure you want to delete the group, "<i>{{ props.group.name}}</i>"?
                </h2>   
                <Form
                    class="mt-6 flex justify-end"
                    :action="route('group.destroy')"
                    method="delete"
                    #default="{ errors }"
                    @success="confirmingGroupDeletion = false;"
                    :options="{
                        preserveScroll: true,
                    }"
                >
                    <div class="flex flex-row mt-4 justify-center sm:justify-end w-full">
                        <SecondaryButton 
                            @click="confirmingGroupDeletion = false;"
                        >
                            Cancel
                        </SecondaryButton>
                        <input
                            type="hidden"
                            name="id"
                            :value="props.group.id"
                        />
                        <DangerButton
                        >
                            Delete Group
                        </DangerButton>
                    </div>
                    <InputError class="mt-2 content-end" :message="errors.id" />
                </Form>
            </div>
        </Modal>
    </div>
</template>
