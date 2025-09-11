<script setup>
    import { Form } from '@inertiajs/vue3';
    import PrimaryButton from '@/Components/PrimaryButton.vue';

    const props = defineProps({
    debt: {
        type: Object,
    },
    user: {
        type: Object,
    },
});

</script>
<template>
    <Form
        :action="route('comment.store')"
        method="post"
        #default="{ errors }"
        resetOnSuccess
        :transform="data => ({ 
                ...data, 
                debt_id: props.debt.id,
                user_id: props.user.id,
            })"
        :options="{
            preserveScroll: true,
        }"
    >
        <label for="content" class="hidden">Post a comment</label>
        <textarea 
            id="content" 
            name="content"
            class="w-full"
            placeholder="Post a comment..."
        >
        </textarea>
        <PrimaryButton type="submit" class="mt-2">Add Comment</PrimaryButton>
    </Form>
</template>