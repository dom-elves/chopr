<script setup>

import { computed, onMounted, onUnmounted, ref, reactive } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { Form } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import Controls from '@/Components/Controls.vue';

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
const isEditing = ref(false);

onMounted(() => {

})
</script>

<template>
    <div class="my-2 flex flex-row w-full p-2 plate">
        <svg width="50" height="50" xmlns="http://www.w3.org/2000/svg">
            <circle cx="25" cy="25" r="20" stroke="green" stroke-width="4" fill="yellow" />
        </svg>
        <div class="flex flex-col">
            <p class="p-2 text-xl w-full text-center">
                {{ group_user.user.name }}
            </p>
            <small>placeholder for group user alias</small>
        </div>
        <Controls
            v-if="owns_group"
            item="Group User"
            @edit="isEditing = !isEditing"
            @destroy="confirmingGroupUserDeletion = true"
        >
        </Controls>
        <Modal :show="confirmingGroupUserDeletion">
            <div class="p-6">
                <h2
                    class="text-lg font-medium text-gray-900"
                >
                    Are you sure you want remove {{  group_user.user.name }} from "{{ group.name }}"?
                </h2>
                <Form
                    class="mt-6 flex justify-end"
                    :action="route('group-users.destroy')"
                    method="delete"
                    #default="{ errors }"
                    @success="confirmingGroupUserDeletion = false"
                    :options="{
                        preserveScroll: true,
                    }"
                    :transform="data => ({ 
                        ...data, 
                        group_user_id: props.group_user.id,
                    })"
                >
                    <div>
                        <div class="flex justify-end">
                            <button
                                type="button"
                                @click="confirmingGroupUserDeletion = false"
                            >
                                Cancel
                            </button>
                            <input
                                type="hidden"
                                name="group_id"
                                :value="props.group.id"
                            />
                            <button
                                class="ms-3"
                            >
                                Delete Group User
                            </button>
                        </div>
                        <InputError class="mt-2 content-end" :message="errors.id" />
                    </div>
                </Form>
            </div>
        </Modal>
    </div>
</template>