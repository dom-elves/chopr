<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    groupUser: {
        type: Object,
    },
});

const selected = ref(false);

function splitEven() {
    console.log('split even');
}

// onMounted(() => console.log('share', props.groupUser));

</script>

<template>
    <div
        @click="selected = !selected"
        class="border-solid border-2 border-green-600 m-2 p-2 flex flex-row"
        :class="selected ? 'bg-green-200' : ''"
    >
        <label 
            :for="groupUser.id">{{ groupUser.user.name }}
        </label>
        <input
            :name="`groupUser-${groupUser.id}`"
            @click.stop
            @submit.prevent
            v-show="selected"
            type="number"
            step="0.01" 
            :id="groupUser.id"
            class="disabled:bg-slate-50"
            @change="$emit('emitShare', groupUser.id, Number($event.target.value))"
            @split-even="splitEven"
        />
    </div>
</template>
