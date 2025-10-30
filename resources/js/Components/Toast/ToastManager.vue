<script setup>
import { Toaster, toast } from 'sonner'
import { usePage } from '@inertiajs/vue3'
import { watch } from 'vue'

// Escuchar flashes de Inertia y mostrar toasts
const page = usePage()

watch(
    () => page.props.flash,
    (flash) => {
        if (!flash) return

        if (flash.success) {
            toast.success(flash.success, {
                duration: 3000,
            })
        }

        if (flash.error) {
            toast.error(flash.error, {
                duration: 4000,
            })
        }

        if (flash.info) {
            toast.info(flash.info, {
                duration: 3000,
            })
        }

        if (flash.warning) {
            toast.warning(flash.warning, {
                duration: 3500,
            })
        }
    },
    { deep: true, immediate: true }
)
</script>

<template>
    <Toaster
        position="top-right"
        :duration="3000"
        close-button
        rich-colors
        :toast-options="{
            classNames: {
                toast: 'dark:bg-gray-800 dark:border-gray-700',
                title: 'dark:text-white',
                description: 'dark:text-gray-400',
                actionButton: 'dark:bg-indigo-600 dark:text-white',
                cancelButton: 'dark:bg-gray-700 dark:text-gray-300',
                closeButton: 'dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600',
            },
        }"
    />
</template>
