
<script setup>
import { ref, onMounted, reactive } from 'vue';
import { router, useForm } from '@inertiajs/vue3';

const showAddUserButton = ref(true);

const form = useForm({
    query: '',
});

const formErrors = reactive({
    query: '',
});

</script>

<template>
    <div>
        <button class="bg-blue-400 text-white p-2 w-full" @click="showAddUserButton = !showAddUserButton" v-if="showAddUserButton">Add a user</button>
        <div class="my-2 border-solid border-2 border-amber-600 flex justify-center" v-else>
            <form>
                <label for="add-group-user">Search:</label>
                <input type="text" id="add-group-user" v-model="form.query">
            </form>
            <i 
                class="fa-solid fa-x mx-1"
                @click="showAddUserButton = !showAddUserButton"
            >
            </i>
            <p class="text-red-500" v-if="formErrors.query">
                {{ formErrors.query }}
            </p>
        </div>
    </div>
</template>
