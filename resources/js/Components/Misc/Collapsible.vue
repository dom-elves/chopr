<script setup>
import { ref, nextTick, provide } from 'vue'

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

// any child of any collapsible can inject 'refresh' to recalc collapsible div height
// this is used e.g. when a new comment/share is added
// also if a textbox is opened/closed
const refresh = async () => {
    // wait for DOM refresh
    await nextTick()

    // check the element has actually rendered
    if (!content.value || !props.modelValue) return

    // recalc
    const el = content.value
    el.style.height = 'auto'
    el.style.height = el.scrollHeight + 'px'
}

// key/signal to provide, content that is being provided
// so this essentially provides the entire function
provide('collapsibleRefresh', refresh);
</script>

<template>
    <Transition
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
    </Transition>
</template>
