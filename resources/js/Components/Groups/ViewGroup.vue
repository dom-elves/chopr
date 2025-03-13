<script setup>

import { computed, onMounted, onUnmounted, ref, reactive } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import GroupUser from '@/Components/GroupUsers/GroupUser.vue';
import SearchUser from '@/Components/Users/SearchUser.vue';
import Modal from '@/Components/Modal.vue';
import Controls from '@/Components/Controls.vue';

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
    group_id: props.group.id,
    name: props.group.name,
    owner_id: props.group.owner_id,
});

const updateGroupFormErrors = reactive({
    owner_id: null,
});

function updateGroup() {
    updateGroupForm.patch(route('group.update'), {
        onError: (error) => {
            updateGroupFormErrors.owner_id = error.owner_id;
        },
    })
}

function deleteGroup() {
    router.delete(route('group.destroy', { 
        group_id: props.group.id, 
        owner_id: props.group.owner_id 
    }), {
        onError: (error) => {
            updateGroupFormErrors.owner_id = error.owner_id;
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
                            <p class="text-red-500" v-if="updateGroupFormErrors.owner_id">
                                {{ updateGroupFormErrors.owner_id }}
                            </p>
                        </div>
                    </form>
                </div>
                <div class="p-2 flex flex-row justify-between" v-if="owns_group">
                    <Controls
                        item="Group"
                        @editItem="isEditing = !isEditing"
                        @deleteItem="confirmingGroupDeletion = true"
                    >
                    </Controls>
                </div>
            </div>
            <div v-show="showGroupUsers" class="flex flex-col">
                <GroupUser 
                    v-for="group_user in group.group_users"
                    :group_user="group_user"
                    :owns_group="owns_group"
                    :group="group"
                >
                </GroupUser>
                <SearchUser
                    v-if="owns_group"
                    :group_id="props.group.id"
                >
                </SearchUser>
            </div>
            <Modal :show="confirmingGroupDeletion" @close="closeModal">
                <div class="p-6">
                    <h2
                        class="text-lg font-medium text-gray-900"
                    >
                        Are you sure you want to delete this group?
                    </h2>
                    <p class="text-red-500" v-if="updateGroupFormErrors.owner_id">
                        {{ updateGroupFormErrors.owner_id }}
                    </p>
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
