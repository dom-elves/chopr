
<script setup>
import { ref, onMounted, reactive } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';

const showCreateGroupButton = ref(true);
const user_id = usePage().props.auth.user.id;
const group_name = '';

const form = useForm({
    user_id: user_id,
    group_name: '',
});

const formErrors = reactive({
    user_id: null,
    group_name: '',
});

function addGroup() {
    router.post(route('group.store', form), {
        onSuccess: (result) => {
            console.log('result', result);
        },
        onError: (error) => {
            formErrors.user_id = error.user_id;
            formErrors.group_name = error.group_name;
        },
    })
}

onMounted(() => {
    
});

</script>

<template>
    <div class="m-2">
        <button 
            class="bg-blue-400 text-white p-2 w-full" 
            @click="showCreateGroupButton = !showCreateGroupButton" 
            v-if="showCreateGroupButton">
            Create A Group
        </button>
        <div class="my-2 border-solid border-2 border-amber-600 flex flex-col justify-center items-center" v-else>
            <div class="flex flex-row">
                <form @submit.prevent="addGroup">
                    <label 
                        for="new-group-name" 
                        class="hidden" 
                        id="new-group-name"
                    >
                        Search:
                    </label>
                    <input 
                        type="text" 
                        id="new-group-name" 
                        aria-labelledby="new-group-name"
                        v-model="form.group_name"
                        placeholder="Enter a group name..."
                    >
                </form>
                <i 
                    class="fa-solid fa-x mx-1"
                    @click="showCreateGroupButton = !showCreateGroupButton"
                >
                </i>
            </div>
        </div>
    </div>
</template>
