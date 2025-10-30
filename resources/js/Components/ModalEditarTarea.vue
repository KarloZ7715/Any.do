<script setup>
import { ref, watch, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { X, Calendar, Flag, Folder, Trash2 } from 'lucide-vue-next'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/Components/ui/dialog'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Textarea } from '@/Components/ui/textarea'

const props = defineProps({
    tarea: {
        type: Object,
        default: null,
    },
    categorias: {
        type: Array,
        default: () => [],
    },
    open: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['update:open', 'cerrar'])

// Estado del formulario
const form = ref({
    titulo: '',
    descripcion: '',
    categoria_id: null,
    prioridad: 2,
    fecha_vencimiento: null,
})

// Cargar datos de la tarea si existe (modo edición)
watch(() => props.tarea, (tarea) => {
    if (tarea) {
        form.value = {
            titulo: tarea.titulo || '',
            descripcion: tarea.descripcion || '',
            categoria_id: tarea.categoria?.id || null,
            prioridad: tarea.prioridad || 2,
            fecha_vencimiento: tarea.fecha_vencimiento || null,
        }
    }
}, { immediate: true })

// Determinar si es modo creación o edición
const esEdicion = computed(() => !!props.tarea)

// Guardar cambios
const guardar = () => {
    if (!form.value.titulo.trim()) return

    if (esEdicion.value) {
        // Actualizar tarea existente
        router.patch(
            route('tareas.update', props.tarea.id),
            form.value,
            {
                preserveScroll: true,
                onSuccess: () => {
                    cerrarModal()
                },
            },
        )
    } else {
        // Crear nueva tarea (no usado en este caso, pero por consistencia)
        router.post(
            route('tareas.store'),
            { ...form.value, estado: 'pendiente' },
            {
                preserveScroll: true,
                onSuccess: () => {
                    cerrarModal()
                },
            },
        )
    }
}

// Eliminar tarea
const eliminar = () => {
    if (!confirm('¿Estás seguro de que deseas eliminar esta tarea?')) return

    router.delete(route('tareas.destroy', props.tarea.id), {
        preserveScroll: true,
        onSuccess: () => {
            cerrarModal()
        },
    })
}

// Cerrar modal
const cerrarModal = () => {
    emit('update:open', false)
    emit('cerrar')
}

// Colores de prioridad
const coloresPrioridad = {
    1: { bg: 'bg-red-100 dark:bg-red-900/30', text: 'text-red-700 dark:text-red-300', border: 'border-red-300 dark:border-red-700' },
    2: { bg: 'bg-yellow-100 dark:bg-yellow-900/30', text: 'text-yellow-700 dark:text-yellow-300', border: 'border-yellow-300 dark:border-yellow-700' },
    3: { bg: 'bg-green-100 dark:bg-green-900/30', text: 'text-green-700 dark:text-green-300', border: 'border-green-300 dark:border-green-700' },
}
</script>

<template>
    <Dialog :open="open" @update:open="val => emit('update:open', val)">
        <DialogContent class="sm:max-w-[600px] max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle class="text-xl">
                    {{ esEdicion ? 'Editar Tarea' : 'Nueva Tarea' }}
                </DialogTitle>
            </DialogHeader>

            <form @submit.prevent="guardar" class="space-y-6">
                <!-- Título -->
                <div>
                    <Label for="titulo" class="text-sm font-medium">Título *</Label>
                    <Input
                        id="titulo"
                        v-model="form.titulo"
                        type="text"
                        placeholder="Escribe el título de la tarea..."
                        class="mt-2"
                        required
                    />
                </div>

                <!-- Descripción -->
                <div>
                    <Label for="descripcion" class="text-sm font-medium">Descripción</Label>
                    <Textarea
                        id="descripcion"
                        v-model="form.descripcion"
                        placeholder="Agrega detalles adicionales..."
                        class="mt-2 min-h-[100px]"
                    />
                </div>

                <!-- Categoría -->
                <div>
                    <Label for="categoria" class="text-sm font-medium flex items-center gap-2">
                        <Folder :size="16" />
                        Categoría
                    </Label>
                    <select
                        id="categoria"
                        v-model="form.categoria_id"
                        class="mt-2 w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option :value="null">Sin categoría</option>
                        <option
                            v-for="categoria in categorias"
                            :key="categoria.id"
                            :value="categoria.id"
                        >
                            {{ categoria.nombre }}
                        </option>
                    </select>
                </div>

                <!-- Prioridad -->
                <div>
                    <Label class="text-sm font-medium flex items-center gap-2">
                        <Flag :size="16" />
                        Prioridad
                    </Label>
                    <div class="mt-2 flex gap-2">
                        <button
                            v-for="(valor, nombre) in { Alta: 1, Media: 2, Baja: 3 }"
                            :key="valor"
                            type="button"
                            @click="form.prioridad = valor"
                            :class="[
                                'flex-1 py-2 px-3 rounded-md text-sm font-medium border-2 transition-all',
                                form.prioridad === valor
                                    ? `${coloresPrioridad[valor].bg} ${coloresPrioridad[valor].text} ${coloresPrioridad[valor].border}`
                                    : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-700 hover:border-gray-400',
                            ]"
                        >
                            {{ nombre }}
                        </button>
                    </div>
                </div>

                <!-- Fecha de vencimiento -->
                <div>
                    <Label for="fecha" class="text-sm font-medium flex items-center gap-2">
                        <Calendar :size="16" />
                        Fecha de vencimiento
                    </Label>
                    <Input
                        id="fecha"
                        v-model="form.fecha_vencimiento"
                        type="date"
                        class="mt-2"
                    />
                </div>

                <!-- Botones de acción -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-800">
                    <Button
                        v-if="esEdicion"
                        type="button"
                        variant="destructive"
                        size="sm"
                        @click="eliminar"
                        class="gap-2"
                    >
                        <Trash2 :size="16" />
                        Eliminar
                    </Button>
                    <div v-else />

                    <div class="flex gap-2">
                        <Button type="button" variant="outline" @click="cerrarModal">
                            Cancelar
                        </Button>
                        <Button type="submit">
                            {{ esEdicion ? 'Guardar cambios' : 'Crear tarea' }}
                        </Button>
                    </div>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>
