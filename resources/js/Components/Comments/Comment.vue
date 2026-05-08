<script setup>
import { computed, onMounted, onUnmounted, ref, reactive, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Forms/Modal.vue';
import Controls from '@/Components/Misc/Controls.vue';
import { Form } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/Misc/PrimaryButton.vue';
import SecondaryButton from '@/Components/Misc/SecondaryButton.vue';
import InputError from '@/Components/Forms/InputError.vue';
import DangerButton from '@/Components/Misc/DangerButton.vue';
import UserProfileIcon from '@/Components/Users/UserProfileIcon.vue';

const props = defineProps({
    comment: {
        type: Object,
    },
});

const options = { 
    year: "numeric", 
    month: "long", 
    day: "numeric", 
    hour: "2-digit", 
    minute: "2-digit", 
};

const isEditing = ref(false);
const confirmingCommentDeletion = ref(false);

function formatCommentDate(date) {
    return new Date(date).toLocaleDateString("en-GB", options);
}

onMounted(() => {

});

</script>
<template>
    <div class="flex flex-col m-2 bg-gray-100" style="border-radius:15px;padding:10px">
        <!-- picture, name, controls-->
        <div class="flex flex-row w-full justify-between">
            <div class="flex flex-row">
                <UserProfileIcon class="mr-2"/>
                <div class="flex flex-col">
                    <div>
                        <strong>{{ comment.group_user.user.name }}</strong>
                        <small> on {{ formatCommentDate(comment.created_at) }}</small>
                    </div>
                    <i v-if="comment.edited">
                        <small>last edited at {{ formatCommentDate(comment.updated_at) }}</small>
                    </i>
                </div>
            </div>
            <Controls
                item="Comment"
                :visible="props.comment.can.update || props.comment.can.delete"
                :updatable="props.comment.can.update"
                :deletable="props.comment.can.delete"
                @edit="isEditing = !isEditing"
                @destroy="confirmingCommentDeletion = true"
            >
            </Controls>
        </div>
        <!-- comment & edit form -->
        <p  v-if="!isEditing"class="p-2">{{ comment.content }}</p>
        <Form
            v-else
            class="mt-2"
            :action="route('comment.update', props.comment)"
            method="patch"
            #default="{ errors }"
            resetOnSuccess
            @success="isEditing = false"
            :options="{
                preserveScroll: true,
            }"
        >   
            <label for="edit_comment" class="hidden">Edit comment</label>
            <textarea 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                id="edit_comment"
                name="content"
                :value="comment.content"
            >
            </textarea>
            <div class="flex flex-row mt-2 sm:justify-end">
                <SecondaryButton
                    type="button"
                    class="mr-2"
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
            <InputError class="mt-2 text-center flex sm:justify-end" :message="errors.content" />
        </Form>
        <Modal :show="confirmingCommentDeletion" @close="confirmingCommentDeletion = false">
            <div class="p-6 flex flex-col">
                <h2
                    class="text-lg font-medium text-gray-900"
                >
                    Are you sure you want delete this comment?
                </h2>
                <Form
                    class="mt-6 flex flex-col justify-end"
                    :action="route('comment.destroy', props.comment)"
                    method="delete"
                    #default="{ errors }"
                    @success="confirmingCommentDeletion = false;refresh & refresh()"
                    :options="{
                        preserveScroll: true,
                    }"
                >
                    <div class="flex flex-row mt-4 justify-center sm:justify-end w-full">
                        <SecondaryButton 
                            @click="confirmingCommentDeletion = false;"
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