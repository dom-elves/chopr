<script setup>
import { ref, onMounted, onUnmounted, getCurrentInstance  } from 'vue';

const emit = defineEmits(['edit', 'destroy']);

const props = defineProps({
    // the 'item' is what's being actioned, Debt, Comment etc.
    item: {
        type: String,
    }
});

const popoverId = ref('popover-' + getCurrentInstance().uid);

function edit() {
    emit('edit');
    document.getElementById(popoverId.value).hidePopover();
}

function destroy() {
    emit('destroy');
}

// todo: figure out why popover isn't closing on esc like docs say it should do
onMounted(() => {

});

</script>
<template>
    <div class="flex flex-col p-2">
        <button :popovertarget="popoverId" style="position:relative"><i class="fa-solid fa-ellipsis-vertical"></i></button>
        <ul popover="auto" :id="popoverId" class="popover">
            <li @click="edit">Edit {{ props.item }}</li>
            <li @click="destroy">Delete {{ props.item }}</li>
        </ul>
    </div>
</template>
<style>

ul > li {
    display: block;
    width: 100%;
    padding: 0.5rem 1rem;
    text-align: left;
    font-size: 0.875rem;
    line-height: 1.25rem;
    color: #4a5568; /* text-gray-700 */
    transition: background-color 150ms ease-in-out;
}

ul > li:hover,
ul > li:focus {
    background-color: #f7fafc; /* hover:bg-gray-100 */
    outline: none;
}

.popover {
    top: calc(anchor(top) + 20px);
    justify-self: anchor-center;
    background: white;
    border-radius: 0.375rem; /* rounded-md */
    box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1); /* shadow-lg */
    border: 1px solid grey;
}
</style>