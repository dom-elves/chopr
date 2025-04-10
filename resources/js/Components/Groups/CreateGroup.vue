
<script setup>
import { ref, onMounted, reactive } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const showCreateGroupButton = ref(true);
const user_id = usePage().props.auth.user.id;

const createGroupForm = useForm({
    user_id: user_id,
    name: '',
});

function addGroup() {
    createGroupForm.post(route('group.store'), {
        onSuccess: (result) => {

        },
        onError: (error) => {
            createGroupForm.errors.name;
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
                        v-model="createGroupForm.name"
                        placeholder="Enter a group name..."
                    >
                </form>
                <i 
                    class="fa-solid fa-x mx-1"
                    @click="showCreateGroupButton = !showCreateGroupButton"
                ></i>
            </div>
            <InputError class="mt-2" :message="createGroupForm.errors.name" />
        </div>
    </div>
</template>
