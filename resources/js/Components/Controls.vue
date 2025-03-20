<script setup>
import { ref, onMounted, onUnmounted, getCurrentInstance  } from 'vue';

const emit = defineEmits(['edit', 'destroy']);

const props = defineProps({
    // the 'item' is what's being actioned, Debt, Comment etc.
    item: {
        type: String,
    }
});

function edit() {
    emit('edit');
}

function destroy() {
    emit('destroy');
}

// todo: figure out why popover isn't closing on esc like docs say it should do
onMounted(() => {

});

</script>
<template>
    <div class="flex flex-col p-2"> {{  props.item }}
        <button :popovertarget="getCurrentInstance().uid" style="position:relative"><i class="fa-solid fa-ellipsis-vertical"></i></button>
        <ul popover="auto" :id="getCurrentInstance().uid" class="popover">
            <li @click="edit">Edit {{ props.item }}</li>
            <li @click="destroy">Delete {{ props.item }}</li>
        </ul>
    </div>
</template>
<style>
li {
    background-color: white;
    padding: 10px;
}
li:hover {
    background-color: #c5c7c9;
}

ul li:first-child {
    border-bottom: 1px solid black;
}
.popover {
    top: calc(anchor(top) + 20px);
    justify-self: anchor-center;
    background: white;
    border: 1px solid black;
    box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.2);
}
</style>