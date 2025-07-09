<script setup>

import { computed, onMounted, onUnmounted, ref, reactive } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import GroupUser from '@/Components/GroupUsers/GroupUser.vue';
import SearchUser from '@/Components/Users/SearchUser.vue';
import Modal from '@/Components/Modal.vue';
import Controls from '@/Components/Controls.vue';
import InputError from '@/Components/InputError.vue';
import InviteToGroup from '@/Components/InviteToGroup.vue';

const props = defineProps({
    group: {
        type: Object,
    },
});
const owns_group = ref(usePage().props.ownership.group_ids.includes(props.group.id));
const showGroupUsers = ref(false);
const isEditing = ref(false);
const confirmingGroupDeletion = ref(false);

const updateGroupForm = useForm({
    id: props.group.id,
    name: props.group.name,
    user_id: props.group.user_id,
});

function updateGroup() {
    updateGroupForm.patch(route('group.update'), {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = !isEditing.value;
        },
        onError: (error) => {
            updateGroupForm.errors.id = error.user_id;
        },
    })
}

function deleteGroup() {
    updateGroupForm.delete(route('group.destroy', { 
        group_id: props.group.id, 
        user_id: props.group.user_id 
    }), {
        onError: (error) => {
            updateGroupForm.user_id = error.user_id;
        },
    })
}

const closeModal = () => {
    confirmingGroupDeletion.value = false;
};

</script>

<template>
    <div>
        <div 
            class="p-4 m-2 border-solid border-2 border-indigo-600"
        >
            <div class="flex flex-row items-center">
                <i 
                    class="fa-solid fa-chevron-up p-2"
                    @click="showGroupUsers = !showGroupUsers"
                    :class="showGroupUsers ? 'rotate180' : 'rotateback'"
                >
                </i>
                <p class="p-2 text-xl w-full text-center w-full" v-if="!isEditing"> 
                    {{ group.name }}
                </p>
                <div v-else>
                    <form @submit.prevent>
                        <div class="flex flex-col">
                            <label 
                                for="newgroupName" 
                                style="display:none;"
                                id="newgroupNameLabel"
                            >
                            New Name
                            </label>
                            <input
                                type="text"
                                id="newgroupName"
                                aria-labelledby="newgroupNameLabel"
                                v-model="updateGroupForm.name"
                                @blur="updateGroup"
                            >
                            <InputError class="mt-2" :message="updateGroupForm.errors.id" />
                        </div>
                    </form>
                </div>
                <Controls
                    v-if="owns_group"
                    class="p-2 flex flex-row justify-between"
                    item="Group"
                    @edit="isEditing = !isEditing"
                    @destroy="confirmingGroupDeletion = true"
                >
                </Controls>
            </div>
            <InviteToGroup></InviteToGroup>
            <div v-show="showGroupUsers" class="flex flex-col">
                <GroupUser 
                    v-for="group_user in group.group_users"
                    :group_user="group_user"
                    :owns_group="owns_group"
                    :group="group"
                >
                </GroupUser>
                <!-- <SearchUser
                    v-if="owns_group"
                    :group_id="props.group.id"
                >
                </SearchUser> -->
            </div>
            <Modal :show="confirmingGroupDeletion" @close="closeModal">
                <div class="p-6">
                    <h2
                        class="text-lg font-medium text-gray-900"
                    >
                        Are you sure you want to delete this group?
                    </h2>
                    <InputError class="mt-2" :message="updateGroupForm.errors.id" />
                    <div class="mt-6 flex justify-end">
                        <button 
                            @click="closeModal"
                        >
                            Cancel
                        </button>
                        <button
                            class="ms-3"
                            @click="deleteGroup"
                        >
                            Delete Group
                        </button>
                    </div>
                </div>
            </Modal>
        </div>
    </div>
</template>
