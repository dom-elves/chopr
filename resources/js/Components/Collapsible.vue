<script setup>
import { ref } from 'vue'

const props = defineProps({
    modelValue: {
        type: Boolean,
        required: true,
    },
    duration: {
        type: Number,
        default: 300,
    },
})

const content = ref(null)

// Transition hooks for smooth height animation
const enter = (el) => {
    el.style.height = '0'
    el.style.transition = `height ${props.duration}ms ease`
    el.offsetHeight // trigger reflow
    el.style.height = el.scrollHeight + 'px'
}

const leave = (el) => {
    el.style.height = el.scrollHeight + 'px'
    el.offsetHeight
    el.style.transition = `height ${props.duration}ms ease`
    el.style.height = '0'
}
</script>

<template>
    <transition
        @enter="enter"
        @leave="leave"
        >
        <div
            v-show="modelValue"
            ref="content"
            class="overflow-hidden transition-all duration-300 ease-in-out"
        >
            <slot />
        </div>
    </transition>
</template>
