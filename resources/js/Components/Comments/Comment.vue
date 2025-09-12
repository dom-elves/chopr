<script setup>
import { computed, onMounted, onUnmounted, ref, reactive, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import Controls from '@/Components/Controls.vue';
import { Form } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';

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
    <div class="p-1 my-2 border-solid border-2 bg-white flex justify-between">
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
                    <PrimaryButton type="submit" class="mt-2">Save Comment</PrimaryButton>
                </Form>
            </div>
        </div>
        <Controls
            
            item="Comment"
            @edit="isEditing = !isEditing"
            @destroy="confirmingCommentDeletion = true"
        >
        </Controls>
        <Modal :show="confirmingCommentDeletion" @close="closeModal">
            <div class="p-6">
                <h2
                    class="text-lg font-medium text-gray-900"
                >
                    Are you sure you want to delete this comment?
                </h2>
                <div class="mt-6 flex justify-end">
                    <Form
                        :action="route('comment.destroy')"
                        method="delete"
                        #default="{ errors }"
                        @success="closeModal"
                        :options="{
                            preserveScroll: true,
                        }"
                    >
                        <button 
                            @click="closeModal"
                        >
                            Cancel
                        </button>
                        <input
                            type="hidden"
                            name="id"
                            :value="props.comment.id"
                        />
                        <button
                            class="ms-3"
                            type="submit"
                        >
                            Delete Comment
                        </button>
                        <InputError class="mt-2" :message="errors.id" />
                    </Form>
                </div>
            </div>
        </Modal>
    </div>
</template>