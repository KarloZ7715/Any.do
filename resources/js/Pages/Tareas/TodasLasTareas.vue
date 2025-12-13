<script setup>
import { ref, watch, TransitionGroup } from 'vue'
import { router } from '@inertiajs/vue3'
import { ListTodo, X } from 'lucide-vue-next'
import { usarSidebar } from '@/composables/usarSidebar'
import { usarAgruparTareas } from '@/composables/usarAgruparTareas'
import LayoutPrincipal from '@/Layouts/LayoutPrincipal.vue'
import QuickAddInput from '@/Components/QuickAddInput.vue'
import PanelEdicionTarea from '@/Components/PanelEdicionTarea.vue'
import HeaderPagina from '@/Components/ui/HeaderPagina.vue'
import EmptyState from '@/Components/ui/EmptyState.vue'
import DistincionColapsable from '@/Components/tareas/DistincionColapsable.vue'
import TareaItemSimple from '@/Components/tareas/TareaItemSimple.vue'

const props = defineProps({
    tareas: {
        type: Array,
        required: true,
    },
    categorias: {
        type: Array,
        default: () => [],
    },
    categoriaSeleccionada: {
        type: Object,
        default: null,
    },
})

// Composable del sidebar para responsive
const { estaColapsado } = usarSidebar()

// Estado de las distinciones (expandidas/contraídas)
const distincionesExpandidas = ref({
    hoy: true,
    manana: true,
    proximas: true,
    otras: true,
})

// Tarea seleccionada para mostrar en panel derecho
const tareaSeleccionada = ref(null)

// Copia local de tareas para actualizaciones optimistas
const tareasLocales = ref([...props.tareas])

// Sincronizar con props cuando cambian desde backend
watch(() => props.tareas, (nuevas) => {
    tareasLocales.value = [...nuevas]
}, { deep: true })

// Usar composable para agrupar tareas
const {
    tareasAgrupadas,
    nombresDistinciones,
    obtenerPrimeraTarea,
    hayTareas,
} = usarAgruparTareas(tareasLocales)

// Lista de claves de distinción para iterar en template
const clavesDistinciones = ['hoy', 'manana', 'proximas', 'otras']

// Seleccionar primera tarea al montar
watch(() => props.tareas, (tareas) => {
    if (tareas && tareas.length > 0 && !tareaSeleccionada.value) {
        tareaSeleccionada.value = obtenerPrimeraTarea()
    }

    // Actualizar tarea seleccionada si cambió
    if (tareaSeleccionada.value && tareas) {
        const tareaActualizada = tareas.find(t => t.id === tareaSeleccionada.value.id)
        if (tareaActualizada) {
            tareaSeleccionada.value = tareaActualizada
        }
    }
}, { immediate: true })

// Toggle expansión de distinción
const toggleDistincion = (distincion) => {
    distincionesExpandidas.value[distincion] = !distincionesExpandidas.value[distincion]
}

// Seleccionar tarea
const seleccionarTarea = (tarea) => {
    tareaSeleccionada.value = tarea
}

// Toggle checkbox (marcar como completada/pendiente) - OPTIMISTIC
const toggleCompletada = (tarea) => {
    // 1. Actualizar UI inmediatamente (optimistic)
    const index = tareasLocales.value.findIndex(t => t.id === tarea.id)
    if (index !== -1) {
        const nuevoEstado = tareasLocales.value[index].estado === 'completada' ? 'pendiente' : 'completada'
        tareasLocales.value[index].estado = nuevoEstado

        // Actualizar tarea seleccionada si es la misma
        if (tareaSeleccionada.value?.id === tarea.id) {
            tareaSeleccionada.value = { ...tareasLocales.value[index] }
        }
    }

    // 2. Enviar a backend
    router.patch(route('tareas.toggle', tarea.id), {}, {
        preserveScroll: true,
        onError: () => {
            // Si falla, revertir (se sincronizará con el watch)
            console.error('Error al toggle tarea, revirtiendo...')
        },
    })
}

// Eliminar tarea completada
const eliminarTarea = (tarea) => {
    if (confirm('¿Estás seguro de que deseas eliminar esta tarea?')) {
        // 1. Actualización optimista (UI primero)
        const index = tareasLocales.value.findIndex(t => t.id === tarea.id)
        if (index !== -1) {
            tareasLocales.value.splice(index, 1)
        }

        // 2. Si era la tarea seleccionada, seleccionar otra
        if (tareaSeleccionada.value?.id === tarea.id) {
            tareaSeleccionada.value = obtenerPrimeraTarea()
        }

        // 3. Petición al backend
        router.delete(route('tareas.destroy', tarea.id), {
            preserveScroll: true,
            onError: () => {
                // Si falla, revertir la eliminación optimista
                tareasLocales.value.splice(index, 0, tarea)
                // Restaurar tarea seleccionada si era la misma
                if (tarea.id === tarea.id) {
                    tareaSeleccionada.value = tarea
                }
            },
        })
    }
}

// Limpiar filtro de categoría
const limpiarFiltro = () => {
    router.get(route('tareas.todas'))
}

// Handler para cuando se actualizan las subtareas en el panel
const manejarSubtareasActualizadas = ({ tareaId, subtareas }) => {
    // Actualizar en tareasLocales
    const index = tareasLocales.value.findIndex(t => t.id === tareaId)
    if (index !== -1) {
        tareasLocales.value[index].subtareas = subtareas
    }

    // Actualizar tarea seleccionada si es la misma
    if (tareaSeleccionada.value?.id === tareaId) {
        tareaSeleccionada.value = { ...tareasLocales.value[index] }
    }
}

