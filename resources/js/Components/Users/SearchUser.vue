
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
        <div class="my-2 border-solid border-2 border-amber-600 flex justify-center" v-else>
            <form @submit.prevent>
                <label for="add-group-user">Search:</label>
                <input type="text" id="add-group-user" v-model="query_string" @change="search">
            </form>
            <i 
                class="fa-solid fa-x mx-1"
                @click="showAddUserButton = !showAddUserButton"
            >
            </i>
            <div v-if="results.user_results != null">
                <p v-for="user in results.user_results">{{ user.name }}</p>
            </div>
        </div>
    </div>
</template>
