<script setup>
import { ref, watch, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { X, Calendar, Flag, Folder, Trash2, Plus } from 'lucide-vue-next'
import { Dialog, DialogContent } from '@/Components/ui/dialog'
import { Button } from '@/Components/ui/button'
import { Textarea } from '@/Components/ui/textarea'
import ModalCategoria from '@/Components/QuickAdd/ModalCategoria.vue'
import ModalFecha from '@/Components/QuickAdd/ModalFecha.vue'
import ModalPrioridad from '@/Components/QuickAdd/ModalPrioridad.vue'

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

// Estados de modals para iconos
const modalCategoriaAbierto = ref(false)
const modalFechaAbierto = ref(false)
const modalPrioridadAbierto = ref(false)

// Estado del formulario
const form = ref({
    titulo: '',
    descripcion: '',
    categoria_id: null,
    prioridad: 2,
    fecha_vencimiento: null,
})

// Estado de subtareas
const subtareas = ref([])
const nuevaSubtarea = ref('')

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
        subtareas.value = tarea.subtareas || []
    }
}, { immediate: true })

// Computeds
const esEdicion = computed(() => !!props.tarea)

const nombreCategoriaSeleccionada = computed(() => {
    if (!form.value.categoria_id) return null
    const categoria = props.categorias.find(c => c.id === form.value.categoria_id)
    return categoria?.nombre || null
})

const fechaFormateada = computed(() => {
    if (!form.value.fecha_vencimiento) return null
    const fecha = new Date(form.value.fecha_vencimiento + 'T00:00:00')
    const dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']
    return dias[fecha.getDay()]
})

const colorPrioridad = computed(() => {
    const colores = {
        1: '#EF4444',
        2: '#EAB308',
        3: '#22C55E',
    }
    return colores[form.value.prioridad]
})

// Handlers de modals
const seleccionarCategoria = (categoriaId) => {
    form.value.categoria_id = categoriaId
}

const seleccionarFecha = ({ fecha }) => {
    form.value.fecha_vencimiento = fecha
}

const seleccionarPrioridad = (prioridad) => {
    form.value.prioridad = prioridad
}

// Guardar cambios
const guardar = () => {
    if (!form.value.titulo.trim()) return

    const payload = {
        titulo: form.value.titulo,
        descripcion: form.value.descripcion,
        categoria_id: form.value.categoria_id,
        prioridad: form.value.prioridad,
        fecha_vencimiento: form.value.fecha_vencimiento,
        estado: props.tarea?.estado || 'pendiente',
    }

    router.patch(
        route('tareas.update', props.tarea.id),
        payload,
        {
            preserveScroll: true,
            onSuccess: () => {
                cerrarModal()
            },
        },
    )
}

// Agregar subtarea
const agregarSubtarea = () => {
    if (!nuevaSubtarea.value.trim()) return

    router.post(
        route('subtareas.store', props.tarea.id),
        { texto: nuevaSubtarea.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                nuevaSubtarea.value = ''
            },
        },
    )
}

