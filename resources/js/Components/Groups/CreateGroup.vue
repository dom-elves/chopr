
<script setup>
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import { Form } from '@inertiajs/vue3';

const showCreateGroupButton = ref(true);

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
                <Form 
                    :action="route('group.store')" 
                    method="post" 
                    #default="{ errors }"
                    :transform="data => ({ ...data, user_id: usePage().props.auth.user.id })"
                >
                    <label 
                        for="name" 
                        class="hidden" 
                    >
                        Search:
                    </label>
                    <input 
                        type="text" 
                        name="name"
                        id="name" 
                        aria-labelledby="name"
                        placeholder="Enter a group name..."
                    >
                    <InputError v-if="errors.name" class="mt-2" :message="errors.name" />
                </Form>
                <i 
                    class="fa-solid fa-x mx-1"
                    @click="showCreateGroupButton = !showCreateGroupButton"
                ></i>
            </div>
        </div>
    </div>
</template>