// Handler para cuando se elimina una tarea desde el panel
const manejarTareaEliminada = (tareaId) => {
    const index = tareasLocales.value.findIndex(t => t.id === tareaId)
    if (index !== -1) {
        tareasLocales.value.splice(index, 1)
    }

    if (tareaSeleccionada.value?.id === tareaId) {
        tareaSeleccionada.value = obtenerPrimeraTarea()
    }
}
</script>

<template>
    <LayoutPrincipal>
        <!-- Contenedor principal -->
        <div class="h-screen flex flex-col overflow-hidden bg-background">
            <!-- Header -->
            <div class="shrink-0 px-6 pt-6 pb-4 bg-background">
                <div class="flex items-start justify-between gap-4">
                    <HeaderPagina
                        :titulo="categoriaSeleccionada ? categoriaSeleccionada.nombre : 'Todas mis Tareas'"
                        :color-icono="categoriaSeleccionada?.color"
                    >
                        <template #icono>
                            <ListTodo
                                :size="18"
                                :stroke-width="2.5"
                                :class="categoriaSeleccionada ? '' : 'text-primary'"
                                :style="categoriaSeleccionada ? { color: categoriaSeleccionada.color } : {}"
                            />
                        </template>
                        <template v-if="categoriaSeleccionada" #extra>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs text-muted-foreground">
                                    Filtrando por categoría
                                </span>
                                <button
                                    @click="limpiarFiltro"
                                    class="text-xs text-primary hover:text-primary/80 font-medium transition-colors flex items-center gap-1"
                                >
                                    <X :size="12" />
                                    Ver todas
                                </button>
                            </div>
                        </template>
                    </HeaderPagina>
                </div>
            </div>

            <!-- Contenedor de dos listas -->
            <div class="flex-1 overflow-hidden px-6 pb-6 flex gap-6">
                <!-- Lista Izquierda: Tareas agrupadas -->
                <div
                    :class="[
                        'flex flex-col bg-card rounded-xl border border-transparent shadow-[0_4px_20px_-4px_rgba(0,0,0,0.25)] dark:shadow-sm overflow-hidden',
                        estaColapsado ? 'w-[50%]' : 'w-[52%]'
                    ]"
                >
                    <!-- Área scrolleable de tareas -->
                    <div class="flex-1 overflow-y-auto scrollbar-thin p-4">
                        <!-- Empty state -->
                        <EmptyState
                            v-if="!hayTareas"
                            :mensaje="categoriaSeleccionada ? 'No hay tareas en esta categoría' : 'No tienes tareas'"
                            :descripcion="categoriaSeleccionada
                                ? 'Agrega tareas a esta categoría o cambia el filtro'
                                : 'Crea tu primera tarea usando el formulario de abajo'"
                        >
                            <template #icono>
                                <ListTodo :size="32" class="text-muted-foreground/50" />
                            </template>
                            <template v-if="categoriaSeleccionada" #accion>
                                <button
                                    @click="limpiarFiltro"
                                    class="text-sm text-primary hover:text-primary/80 font-medium transition-colors flex items-center gap-1"
                                >
                                    <X :size="14" />
                                    Ver todas las tareas
                                </button>
                            </template>
                        </EmptyState>

                        <!-- Distinciones -->
                        <template v-for="clave in clavesDistinciones" :key="clave">
                            <DistincionColapsable
                                v-if="tareasAgrupadas[clave].length > 0"
                                :titulo="nombresDistinciones[clave]"
                                :cantidad="tareasAgrupadas[clave].length"
                                :expandida="distincionesExpandidas[clave]"
                                @toggle="toggleDistincion(clave)"
                            >
                                <TransitionGroup name="tarea-list" tag="div" class="space-y-1">
                                    <TareaItemSimple
                                        v-for="(tarea, tareaIndex) in tareasAgrupadas[clave]"
                                        :key="tarea.id"
                                        :tarea="tarea"
                                        :seleccionada="tareaSeleccionada?.id === tarea.id"
                                        :indice="tareaIndex"
                                        @seleccionar="seleccionarTarea"
                                        @toggle="toggleCompletada"
                                        @eliminar="eliminarTarea"
                                    />
                                </TransitionGroup>
                            </DistincionColapsable>
                        </template>
                    </div>

                    <!-- QuickAddInput -->
                    <div class="shrink-0 p-4 border-t border-transparent bg-linear-to-t from-background/30 to-transparent">
                        <QuickAddInput :categorias="categorias" placeholder="+ Agregar tarea" />
                    </div>
                </div>

                <!-- Lista Derecha: Panel de edición -->
                <div :class="['shrink-0', estaColapsado ? 'w-[50%]' : 'w-[48%]']">
                    <PanelEdicionTarea
                        v-if="tareaSeleccionada"
                        :tarea="tareaSeleccionada"
                        :categorias="categorias"
                        @subtareas-actualizadas="manejarSubtareasActualizadas"
                        @tarea-eliminada="manejarTareaEliminada"
                    />

                    <!-- Empty state cuando no hay tarea seleccionada -->
                    <EmptyState
                        v-else
                        mensaje="No hay tareas"
                        descripcion="Agrega tu primera tarea usando el formulario de la izquierda"
                        class="h-full bg-card rounded-xl border border-transparent shadow-[0_4px_20px_-4px_rgba(0,0,0,0.25)] dark:shadow-sm"
                    >
                        <template #icono>
                            <ListTodo :size="32" class="text-muted-foreground/50" />
                        </template>
                    </EmptyState>
                </div>
            </div>
        </div>
    </LayoutPrincipal>
</template>
