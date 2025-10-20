<script setup>
import InputError from '@/Components/InputError.vue';
import { onMounted, ref } from 'vue';

const props = defineProps({
    groups: {
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
            for="group-picker" 
            class="block text-sm font-medium text-gray-700 hidden"
            id="groupType"
        >
            Groups
        </label>
        <select
            @change="$emit('groupSelected', $event.target.value)" 
            id="group-picker"
            aria-labelledby="groupType"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
            <option value="" disabled selected>Select a group</option>
            <option v-for="group in groups"
                :key="group.id"
                :value="group.id"
            >
                {{  group.name }}
            </option>>
        </select>
        <InputError :message="errors" />
    </div>
</template>