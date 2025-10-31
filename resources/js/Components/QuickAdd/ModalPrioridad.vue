<script setup>
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/Components/ui/dialog'
import { Button } from '@/Components/ui/button'
import { Check } from 'lucide-vue-next'

const props = defineProps({
    open: {
        type: Boolean,
        required: true,
    },
    prioridadSeleccionada: {
        type: Number,
        default: null,
    },
})

const emit = defineEmits(['update:open', 'seleccionar'])

const prioridades = [
    {
        valor: 1,
        label: 'Alta',
        color: '#EF4444', // red-500
        bgColor: 'bg-red-500',
        bgHover: 'hover:bg-red-50 dark:hover:bg-red-900/20',
        border: 'border-red-500 dark:border-red-400',
    },
    {
        valor: 2,
        label: 'Media',
        color: '#EAB308', // yellow-500
        bgColor: 'bg-yellow-500',
        bgHover: 'hover:bg-yellow-50 dark:hover:bg-yellow-900/20',
        border: 'border-yellow-500 dark:border-yellow-400',
    },
    {
        valor: 3,
        label: 'Baja',
        color: '#22C55E', // green-500
        bgColor: 'bg-green-500',
        bgHover: 'hover:bg-green-50 dark:hover:bg-green-900/20',
        border: 'border-green-500 dark:border-green-400',
    },
]

const handleSeleccionar = (valor) => {
    emit('seleccionar', valor)
    emit('update:open', false)
}

const handleClose = () => {
    emit('update:open', false)
}
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="sm:max-w-[380px] max-h-[400px] p-0 overflow-hidden">
            <DialogHeader class="px-6 pt-6 pb-0">
                <DialogTitle class="text-lg font-semibold text-gray-900 dark:text-white">
                    Seleccionar Prioridad
                </DialogTitle>
            </DialogHeader>

            <div class="space-y-2 px-6 py-4 overflow-y-auto">
                <button
                    v-for="prioridad in prioridades"
                    :key="prioridad.valor"
                    @click="handleSeleccionar(prioridad.valor)"
                    :class="[
                        'w-full flex items-center justify-between px-4 py-3 rounded-lg transition-all duration-200',
                        'border-2',
                        prioridad.bgHover,
                        prioridadSeleccionada === prioridad.valor
                            ? `bg-opacity-10 ${prioridad.border}`
                            : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700',
                    ]"
                >
                    <div class="flex items-center gap-3">
                        <span
                            :class="[prioridad.bgColor, 'w-4 h-4 rounded-full']"
                        />
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ prioridad.label }}
                        </span>
                        <span
                            v-if="prioridad.valor === 2"
                            class="text-xs text-gray-500 dark:text-gray-400"
                        >
                            (Por defecto)
                        </span>
                    </div>
                    <Check
                        v-if="prioridadSeleccionada === prioridad.valor"
                        :size="20"
                        :style="{ color: prioridad.color }"
                    />
                </button>
            </div>

            <div class="flex justify-end gap-2 px-6 py-4 border-t border-gray-200 dark:border-gray-800">
                <Button
                    variant="outline"
                    @click="handleClose"
                    class="transition-all duration-200"
                >
                    Cancelar
                </Button>
            </div>
        </DialogContent>
    </Dialog>
</template>

