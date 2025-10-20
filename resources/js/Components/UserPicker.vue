<script setup>

import InputError from '@/Components/InputError.vue';

const props = defineProps({
    group_users: {
        type: Object,
    },
    errors: {
        type: String,
    }
});

</script>
<template>
    <div class="py-2">
        <label 
            for="user-picker" 
            class="block text-sm font-medium text-gray-700 hidden"
            id="user-label"
        >
            users
        </label>
        <select
            @change="$emit('userSelected', $event.target.value)" 
            id="user-picker"
            aria-labelledby="user-label"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
            <option value="" disabled selected>Select a user</option>
            <option v-for="group_user in group_users"
                :key="group_user.user.id"
                :value="group_user.user.id"
            >
                {{ group_user.user.name }}
            </option>>
        </select>
        <InputError :message="errors" />
    </div>
</template>