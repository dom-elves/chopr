<script setup>
import { computed, onMounted, onUnmounted, ref, reactive, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import Controls from '@/Components/Controls.vue';
import { Form } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputError from '@/Components/InputError.vue';
import DangerButton from '@/Components/DangerButton.vue';

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

const closeModal = () => {
    confirmingCommentDeletion.value = false;
};
function formatCommentDate(date) {
    return new Date(date).toLocaleDateString("en-GB", options);
}

onMounted(() => {

});

</script>
<template>
    <div class="flex flex-row items-center my-2">
        <div class="flex flex-row w-full items-center">
            <svg width="50" height="50" xmlns="http://www.w3.org/2000/svg">
                <circle cx="25" cy="25" r="20" stroke="green" stroke-width="4" fill="yellow" />
            </svg>
            <div class="flex-col">
                <!-- name & date -->
                <div>
                    <strong>{{ comment.user.name }}</strong>
                    <small> on {{ formatCommentDate(comment.created_at) }}</small>
                </div>
                <i v-if="comment.edited">
                    <small>last edited at {{ formatCommentDate(comment.updated_at) }}</small>
                </i>
                <!-- content & edit form -->
                <div>
                    <p  
                        v-if="!isEditing"
                        class="p-2"
                    >
                        {{ comment.content }}
                    </p>
                    <Form
                        v-else
                        :action="route('comment.update')"
                        method="patch"
                        resetOnSuccess
                        :transform="data => ({ 
                            ...data, 
                            id: props.comment.id,
                            debt_id: props.comment.debt_id,
                            user_id: usePage().props.auth.user.id,
                        })"
                        :options="{
                            preserveScroll: true,
                        }"
                        @success="isEditing = false"
                    >   
                        <label for="edit_comment" class="hidden">Edit comment</label>
                        <textarea 
                            class="w-full"
                            id="edit_comment"
                            name="content"
                        >
                        </textarea>
                        <SecondaryButton
                            type="button"
                            class="w-1/2 justify-center"
                            @click="isEditing = false"
                        >
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" class="mt-2">Save Comment</PrimaryButton>
                    </Form>
                </div>
            </div>
        </div>
        <Controls
            v-if="usePage().props.ownership.comment_ids.includes(props.comment.id)"
            item="Comment"
            @edit="isEditing = !isEditing"
            @destroy="confirmingCommentDeletion = true"
        >
        </Controls>
        <Modal :show="confirmingCommentDeletion" @close="closeModal">
            <div class="p-6 flex flex-col">
                <h2
                    class="text-lg font-medium text-gray-900"
                >
                    Are you sure you want to delete this comment?
                </h2>   
                <Form
                    class="mt-6 flex justify-end"
                    :action="route('comment.destroy')"
                    method="delete"
                    #default="{ errors }"
                    @success="closeModal"
                    :options="{
                        preserveScroll: true,
                    }"
                >
                    <div class="flex flex-row mt-4 justify-center sm:justify-end w-full">
                        <SecondaryButton 
                            @click="closeModal"
                        >
                            Cancel
                        </SecondaryButton>
                        <input
                            type="hidden"
                            name="id"
                            :value="props.comment.id"
                        />
                        <DangerButton
                        >
                            Delete
                        </DangerButton>
                        <InputError class="mt-2 content-end" :message="errors.id" />
                    </div>
                </Form>
            </div>
        </Modal>
    </div>
</template>