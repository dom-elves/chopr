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

// show the user alias that is logged in currently
const visibleAlias = computed(() =>
    props.group_user.aliases.find(
        alias => alias.user_id === Number(usePage().props.auth.user.id)
    )
);

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
            <small>{{ visibleAlias ? visibleAlias.alias : '' }}</small>
        </div>
        <div v-else class="flex flex-col w-full">
            <Form
                :action="visibleAlias ? route('alias.update', visibleAlias.id) : route('alias.store')" 
                :method="visibleAlias ? 'patch' : 'post'"
                #default="{ errors }"
                :transform="data => ({
                    ...(visibleAlias ? { id: visibleAlias.id } : {}),
                    ...data,
                    user_id: usePage().props.auth.user.id,
                    group_user_id: props.group_user.id, 
                })"
                @success="isEditing = false;refresh & refresh()"
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
        <div class="flex justify-end" style="width:50px">
            <Controls
                item="Group User"
                :updatable="true"
                :deletable="props.group_user.can_delete"
                @edit="isEditing = !isEditing;refresh & refresh()"
                @destroy="confirmingGroupUserDeletion = true;refresh & refresh()"
            >
            </Controls>
        </div>
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
                    @success="confirmingGroupUserDeletion = false;refresh & refresh()"
                    :options="{
                        preserveScroll: true,
                    }"
                    :transform="data => ({ 
                        ...data, 
                        group_id: props.group.id,
                        group_user_id: props.group_user.id,
                    })"
                >
                    <div class="flex flex-row mt-4 justify-center sm:justify-end w-full">
                        <SecondaryButton 
                            @click="confirmingGroupUserDeletion = false;"
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