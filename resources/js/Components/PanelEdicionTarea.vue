<script setup>
import { ref, watch, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Calendar, Flag, Folder, Trash2 } from 'lucide-vue-next'
import { Button } from '@/Components/ui/button'
import { Textarea } from '@/Components/ui/textarea'
import ModalCategoria from '@/Components/QuickAdd/ModalCategoria.vue'
import ModalFecha from '@/Components/QuickAdd/ModalFecha.vue'
import ModalPrioridad from '@/Components/QuickAdd/ModalPrioridad.vue'
import ListaSubtareas from '@/Components/Subtareas/ListaSubtareas.vue'

const props = defineProps({
    tarea: {
        type: Object,
        default: null,
    },
    categorias: {
        type: Array,
        default: () => [],
    },
})

const emit = defineEmits(['subtareasActualizadas'])

// Estado local de subtareas para actualizaciones optimistas
const subtareasLocales = ref([])

// Cargar subtareas iniciales
watch(() => props.tarea, (tarea) => {
    if (tarea && tarea.subtareas) {
        subtareasLocales.value = [...tarea.subtareas]
    } else {
        subtareasLocales.value = []
    }
}, { immediate: true, deep: true })

// Handlers para subtareas con actualizaciones optimistas
const crearSubtarea = (texto) => {
    if (!props.tarea?.id) return
    
    // 1. Actualización optimista (UI primero)
    const subtareaTemporal = {
        id: -Date.now(), // ID temporal negativo
        tarea_id: props.tarea.id,
        texto,
        estado: 'pendiente', // ✅ Usar 'estado' en lugar de 'completada'
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString(),
    }
    subtareasLocales.value.push(subtareaTemporal)
    
    // Emitir cambios al padre
    emit('subtareasActualizadas', {
        tareaId: props.tarea.id,
        subtareas: [...subtareasLocales.value],
    })
    
    // 2. Petición al backend
    router.post(
        route('subtareas.store', { tarea: props.tarea.id }),
        {
            texto,
            tarea_id: props.tarea.id,
        },
        {
            preserveScroll: true,
            onError: (errors) => {
                console.error('Error al crear subtarea:', errors)
                // Revertir en caso de error
                const index = subtareasLocales.value.findIndex(s => s.id === subtareaTemporal.id)
                if (index !== -1) {
                    subtareasLocales.value.splice(index, 1)
                    // Emitir cambios revertidos
                    emit('subtareasActualizadas', {
                        tareaId: props.tarea.id,
                        subtareas: [...subtareasLocales.value],
                    })
                }
            },
        },
    )
}

const actualizarSubtarea = (subtarea, texto) => {
    if (!props.tarea?.id) return
    
    // 1. Actualización optimista (UI primero)
    const index = subtareasLocales.value.findIndex(s => s.id === subtarea.id)
    if (index !== -1) {
        const textoAnterior = subtareasLocales.value[index].texto
        subtareasLocales.value[index].texto = texto
        subtareasLocales.value[index].updated_at = new Date().toISOString()
        
        // Emitir cambios al padre
        emit('subtareasActualizadas', {
            tareaId: props.tarea.id,
            subtareas: [...subtareasLocales.value],
        })
        
        // 2. Petición al backend
        router.patch(
            route('subtareas.update', {
                tarea: props.tarea.id,
                subtarea: subtarea.id,
            }),
            {
                texto,
                tarea_id: props.tarea.id,
            },
            {
                preserveScroll: true,
                onError: (errors) => {
                    console.error('Error al actualizar subtarea:', errors)
                    // Revertir en caso de error
                    if (index !== -1) {
                        subtareasLocales.value[index].texto = textoAnterior
                        // Emitir cambios revertidos
                        emit('subtareasActualizadas', {
                            tareaId: props.tarea.id,
                            subtareas: [...subtareasLocales.value],
                        })
                    }
                },
            },
        )
    }
}

const eliminarSubtarea = (subtarea) => {
    if (!props.tarea?.id) return
    
    // 1. Actualización optimista (UI primero)
    const index = subtareasLocales.value.findIndex(s => s.id === subtarea.id)
    if (index !== -1) {
        const subtareaEliminada = subtareasLocales.value[index]
        subtareasLocales.value.splice(index, 1)
        
        // Emitir cambios al padre
        emit('subtareasActualizadas', {
            tareaId: props.tarea.id,
            subtareas: [...subtareasLocales.value],
        })
        
        // 2. Petición al backend
        router.delete(
            route('subtareas.destroy', {
                tarea: props.tarea.id,
                subtarea: subtarea.id,
            }),
            {
                preserveScroll: true,
                onError: (errors) => {
                    console.error('Error al eliminar subtarea:', errors)
                    // Revertir en caso de error
                    subtareasLocales.value.splice(index, 0, subtareaEliminada)
                    // Emitir cambios revertidos
                    emit('subtareasActualizadas', {
                        tareaId: props.tarea.id,
                        subtareas: [...subtareasLocales.value],
                    })
                },
            },
        )
    }
}

