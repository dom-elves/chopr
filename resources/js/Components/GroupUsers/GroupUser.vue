<script setup>

import { computed, onMounted, onUnmounted, ref, reactive } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { Form } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import Controls from '@/Components/Controls.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
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
    <div class="flex flex-row plate">
        <svg width="50" height="50" xmlns="http://www.w3.org/2000/svg">
            <circle cx="25" cy="25" r="20" stroke="green" stroke-width="4" fill="yellow" />
        </svg>
        <div v-if="!isEditing"class="flex flex-col p-2 w-full items-center">
            <h3 class="text-xl text-center font-semibold">
                {{ group_user.user.name }}
            </h3>
            <small>placeholder for group user alias</small>
        </div>
        <div v-else class="w-full">
            <Form
                :action="route('group-users.update')" 
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
                        for="newGroupUserName" 
                        style="display:none;"
                        id="newGroupUserNameLabel"
                    >
                    New Name
                    </label>
                    <TextInput
                        name="name"
                        type="text"
                        id="newGroupUserName"
                        aria-labelledby="newGroupUserNameLabel"
                        placeholder="Enter an alias for this group user"
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
            :class=" owns_group ? 'visible' : 'invisible' "
            item="Group User"
            @edit="isEditing = !isEditing"
            @destroy="confirmingGroupUserDeletion = true"
        >
        </Controls>
        <Modal :show="confirmingGroupUserDeletion" @close="confirmingGroupUserDeletion = false">
            <div class="p-6">
                <h2
                    class="text-lg font-medium text-gray-900"
                >
                    Are you sure you want remove <i>{{ group_user.user.name }}</i> from "<i>{{ group.name }}</i>"?
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
                    <div class="flex flex-row mt-4 justify-center sm:justify-end w-full">
                        <SecondaryButton 
                            @click="confirmingGroupDeletion = false;"
                        >
                            Cancel
                        </SecondaryButton>
                        <input
                            type="hidden"
                            name="id"
                            :value="props.group_user.id"
                        />
                        <DangerButton
                        >
                            Delete
                        </DangerButton>
                    </div>
                    <InputError class="mt-2 content-end" :message="errors.id" />
                </Form>
            </div>
        </Modal>
    </div>
</template>