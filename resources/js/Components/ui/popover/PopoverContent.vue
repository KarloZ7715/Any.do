<script setup>
import { PopoverContent, PopoverPortal } from 'reka-ui'
import { cn } from '@/lib/utils'

defineProps({
    forceMount: { type: Boolean, required: false },
    side: { type: String, required: false },
    sideOffset: { type: Number, required: false, default: 4 },
    align: { type: String, required: false },
    alignOffset: { type: Number, required: false },
    avoidCollisions: { type: Boolean, required: false },
    collisionBoundary: { type: [Object, Array, null], required: false },
    collisionPadding: { type: [Number, Object], required: false },
    arrowPadding: { type: Number, required: false },
    sticky: { type: String, required: false },
    hideWhenDetached: { type: Boolean, required: false },
    updatePositionStrategy: { type: String, required: false },
    prioritizePosition: { type: Boolean, required: false },
    asChild: { type: Boolean, required: false },
    as: { type: null, required: false },
    class: { type: null, required: false },
})
</script>

<template>
    <PopoverPortal>
        <PopoverContent
            :side-offset="sideOffset"
            v-bind="$attrs"
            :class="
                cn(
                    'z-50 rounded-md border bg-white text-neutral-950 shadow-md outline-hidden',
                    'data-[state=open]:animate-in data-[state=closed]:animate-out',
                    'data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0',
                    'data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95',
                    'data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2',
                    'data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2',
                    'dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-50',
                    $props.class,
                )
            "
        >
            <slot />
        </PopoverContent>
    </PopoverPortal>
</template>