const toggleEstado = (subtarea) => {
    if (!props.tarea?.id) return
    
    // 1. Actualización optimista (UI primero)
    const index = subtareasLocales.value.findIndex(s => s.id === subtarea.id)
    if (index !== -1) {
        const estadoAnterior = subtareasLocales.value[index].estado
        const nuevoEstado = estadoAnterior === 'completada' ? 'pendiente' : 'completada'
        subtareasLocales.value[index].estado = nuevoEstado
        subtareasLocales.value[index].updated_at = new Date().toISOString()
        
        // Emitir cambios al padre
        emit('subtareasActualizadas', {
            tareaId: props.tarea.id,
            subtareas: [...subtareasLocales.value],
        })
        
        // 2. Petición al backend
        router.post(
            route('subtareas.toggle', {
                tarea: props.tarea.id,
                subtarea: subtarea.id,
            }),
            {},
            {
                preserveScroll: true,
                onError: (errors) => {
                    console.error('Error al cambiar estado:', errors)
                    // Revertir en caso de error
                    if (index !== -1) {
                        subtareasLocales.value[index].estado = estadoAnterior
                        // Emitir cambios revertidos
                        emit('subtareasActualizadas', {
                            tareaId: props.tarea.id,
                            subtareas: [...subtareasLocales.value],
                        })
                    }
                },
            },
        )
    }
}

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
    hora_vencimiento: null,
})

// Cargar datos de la tarea
watch(() => props.tarea, (tarea) => {
    if (tarea) {
        // Usar hora_vencimiento del Resource si existe, sino intentar parsearlo
        let fecha = null
        let hora = null
        
        if (tarea.fecha_vencimiento) {
            fecha = tarea.fecha_vencimiento // Ya viene en formato YYYY-MM-DD
            
            // Usar hora_vencimiento del Resource directamente si existe
            if (tarea.hora_vencimiento && tarea.hora_vencimiento !== '00:00') {
                hora = tarea.hora_vencimiento
            }
        }
        
        form.value = {
            titulo: tarea.titulo || '',
            descripcion: tarea.descripcion || '',
            categoria_id: tarea.categoria_id || tarea.categoria?.id || null,
            prioridad: tarea.prioridad || 2,
            fecha_vencimiento: fecha,
            hora_vencimiento: hora,
        }
    }
}, { immediate: true })

// Handlers
const actualizarTarea = () => {
    if (!props.tarea?.id) return

    // Incluir estado (requerido por TareaData)
    const payload = {
        ...form.value,
        estado: props.tarea.estado || 'pendiente',
    }
    
    router.put(route('tareas.update', props.tarea.id), payload, {
        preserveScroll: true,
    })
}

const eliminarTarea = () => {
    if (!props.tarea?.id) return
    
    if (confirm('¿Estás seguro de que deseas eliminar esta tarea?')) {
        router.delete(route('tareas.destroy', props.tarea.id), {
            preserveScroll: true,
        })
    }
}

const seleccionarCategoria = (categoriaId) => {
    form.value.categoria_id = categoriaId
    modalCategoriaAbierto.value = false
    actualizarTarea()
}

const seleccionarPrioridad = (prioridad) => {
    form.value.prioridad = prioridad
    modalPrioridadAbierto.value = false
    actualizarTarea()
}

const seleccionarFecha = (data) => {
    form.value.fecha_vencimiento = data.fecha
    form.value.hora_vencimiento = data.hora
    modalFechaAbierto.value = false
    actualizarTarea()
}

// Computed
const categoriaSeleccionada = computed(() => {
    if (!form.value.categoria_id) return null
    return props.categorias.find(c => c.id == form.value.categoria_id) // Usar == para comparación flexible
})

const iconoPrioridad = computed(() => {
    const prioridades = {
        1: { color: 'text-red-600 dark:text-red-400', label: 'Alta' },
        2: { color: 'text-yellow-600 dark:text-yellow-400', label: 'Media' },
        3: { color: 'text-green-600 dark:text-green-400', label: 'Baja' },
    }
    return prioridades[form.value.prioridad] || prioridades[2]
})

