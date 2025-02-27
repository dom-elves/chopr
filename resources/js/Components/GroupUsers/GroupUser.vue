<script setup>

import { computed, onMounted, onUnmounted, ref, reactive } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    group_user: {
        type: Object,
    },
    owns_group: {
        type: Boolean,
    },
    group: {
        type: Object,
    }
});

const confirmingGroupUserDeletion = ref(false);

const confirmGroupUserDeletion = () => {
    confirmingGroupUserDeletion.value = true;
};

const closeModal = () => {
    confirmingGroupUserDeletion.value = false;
};

function deleteGroupUser() {
    router.delete(route('group-users.destroy', { 
        group_user_id: props.group_user.id,
    }), {
        onError: (error) => {
            
        },
    })
}

onMounted(() => {
    console.log(usePage().props);
})
</script>

<template>
    <div class="my-2 border-solid border-2 border-amber-600 flex flex-row items-center p-2">
        <p class="p-2 text-xl w-full text-center w-full">
            {{ group_user.user.name }}
        </p>
        <i 
            v-if="owns_group && group_user.user_id !== usePage().props.auth.user.id"
            class="fa-solid fa-x mx-1"
            @click="confirmGroupUserDeletion"
        >
        </i>
        <Modal :show="confirmingGroupUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2
                    class="text-lg font-medium text-gray-900"
                >
                    Are you sure you want remove {{  group_user.user.name }} from "{{ group.name }}"?
                </h2>
                <div class="mt-6 flex justify-end">
                    <button 
                        @click="closeModal"
                    >
                        Cancel
                    </button>
                    <button
                        class="ms-3"
                        @click="deleteGroupUser"
                    >
                        Delete GroupUser
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>