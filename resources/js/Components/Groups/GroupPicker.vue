<script setup>
import InputError from '@/Components/InputError.vue';

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
    <div>
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
        >
            <option value="" disabled selected>Select a group</option>
            <option v-for="group in groups"
                :key="group.code"
                :value="group.code"
            >
                {{  group.name }}
            </option>>
        </select>
        <InputError :message="errors" />
    </div>
</template>