<script setup>
import { ref, computed, watch } from 'vue'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/Components/ui/dialog'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'

const props = defineProps({
    open: {
        type: Boolean,
        required: true,
    },
    fecha: {
        type: String,
        default: null,
    },
    hora: {
        type: String,
        default: null,
    },
})

const emit = defineEmits(['update:open', 'seleccionar'])

// Estados locales
const fechaLocal = ref(props.fecha || '')
const horaLocal = ref(props.hora || '')

// Watch para actualizar cuando cambian las props
watch(() => props.fecha, (newVal) => {
    fechaLocal.value = newVal || ''
}, { immediate: true })

watch(() => props.hora, (newVal) => {
    horaLocal.value = newVal || ''
}, { immediate: true })

const handleGuardar = () => {
    if (fechaLocal.value) {
        emit('seleccionar', {
            fecha: fechaLocal.value,
            hora: horaLocal.value || null,
        })
        emit('update:open', false)
    }
}

const handleLimpiar = () => {
    fechaLocal.value = ''
    horaLocal.value = ''
    emit('seleccionar', { fecha: null, hora: null })
    emit('update:open', false)
}

const handleClose = () => {
    emit('update:open', false)
}

// Fecha mínima (hoy)
const fechaMinima = computed(() => {
    return new Date().toISOString().split('T')[0]
})

// Atajos de fecha
const atajos = [
    {
        label: 'Hoy',
        fecha: new Date().toISOString().split('T')[0],
    },
    {
        label: 'Mañana',
        fecha: (() => {
            const manana = new Date()
            manana.setDate(manana.getDate() + 1)
            return manana.toISOString().split('T')[0]
        })(),
    },
    {
        label: 'Próxima semana',
        fecha: (() => {
            const proximaSemana = new Date()
            proximaSemana.setDate(proximaSemana.getDate() + 7)
            return proximaSemana.toISOString().split('T')[0]
        })(),
    },
]

const seleccionarAtajo = (atajo) => {
    fechaLocal.value = atajo.fecha
}
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="sm:max-w-[420px] max-h-[450px] p-0 overflow-hidden">
            <DialogHeader class="px-6 pt-6 pb-0">
                <DialogTitle class="text-lg font-semibold text-gray-900 dark:text-white">
                    Seleccionar Fecha y Hora
                </DialogTitle>
            </DialogHeader>

            <div class="space-y-4 px-6 py-4 overflow-y-auto">
                <!-- Atajos rápidos -->
                <div class="flex gap-2">
                    <Button
                        v-for="atajo in atajos"
                        :key="atajo.label"
                        variant="outline"
                        size="sm"
                        @click="seleccionarAtajo(atajo)"
                        class="flex-1 transition-all duration-200 hover:bg-indigo-50 dark:hover:bg-indigo-900/20"
                    >
                        {{ atajo.label }}
                    </Button>
                </div>

                <!-- Selector de fecha -->
                <div class="space-y-2">
                    <Label for="fecha" class="text-sm font-semibold text-gray-900 dark:text-white">
                        Fecha
                        <span class="text-red-500">*</span>
                    </Label>
                    <Input
                        id="fecha"
                        v-model="fechaLocal"
                        type="date"
                        :min="fechaMinima"
                        class="transition-all duration-200 focus:border-indigo-500 dark:focus:border-indigo-400 dark:text-white dark:[color-scheme:dark]"
                    />
                </div>

                <!-- Selector de hora -->
                <div class="space-y-2">
                    <Label for="hora" class="text-sm font-semibold text-gray-900 dark:text-white">
                        Hora
                        <span class="text-gray-400 text-xs font-normal">(opcional)</span>
                    </Label>
                    <Input
                        id="hora"
                        v-model="horaLocal"
                        type="time"
                        class="transition-all duration-200 focus:border-indigo-500 dark:focus:border-indigo-400 dark:text-white dark:[color-scheme:dark]"
                    />
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Si no especificas hora, la tarea vence al final del día
                    </p>
                </div>
            </div>

            <div class="flex justify-between gap-2 px-6 py-4 border-t border-gray-200 dark:border-gray-800">
                <Button
                    variant="outline"
                    @click="handleLimpiar"
                    class="transition-all duration-200 text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20"
                >
                    Limpiar fecha
                </Button>
                <div class="flex gap-2">
                    <Button
                        variant="outline"
                        @click="handleClose"
                        class="transition-all duration-200"
                    >
                        Cancelar
                    </Button>
                    <Button
                        @click="handleGuardar"
                        :disabled="!fechaLocal"
                        class="transition-all duration-200 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600"
                    >
                        Guardar
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
