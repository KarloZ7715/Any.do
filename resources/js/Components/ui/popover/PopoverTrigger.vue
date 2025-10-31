<script setup>
import { PopoverTrigger } from 'reka-ui'
import { computed } from 'vue'

const props = defineProps({
    asChild: { type: Boolean, required: false },
})

const delegatedProps = computed(() => {
    const { asChild, ...delegated } = props

    return delegated
})
</script>

<template>
    <PopoverTrigger v-bind="delegatedProps" :as-child="asChild">
        <slot />
    </PopoverTrigger>
</template>
