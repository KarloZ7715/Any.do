<script setup>
import { computed } from 'vue'

const props = defineProps({
    checked: {
        type: [Array, Boolean],
        required: true,
    },
    value: {
        type: [String, Number, Boolean, Object],
        default: null,
    },
})

const emit = defineEmits(['update:checked'])

const proxyChecked = computed({
    get() {
        return props.checked
    },

    set(val) {
        emit('update:checked', val)
    },
})
</script>

<template>
    <input
        type="checkbox"
        :value="value"
        v-model="proxyChecked"
        class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 dark:text-indigo-500 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-400 dark:bg-gray-700 dark:checked:bg-indigo-500 transition-colors"
    />
</template>
