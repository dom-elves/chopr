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

const selectedOption = ref(null);

onMounted(() => {
    console.log(props.groups);
})
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
            v-model="selectedOption" 
            @change="$emit('groupSelected', $event.target.value)" 
            id="group-picker"
            aria-labelledby="groupType"
        >
            <option value=" " disabled selected>Select a group</option>
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