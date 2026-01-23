<script setup>
import { Form } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/Misc/PrimaryButton.vue';
import SecondaryButton from '@/Components/Misc/SecondaryButton.vue';
import { inject } from 'vue'
import InputError from '@/Components/Forms/InputError.vue';

const props = defineProps({
    debt: {
        type: Object,
    },
    user: {
        type: Object,
    },
});

const refresh = inject('collapsibleRefresh');
const emit = defineEmits(['closeAddComment']);

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
        @success="refresh & refresh()"
        @error="refresh & refresh()"
    >
        <label for="content" class="hidden">Post a comment</label>
        <textarea 
            id="content" 
            name="content"
            class="w-full w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            placeholder="Post a comment..."
        >
        </textarea>
        <div class="flex flex-col w-full">
            <InputError v-for="error in errors" class="mt-2 text-center lg:text-end" :message="error" />
            <div class="flex flex-row justify-center sm:justify-end">
                <SecondaryButton
                    class="w-1/2 mt-2"
                    @click="emit('closeAddComment')"
                >
                    Cancel
                </SecondaryButton>
                <PrimaryButton
                    type="submit"
                    class="w-1/2 mt-2"
                >
                    Save
                </PrimaryButton>
            </div>
        </div>
    </Form>
</template>