// Toggle subtarea
const toggleSubtarea = (subtarea) => {
    router.post(
        route('subtareas.toggle', { tarea: props.tarea.id, subtarea: subtarea.id }),
        {},
        { preserveScroll: true },
    )
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
</script>

<template>
    <Dialog :open="open" @update:open="val => emit('update:open', val)">
        <DialogContent class="sm:max-w-[650px] max-h-[90vh] overflow-y-auto p-0">
            <!-- Header con título editable -->
            <div class="p-6 pb-4">
                <!-- Título como input invisible -->
                <input
                    v-model="form.titulo"
                    type="text"
                    placeholder="Título de la tarea"
                    class="w-full text-2xl font-semibold bg-transparent border-none outline-none focus:ring-0 p-0 text-gray-900 dark:text-white placeholder:text-gray-400"
                    @keydown.enter.prevent="guardar"
                />

                <!-- Iconos de metadatos (Categoría, Fecha, Prioridad) -->
                <div class="flex items-center gap-2 mt-4">
                    <!-- Categoría -->
                    <button
                        type="button"
                        @click="modalCategoriaAbierto = true"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-xs font-medium transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                        :class="nombreCategoriaSeleccionada ? 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300' : 'text-gray-500 dark:text-gray-400'"
                    >
                        <Folder :size="14" />
                        <span>{{ nombreCategoriaSeleccionada || 'Categoría' }}</span>
                    </button>

                    <!-- Fecha -->
                    <button
                        type="button"
                        @click="modalFechaAbierto = true"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-xs font-medium transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                        :class="fechaFormateada ? 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300' : 'text-gray-500 dark:text-gray-400'"
                    >
                        <Calendar :size="14" />
                        <span>{{ fechaFormateada || 'Fecha' }}</span>
                    </button>

                    <!-- Prioridad -->
                    <button
                        type="button"
                        @click="modalPrioridadAbierto = true"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-xs font-medium transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                        :class="form.prioridad !== 2 ? 'bg-gray-100 dark:bg-gray-800' : 'text-gray-500 dark:text-gray-400'"
                    >
                        <Flag :size="14" :style="form.prioridad !== 2 ? { color: colorPrioridad } : {}" />
                        <span :style="form.prioridad !== 2 ? { color: colorPrioridad } : {}">
                            {{ form.prioridad === 1 ? 'Alta' : form.prioridad === 2 ? 'Media' : 'Baja' }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Descripción -->
            <div class="px-6 pb-4">
                <Textarea
                    v-model="form.descripcion"
                    placeholder="Agregar descripción..."
                    class="min-h-[80px] resize-y text-sm"
                />
            </div>

            <!-- Subtareas -->
            <div class="px-6 pb-4">
                <div class="space-y-2">
                    <!-- Lista de subtareas existentes -->
                    <div
                        v-for="subtarea in subtareas"
                        :key="subtarea.id"
                        class="flex items-center gap-2 group"
                    >
                        <input
                            type="checkbox"
                            :checked="subtarea.estado === 'completada'"
                            @change="toggleSubtarea(subtarea)"
                            class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500"
                        />
                        <span
                            class="flex-1 text-sm"
                            :class="subtarea.estado === 'completada' ? 'line-through text-gray-400' : 'text-gray-700 dark:text-gray-300'"
                        >
                            {{ subtarea.texto }}
                        </span>
                    </div>

                    <!-- Input para nueva subtarea -->
                    <div class="flex items-center gap-2 pt-2">
                        <Plus :size="16" class="text-gray-400" />
                        <input
                            v-model="nuevaSubtarea"
                            type="text"
                            placeholder="Agregar subtarea"
                            class="flex-1 text-sm bg-transparent border-none outline-none focus:ring-0 p-0 text-gray-700 dark:text-gray-300 placeholder:text-gray-400"
                            @keydown.enter.prevent="agregarSubtarea"
                        />
                    </div>
                </div>
            </div>

            <!-- Footer con botones -->
            <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 dark:border-gray-800">
                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    @click="eliminar"
                    class="text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20"
                >
                    <Trash2 :size="16" class="mr-2" />
                    Eliminar
                </Button>

                <Button type="button" @click="guardar" size="sm">
                    Guardar
                </Button>
            </div>

            <!-- Modals para iconos (reutilizando los del QuickAddInput) -->
            <ModalCategoria
                v-model:open="modalCategoriaAbierto"
                :categorias="categorias"
                :categoria-seleccionada="form.categoria_id"
                @seleccionar="seleccionarCategoria"
            />

            <ModalFecha
                v-model:open="modalFechaAbierto"
                :fecha-seleccionada="form.fecha_vencimiento"
                @seleccionar="seleccionarFecha"
            />

            <ModalPrioridad
                v-model:open="modalPrioridadAbierto"
                :prioridad-seleccionada="form.prioridad"
                @seleccionar="seleccionarPrioridad"
            />
        </DialogContent>
    </Dialog>
</template>
