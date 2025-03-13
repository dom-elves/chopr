<script setup>
import { ref } from 'vue';

const props = defineProps({
    // the 'item' is what's being actioned, Debt, Comment etc.
    item: {
        type: String,
    }
});

const showMenu = ref(false);

</script>
<template>
    <div class="flex flex-col" @click="showMenu = !showMenu">
        <i class="fa-solid fa-ellipsis-vertical"></i>
        <div v-if="showMenu">
            <ul>
                <li @click="$emit('editItem')">Edit {{ props.item }}</li>
                <li @click="$emit('deleteItem')">Delete{{ props.item }}</li>
            </ul>
        </div>
    </div>
</template>
<style>
li {
    background-color: white;
}
li:hover {
    background-color: #c5c7c9;
}
</style>