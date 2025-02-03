<script setup>

import { computed, onMounted, onUnmounted, ref, reactive } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import GroupUser from '@/Components/GroupUsers/GroupUser.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    group: {
        type: Object,
    },
});

const showGroupUsers = ref(false);
const isEditing = ref(false);
const confirmingGroupDeletion = ref(false);

const form = useForm({
    group_id: props.group.id,
    name: props.group.name,
    owner_id: props.group.owner_id,
});

const formErrors = reactive({
    owner_id: null,
});

function updateForm() {
    form.patch(route('group.update'), {
        onError: (error) => {
            console.log(error);
            formErrors.owner_id = error.owner_id;
        },
    })
}

const confirmGroupDeletion = () => {
    confirmingGroupDeletion.value = true;
};

const closeModal = () => {
    confirmingGroupDeletion.value = false;
};

onMounted(() => console.log('form', form));

</script>

<template>
    <div>
        <!-- 
            add group component (with email invite)
            loop over groups to view
                group user component
                maybe into user component?
        -->
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
                            {{  group.name }}
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
                                        v-model="form.name"
                                        @blur="updateForm"
                                    >
                                    <p class="text-red-500" v-if="formErrors.owner_id">
                                        {{ formErrors.owner_id }}
                                    </p>
                                </div>
                            </form>
                        </div>
                        <div class="p-2 flex flex-row justify-between">
                            <i 
                                class="fa-solid fa-gear mx-1"
                                @click="isEditing = !isEditing"
                            >
                            </i>
                            <i 
                                class="fa-solid fa-x mx-1"
                                @click="confirmGroupDeletion"
                            >
                            </i>
                        </div>
                    </div>
                    <!-- group users go here-->
                    <Modal :show="confirmingGroupDeletion" @close="closeModal">
                        <div class="p-6">
                            <h2
                                class="text-lg font-medium text-gray-900"
                            >
                                Are you sure you want to delete this group?
                            </h2>
                            <div class="mt-6 flex justify-end">
                                <button 
                                    @click="closeModal"
                                >
                                    Cancel
                                </button>
                                <button
                                    class="ms-3"
                                    @click="router.delete(route('group.destroy', { group_id: group.id }));"
                                >
                                    Delete Group
                                </button>
                            </div>
                        </div>
                    </Modal>
                </div>
                    <!-- <h3 class="text-3xl text-center mb-4">
                        {{ group.name }}
                    </h3>
                    <GroupUser
                        v-for="group_user in group.group_users"
                        :group_user="group_user"
                        >
                    </GroupUser> -->

        </div>
</template>