const fechaFormateada = computed(() => {
    if (!form.value.fecha_vencimiento) return 'Sin fecha'
    
    const fecha = new Date(form.value.fecha_vencimiento + 'T00:00:00')
    const hoy = new Date()
    hoy.setHours(0, 0, 0, 0)
    
    const manana = new Date(hoy)
    manana.setDate(manana.getDate() + 1)
    
    if (fecha.getTime() === hoy.getTime()) {
        return form.value.hora_vencimiento ? `Hoy ${form.value.hora_vencimiento}` : 'Hoy'
    } else if (fecha.getTime() === manana.getTime()) {
        return form.value.hora_vencimiento ? `Mañana ${form.value.hora_vencimiento}` : 'Mañana'
    } else {
        const opciones = { day: 'numeric', month: 'short' }
        const fechaStr = fecha.toLocaleDateString('es-ES', opciones)
        return form.value.hora_vencimiento ? `${fechaStr} ${form.value.hora_vencimiento}` : fechaStr
    }
})
</script>

<template>
    <div v-if="tarea" class="h-full flex flex-col bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
        <!-- Header con título editable -->
        <div class="flex-shrink-0 p-4 border-b border-gray-200 dark:border-gray-800">
            <Textarea
                v-model="form.titulo"
                placeholder="Título de la tarea"
                class="text-xl font-semibold resize-none border-0 p-0 focus:ring-0 min-h-[32px]"
                rows="1"
                @blur="actualizarTarea"
                @keydown.enter.prevent="actualizarTarea"
            />
        </div>

        <!-- Área scrolleable -->
        <div class="flex-1 overflow-y-auto scrollbar-thin p-4 space-y-4">
            <!-- Iconos de categoría, fecha, prioridad -->
            <div class="flex items-center gap-2">
                <!-- Categoría -->
                <button
                    @click="modalCategoriaAbierto = true"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                >
                    <Folder :size="16" class="text-indigo-600 dark:text-indigo-400" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">
                        {{ categoriaSeleccionada?.nombre || 'Sin categoría' }}
                    </span>
                </button>

                <!-- Fecha -->
                <button
                    @click="modalFechaAbierto = true"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                >
                    <Calendar :size="16" class="text-blue-600 dark:text-blue-400" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">
                        {{ fechaFormateada }}
                    </span>
                </button>

                <!-- Prioridad -->
                <button
                    @click="modalPrioridadAbierto = true"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                >
                    <Flag :size="16" :class="iconoPrioridad.color" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">
                        {{ iconoPrioridad.label }}
                    </span>
                </button>
            </div>

            <!-- Descripción -->
            <div>
                <Textarea
                    v-model="form.descripcion"
                    placeholder="Agregar descripción..."
                    class="resize-none min-h-[100px]"
                    @blur="actualizarTarea"
                    @keydown.enter.prevent
                />
            </div>

            <!-- Subtareas -->
            <div v-if="tarea">
                <ListaSubtareas
                    :tarea-id="tarea.id"
                    :subtareas="subtareasLocales"
                    @crear="crearSubtarea"
                    @actualizar="actualizarSubtarea"
                    @eliminar="eliminarSubtarea"
                    @toggle="toggleEstado"
                />
            </div>
        </div>

        <!-- Footer con botón eliminar -->
        <div class="flex-shrink-0 p-4 border-t border-gray-200 dark:border-gray-800">
            <Button
                variant="destructive"
                size="sm"
                class="w-full"
                @click="eliminarTarea"
            >
                <Trash2 :size="16" class="mr-2" />
                Eliminar Tarea
            </Button>
        </div>

        <!-- Modales -->
        <ModalCategoria
            v-model:open="modalCategoriaAbierto"
            :categorias="categorias"
            :categoria-seleccionada-id="form.categoria_id"
            @seleccionar="seleccionarCategoria"
        />

        <ModalFecha
            v-model:open="modalFechaAbierto"
            :fecha="form.fecha_vencimiento"
            :hora="form.hora_vencimiento"
            @seleccionar="seleccionarFecha"
        />

        <ModalPrioridad
            v-model:open="modalPrioridadAbierto"
            :prioridad-seleccionada="form.prioridad"
            @seleccionar="seleccionarPrioridad"
        />
    </div>
</template>

<style scoped>
/* Scrollbar personalizado */
.scrollbar-thin {
    scrollbar-width: thin;
    scrollbar-color: rgba(156, 163, 175, 0.2) transparent;
}

.scrollbar-thin::-webkit-scrollbar {
    width: 6px;
}

.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.2);
    border-radius: 3px;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: rgba(156, 163, 175, 0.4);
}

.dark .scrollbar-thin {
    scrollbar-color: rgba(75, 85, 99, 0.3) transparent;
}

.dark .scrollbar-thin::-webkit-scrollbar-thumb {
    background: rgba(75, 85, 99, 0.3);
}

.dark .scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: rgba(75, 85, 99, 0.5);
}
</style>
