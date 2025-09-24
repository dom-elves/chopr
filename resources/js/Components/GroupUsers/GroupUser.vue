<script setup>

import { computed, onMounted, onUnmounted, ref, reactive } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { Form } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

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

onMounted(() => {

})
</script>

<template>
    <div class="my-2 border-solid border-2 border-amber-600 flex flex-row items-center p-2">
        <p class="p-2 text-xl w-full text-center w-full">
            {{ group_user.user.name }}
        </p>
        <i 
            
            class="fa-solid fa-x mx-1"
            @click="confirmingGroupUserDeletion = true"
        >
        </i>
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
                                name="id"
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