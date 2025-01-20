<script setup>
import { data } from 'autoprefixer';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    groupUser: {
        type: Object,
    },
});

const selected = ref(false);

// onMounted(() => console.log('share', props.groupUser));

</script>

<template>
    <div
        @click="selected = !selected"
        class="border-solid border-2 border-green-600 bg-white m-2 p-2 flex flex-row"
        :class="selected ? 'bg-green-200' : ''"
    >
        <label 
            :for="groupUser.id">{{ groupUser.user.name }}
        </label>
        <input
            :name="`groupUser-${groupUser.id}`"
            @click.stop
            v-show="selected"
            type="number"
            step="0.01" 
            :id="groupUser.id"
            class="disabled:bg-slate-50" 
        />
    </div>
</template>
