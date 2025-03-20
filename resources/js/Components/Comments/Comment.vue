<script setup>
import { computed, onMounted, onUnmounted, ref, reactive, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import Controls from '@/Components/Controls.vue';

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

const commentForm = useForm({
    id: props.comment.id,
    debt_id: props.comment.debt_id,
    content: props.comment.content,
    user_id: usePage().props.auth.user.id,
});

function editComment() {
    commentForm.patch(route('comment.update'), {  
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = !isEditing.value;
        }, 
    });
}

function deleteComment() {
    commentForm.delete(route('comment.destroy'), {  
        preserveScroll: true,
    });
}

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
    <div class="p-1 my-2 border-solid border-2 bg-white">
        <div class="flex flex-row justify-between">
            <div class="flex-col">
                <div>
                    <strong>{{ comment.user.name }}</strong>
                    <small> on {{ formatCommentDate(comment.created_at) }}</small>
                </div>
                <small v-if="comment.edited">last edited at {{ formatCommentDate(comment.updated_at) }}</small>
            </div>
            <div
                v-if="usePage().props.ownership.comment_ids.includes(props.comment.id)"
                class="p-2 flex flex-row justify-between"
            >
                <Controls
                    item="Comment"
                    @edit="isEditing = !isEditing"
                    @destroy="confirmingCommentDeletion = true"
                >
                </Controls>
            </div>
        </div>
        <p  
            v-if="!isEditing"
            class="p-2"
        >
            {{ comment.content }}
        </p>
        <form 
            v-else
        >   
            <label for="editComment" class="hidden">Edit comment</label>
            <textarea 
                v-model="commentForm.content"
                class="w-full"
                id="editComment"
                @keydown.enter.prevent="editComment"
            >
            </textarea>
        </form>
        <Modal :show="confirmingCommentDeletion" @close="closeModal">
            <div class="p-6">
                <h2
                    class="text-lg font-medium text-gray-900"
                >
                    Are you sure you want to delete this comment?
                </h2>
                <div class="mt-6 flex justify-end">
                    <button 
                        @click="closeModal"
                    >
                        Cancel
                    </button>
                    <button
                        class="ms-3"
                        @click="deleteComment"
                    >
                        Delete Comment
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>