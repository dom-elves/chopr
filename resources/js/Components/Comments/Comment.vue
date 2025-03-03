<script setup>
import { computed, onMounted, onUnmounted, ref, reactive, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';

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
            isEditing.value = false;
        }, 
    });
}

function formatCommentDate(date) {
    return new Date(date).toLocaleDateString("en-GB", options);
}

onMounted(() => {
    console.log(usePage().props);
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
                class="p-2 flex flex-row justify-between">
                <i 
                    class="fa-solid fa-gear mx-1"
                    @click="isEditing = !isEditing"
                >
                </i>
                <i 
                    class="fa-solid fa-x mx-1"
                >
                </i>
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
    </div>
</template>