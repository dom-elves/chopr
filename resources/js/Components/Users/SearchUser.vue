
<script setup>
import { ref, onMounted, reactive } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    group_id: {
        type: Number,
    }
});
const showAddUserButton = ref(true);

const query_string = ref('');

// results from searching for a user
const results = ref([])

async function search() {
    try {
        const res = await axios.get(route('users.index'), {
            params: { 
                query_string: query_string.value,
                group_id: props.group_id,
             }
        });
        
        // if no users are found, set to null in order to display error message
        if (res.data.data.length == 0) {
            results.value = null;
        } else {
            results.value = res.data.data;
        }
        
    } catch (error) {
        // console.log(error);
    }
}

const searchUserForm = useForm({
    user_id: null,
    group_id: props.group_id,
});

function addUser(user_id) {
    searchUserForm.user_id = user_id;
    searchUserForm.post(route('group-users.store'),
    {
        preserveScroll: true,
    },
{
        onSuccess: (result) => {
            console.log('result', result);
        },
        onError: (error) => {
            console.log('error', error);
        },
    })
}

onMounted(() => {

});

</script>

<template>
    <div>
        <button 
            class="bg-blue-400 text-white p-2 w-full" 
            @click="showAddUserButton = !showAddUserButton" 
            v-if="showAddUserButton">
            Add a user
        </button>
        <div class="my-2 border-solid border-2 border-amber-600 flex flex-col justify-center items-center" v-else>
            <div class="flex flex-row">
                <form @submit.prevent>
                    <label 
                        for="add-group-user" 
                        class="hidden" 
                        id="user-search"
                    >
                        Search:
                    </label>
                    <input 
                        type="text" 
                        id="add-group-user" 
                        aria-labelledby="user-search"
                        v-model="query_string" 
                        @keydown="search"
                        placeholder="Search for a user..."
                    >
                </form>
                <i 
                    class="fa-solid fa-x mx-1"
                    @click="showAddUserButton = !showAddUserButton"
                >
                </i>
            </div>
            <InputError v-if="results == null" class="mt-2" message="No users found" />
            <InputError class="mt-2" :message="searchUserForm.errors.user_id" />
            <div v-if="results != null">
                <div 
                    v-for="user in results" 
                    style="background-color:#ffffff" 
                    class="p-2 flex flex-row items-center justify-between"
                >
                    <div class="flex flex-col">
                        <p>{{ user.name }}</p>
                        <small class="text-gray-300">{{ user.email }}</small>
                    </div>
                    <i class="fa-solid fa-plus" @click="addUser(user.id)"></i>
                </div>
            </div>
        </div>
    </div>
</template>
