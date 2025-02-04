
<script setup>
import { ref, onMounted, reactive } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';

const showAddUserButton = ref(true);

const query_string = ref('');

const results = reactive({
    user_results: null,
})

const search = async () => {
    try {
        const res = await axios.get(route('users.index'), {
            params: { query_string: query_string.value }
        });
        results.user_results = res.data.data;

    } catch (error) {
        console.log(error);
    }
}

onMounted(() => {

});

</script>

<template>
    <div>
        <button class="bg-blue-400 text-white p-2 w-full" @click="showAddUserButton = !showAddUserButton" v-if="showAddUserButton">Add a user</button>
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
                        @change="search"
                        placeholder="Search for a user..."
                    >
                </form>
                <i 
                    class="fa-solid fa-x mx-1"
                    @click="showAddUserButton = !showAddUserButton"
                >
                </i>
            </div>
            <div v-if="results.user_results != null">
                <div 
                    v-for="user in results.user_results" 
                    style="background-color:#ffffff" 
                    class="p-2 flex flex-row items-center justify-between"
                >
                    <div class="flex flex-col">
                        <p>{{ user.name }}</p>
                        <small class="text-gray-300">{{ user.email }}</small>
                    </div>
                    <i class="fa-solid fa-plus"></i>
                </div>
            </div>
        </div>
    </div>
</template>
