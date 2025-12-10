<script setup>
import { ref, computed, watch, TransitionGroup } from 'vue'
import { router } from '@inertiajs/vue3'
import { ListTodo, ChevronDown, ChevronRight, X } from 'lucide-vue-next'
import { usarSidebar } from '@/composables/usarSidebar'
import LayoutPrincipal from '@/Layouts/LayoutPrincipal.vue'
import QuickAddInput from '@/Components/QuickAddInput.vue'
import PanelEdicionTarea from '@/Components/PanelEdicionTarea.vue'
import CheckboxRedondo from '@/Components/CheckboxRedondo.vue'

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

// Agrupar tareas por distinción
const tareasAgrupadas = computed(() => {
    // Función para obtener fecha en formato YYYY-MM-DD
    const obtenerFechaFormato = (fecha) => {
        const year = fecha.getFullYear()
        const month = String(fecha.getMonth() + 1).padStart(2, '0')
        const day = String(fecha.getDate()).padStart(2, '0')
        return `${year}-${month}-${day}`
    }

    // Hoy en formato YYYY-MM-DD
    const hoyFormato = obtenerFechaFormato(new Date())

    // Mañana en formato YYYY-MM-DD
    const mananaDate = new Date()
    mananaDate.setDate(mananaDate.getDate() + 1)
    const mananaFormato = obtenerFechaFormato(mananaDate)

    const grupos = {
        hoy: [],
        manana: [],
        proximas: [],
        otras: [],
    }

    tareasLocales.value.forEach(tarea => {
        if (!tarea.fecha_vencimiento) {
            grupos.otras.push(tarea)
        } else {
            // La fecha viene en formato Y-m-d desde el backend
            const fechaTareaFormato = tarea.fecha_vencimiento.split(' ')[0] // Eliminar hora si existe

            if (fechaTareaFormato === hoyFormato) {
                grupos.hoy.push(tarea)
            } else if (fechaTareaFormato === mananaFormato) {
                grupos.manana.push(tarea)
            } else if (fechaTareaFormato > mananaFormato) {
                grupos.proximas.push(tarea)
            } else {
                grupos.otras.push(tarea)
            }
        }
    })

    // Ordenar cada grupo: pendientes primero, completadas al final
    Object.keys(grupos).forEach(key => {
        grupos[key].sort((a, b) => {
            if (a.estado === 'pendiente' && b.estado === 'completada') return -1
            if (a.estado === 'completada' && b.estado === 'pendiente') return 1
            // Ordenar completadas por fecha de completado (últimas primero)
            if (a.estado === 'completada' && b.estado === 'completada') {
                return new Date(b.fecha_completada) - new Date(a.fecha_completada)
            }
            return 0
        })
    })

    return grupos
})

// Seleccionar primera tarea al montar
watch(() => props.tareas, (tareas) => {
    if (tareas && tareas.length > 0 && !tareaSeleccionada.value) {
        // Buscar primera tarea en orden: hoy, mañana, próximas, otras
        const grupos = tareasAgrupadas.value
        tareaSeleccionada.value = grupos.hoy[0] || grupos.manana[0] || grupos.proximas[0] || grupos.otras[0]
    }

    // Actualizar tarea seleccionada si cambió (por ejemplo, subtareas agregadas/editadas)
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
            if (tareasLocales.value.length > 0) {
                const grupos = tareasAgrupadas.value
                tareaSeleccionada.value = grupos.hoy[0] || grupos.manana[0] || grupos.proximas[0] || grupos.otras[0]
            } else {
                tareaSeleccionada.value = null
            }
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

// Nombres de distinciones
const nombresDistinciones = {
    hoy: 'Hoy',
    manana: 'Mañana',
    proximas: 'Próximas',
    otras: 'Otras',
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
    // Eliminar de tareasLocales
    const index = tareasLocales.value.findIndex(t => t.id === tareaId)
    if (index !== -1) {
        tareasLocales.value.splice(index, 1)
    }

    // Seleccionar nueva tarea si la eliminada era la seleccionada
    if (tareaSeleccionada.value?.id === tareaId) {
        if (tareasLocales.value.length > 0) {
            const grupos = tareasAgrupadas.value
            tareaSeleccionada.value = grupos.hoy[0] || grupos.manana[0] || grupos.proximas[0] || grupos.otras[0]
        } else {
            tareaSeleccionada.value = null
        }
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
                    <div
                        class="inline-flex items-center gap-3 px-4 py-3 bg-card rounded-xl border border-transparent shadow-[0_2px_16px_5px_rgba(0,0,0,0.10)] hover:shadow-[0_8px_24px_0px_rgba(0,0,0,0.15)] dark:shadow-none dark:hover:shadow-none transition-all duration-200 group">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg transition-colors duration-200"
                            :class="categoriaSeleccionada
                                ? ''
                                : 'bg-primary/10 group-hover:bg-primary/20'" :style="categoriaSeleccionada ? {
                                    backgroundColor: categoriaSeleccionada.color + '20',
                                } : {}">
                            <ListTodo :size="18" :stroke-width="2.5"
                                :class="categoriaSeleccionada ? '' : 'text-primary'"
                                :style="categoriaSeleccionada ? { color: categoriaSeleccionada.color } : {}" />
                        </div>
                        <div>
                            <h1 class="text-lg font-semibold text-foreground">
                                {{ categoriaSeleccionada ? categoriaSeleccionada.nombre : 'Todas mis Tareas' }}
                            </h1>
                            <div v-if="categoriaSeleccionada" class="flex items-center gap-2 mt-1">
                                <span class="text-xs text-muted-foreground">
                                    Filtrando por categoría
                                </span>
                                <button @click="limpiarFiltro"
                                    class="text-xs text-primary hover:text-primary/80 font-medium transition-colors flex items-center gap-1">
                                    <X :size="12" />
                                    Ver todas
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenedor de dos listas -->
            <div class="flex-1 overflow-hidden px-6 pb-6 flex gap-6">
                <!-- Lista Izquierda: Tareas agrupadas -->
                <div :class="[
                    'flex flex-col bg-card rounded-xl border border-transparent shadow-[0_4px_20px_-4px_rgba(0,0,0,0.25)] dark:shadow-sm overflow-hidden',
                    estaColapsado ? 'w-[50%]' : 'w-[52%]'
                ]">
                    <!-- Área scrolleable de tareas -->
                    <div class="flex-1 overflow-y-auto scrollbar-thin p-4">
                        <!-- Empty state cuando no hay tareas en ninguna distinción -->
                        <div v-if="tareas.length === 0" class="h-full flex flex-col items-center justify-center p-8">
                            <div class="w-16 h-16 rounded-full bg-muted flex items-center justify-center mb-4">
                                <ListTodo :size="32" class="text-muted-foreground/50" />
                            </div>
                            <p class="text-sm font-medium text-foreground mb-1">
                                {{ categoriaSeleccionada ? 'No hay tareas en esta categoría' : 'No tienes tareas' }}
                            </p>
                            <p class="text-xs text-muted-foreground text-center max-w-xs mb-3">
                                {{ categoriaSeleccionada
                                    ? 'Agrega tareas a esta categoría o cambia el filtro'
                                    : 'Crea tu primera tarea usando el formulario de abajo'
                                }}
                            </p>
                            <button v-if="categoriaSeleccionada" @click="limpiarFiltro"
                                class="text-sm text-primary hover:text-primary/80 font-medium transition-colors flex items-center gap-1">
                                <X :size="14" />
                                Ver todas las tareas
                            </button>
                        </div>

                        <!-- Distinción: Hoy -->
                        <div v-if="tareasAgrupadas.hoy.length > 0" class="mb-4">
                            <button @click="toggleDistincion('hoy')"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-accent transition-colors group">
                                <div class="flex items-center gap-2">
                                    <ChevronDown v-if="distincionesExpandidas.hoy" :size="18"
                                        class="text-muted-foreground transition-transform" />
                                    <ChevronRight v-else :size="18"
                                        class="text-muted-foreground transition-transform" />
                                    <span class="text-sm font-semibold text-foreground">
                                        {{ nombresDistinciones.hoy }}
                                    </span>
                                </div>
                                <span v-if="!distincionesExpandidas.hoy"
                                    class="text-xs font-medium text-muted-foreground bg-muted px-2 py-0.5 rounded-full">
                                    {{ tareasAgrupadas.hoy.length }}
                                </span>
                            </button>

                            <!-- Tareas de hoy -->
                            <TransitionGroup v-if="distincionesExpandidas.hoy" name="tarea-list" tag="div"
                                class="mt-2 space-y-1">
                                <div v-for="(tarea, tareaIndex) in tareasAgrupadas.hoy" :key="tarea.id"
                                    @click="seleccionarTarea(tarea)"
                                    class="group/tarea flex items-center gap-3 p-3 rounded-lg cursor-pointer transition-all duration-200 animate-slide-in"
                                    :style="{ animationDelay: `${tareaIndex * 30}ms` }" :class="[
                                        tareaSeleccionada?.id === tarea.id
                                            ? 'bg-primary/10 shadow-[0_2px_8px_0px_rgba(0,0,0,0.06)] dark:shadow-none'
                                            : 'hover:bg-accent'
                                    ]">
                                    <!-- Checkbox -->
                                    <div class="shrink-0">
                                        <CheckboxRedondo :checked="tarea.estado === 'completada'"
                                            @update:checked="toggleCompletada(tarea)" @click.stop />
                                    </div>

                                    <!-- Título de la tarea -->
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium transition-all duration-300" :class="[
                                            tarea.estado === 'completada'
                                                ? 'line-through text-muted-foreground opacity-60'
                                                : 'text-foreground'
                                        ]">
                                            {{ tarea.titulo }}
                                        </p>
                                    </div>

                                    <!-- Botón eliminar -->
                                    <button v-if="tarea.estado === 'completada'" @click.stop="eliminarTarea(tarea)"
                                        class="shrink-0 opacity-0 group-hover/tarea:opacity-100 p-1.5 rounded-lg hover:bg-destructive/10 text-destructive transition-all duration-200">
                                        <X :size="16" />
                                    </button>
                                </div>
                            </TransitionGroup>
                        </div>

                        <!-- Distinción: Mañana -->
                        <div v-if="tareasAgrupadas.manana.length > 0" class="mb-4">
                            <button @click="toggleDistincion('manana')"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-accent transition-colors group">
                                <div class="flex items-center gap-2">
                                    <ChevronDown v-if="distincionesExpandidas.manana" :size="18"
                                        class="text-muted-foreground transition-transform" />
                                    <ChevronRight v-else :size="18"
                                        class="text-muted-foreground transition-transform" />
                                    <span class="text-sm font-semibold text-foreground">
                                        {{ nombresDistinciones.manana }}
                                    </span>
                                </div>
                                <span v-if="!distincionesExpandidas.manana"
                                    class="text-xs font-medium text-muted-foreground bg-muted px-2 py-0.5 rounded-full">
                                    {{ tareasAgrupadas.manana.length }}
                                </span>
                            </button>

                            <TransitionGroup v-if="distincionesExpandidas.manana" name="tarea-list" tag="div"
                                class="mt-2 space-y-1">
                                <div v-for="(tarea, tareaIndex) in tareasAgrupadas.manana" :key="tarea.id"
                                    @click="seleccionarTarea(tarea)"
                                    class="group/tarea flex items-center gap-3 p-3 rounded-lg cursor-pointer transition-all duration-200 animate-slide-in"
                                    :style="{ animationDelay: `${tareaIndex * 30}ms` }" :class="[
                                        tareaSeleccionada?.id === tarea.id
                                            ? 'bg-primary/10'
                                            : 'hover:bg-accent'
                                    ]">
                                    <div class="shrink-0">
                                        <CheckboxRedondo :checked="tarea.estado === 'completada'"
                                            @update:checked="toggleCompletada(tarea)" @click.stop />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium transition-all duration-300" :class="[
                                            tarea.estado === 'completada'
                                                ? 'line-through text-muted-foreground opacity-60'
                                                : 'text-foreground'
                                        ]">
                                            {{ tarea.titulo }}
                                        </p>
                                    </div>
                                    <button v-if="tarea.estado === 'completada'" @click.stop="eliminarTarea(tarea)"
                                        class="shrink-0 opacity-0 group-hover/tarea:opacity-100 p-1.5 rounded-lg hover:bg-destructive/10 text-destructive transition-all duration-200">
                                        <X :size="16" />
                                    </button>
                                </div>
                            </TransitionGroup>
                        </div>

                        <!-- Distinción: Próximas -->
                        <div v-if="tareasAgrupadas.proximas.length > 0" class="mb-4">
                            <button @click="toggleDistincion('proximas')"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-accent transition-colors group">
                                <div class="flex items-center gap-2">
                                    <ChevronDown v-if="distincionesExpandidas.proximas" :size="18"
                                        class="text-muted-foreground transition-transform" />
                                    <ChevronRight v-else :size="18"
                                        class="text-muted-foreground transition-transform" />
                                    <span class="text-sm font-semibold text-foreground">
                                        {{ nombresDistinciones.proximas }}
                                    </span>
                                </div>
                                <span v-if="!distincionesExpandidas.proximas"
                                    class="text-xs font-medium text-muted-foreground bg-muted px-2 py-0.5 rounded-full">
                                    {{ tareasAgrupadas.proximas.length }}
                                </span>
                            </button>

                            <TransitionGroup v-if="distincionesExpandidas.proximas" name="tarea-list" tag="div"
                                class="mt-2 space-y-1">
                                <div v-for="(tarea, tareaIndex) in tareasAgrupadas.proximas" :key="tarea.id"
                                    @click="seleccionarTarea(tarea)"
                                    class="group/tarea flex items-center gap-3 p-3 rounded-lg cursor-pointer transition-all duration-200 animate-slide-in"
                                    :style="{ animationDelay: `${tareaIndex * 30}ms` }" :class="[
                                        tareaSeleccionada?.id === tarea.id
                                            ? 'bg-primary/10'
                                            : 'hover:bg-accent'
                                    ]">
                                    <div class="shrink-0">
                                        <CheckboxRedondo :checked="tarea.estado === 'completada'"
                                            @update:checked="toggleCompletada(tarea)" @click.stop />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium transition-all duration-300" :class="[
                                            tarea.estado === 'completada'
                                                ? 'line-through text-muted-foreground opacity-60'
                                                : 'text-foreground'
                                        ]">
                                            {{ tarea.titulo }}
                                        </p>
                                    </div>
                                    <button v-if="tarea.estado === 'completada'" @click.stop="eliminarTarea(tarea)"
                                        class="shrink-0 opacity-0 group-hover/tarea:opacity-100 p-1.5 rounded-lg hover:bg-destructive/10 text-destructive transition-all duration-200">
                                        <X :size="16" />
                                    </button>
                                </div>
                            </TransitionGroup>
                        </div>

                        <!-- Distinción: Otras -->
                        <div v-if="tareasAgrupadas.otras.length > 0" class="mb-4">
                            <button @click="toggleDistincion('otras')"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-accent transition-colors group">
                                <div class="flex items-center gap-2">
                                    <ChevronDown v-if="distincionesExpandidas.otras" :size="18"
                                        class="text-muted-foreground transition-transform" />
                                    <ChevronRight v-else :size="18"
                                        class="text-muted-foreground transition-transform" />
                                    <span class="text-sm font-semibold text-foreground">
                                        {{ nombresDistinciones.otras }}
                                    </span>
                                </div>
                                <span v-if="!distincionesExpandidas.otras"
                                    class="text-xs font-medium text-muted-foreground bg-muted px-2 py-0.5 rounded-full">
                                    {{ tareasAgrupadas.otras.length }}
                                </span>
                            </button>

                            <TransitionGroup v-if="distincionesExpandidas.otras" name="tarea-list" tag="div"
                                class="mt-2 space-y-1">
                                <div v-for="(tarea, tareaIndex) in tareasAgrupadas.otras" :key="tarea.id"
                                    @click="seleccionarTarea(tarea)"
                                    class="group/tarea flex items-center gap-3 p-3 rounded-lg cursor-pointer transition-all duration-200 animate-slide-in"
                                    :style="{ animationDelay: `${tareaIndex * 30}ms` }" :class="[
                                        tareaSeleccionada?.id === tarea.id
                                            ? 'bg-primary/10'
                                            : 'hover:bg-accent'
                                    ]">
                                    <div class="shrink-0">
                                        <CheckboxRedondo :checked="tarea.estado === 'completada'"
                                            @update:checked="toggleCompletada(tarea)" @click.stop />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium transition-all duration-300" :class="[
                                            tarea.estado === 'completada'
                                                ? 'line-through text-muted-foreground opacity-60'
                                                : 'text-foreground'
                                        ]">
                                            {{ tarea.titulo }}
                                        </p>
                                    </div>
                                    <button v-if="tarea.estado === 'completada'" @click.stop="eliminarTarea(tarea)"
                                        class="shrink-0 opacity-0 group-hover/tarea:opacity-100 p-1.5 rounded-lg hover:bg-destructive/10 text-destructive transition-all duration-200">
                                        <X :size="16" />
                                    </button>
                                </div>
                            </TransitionGroup>
                        </div>
                    </div>

                    <!-- QuickAddInput al final -->
                    <div
                        class="shrink-0 p-4 border-t border-transparent bg-linear-to-t from-background/30 to-transparent">
                        <QuickAddInput :categorias="categorias" placeholder="+ Agregar tarea" />
                    </div>
                </div>

                <!-- Lista Derecha: Panel de edición (48% con sidebar, 50% sin sidebar) -->
                <div :class="[
                    'shrink-0',
                    estaColapsado ? 'w-[50%]' : 'w-[48%]'
                ]">
                    <PanelEdicionTarea v-if="tareaSeleccionada" :tarea="tareaSeleccionada" :categorias="categorias"
                        @subtareas-actualizadas="manejarSubtareasActualizadas"
                        @tarea-eliminada="manejarTareaEliminada" />

                    <!-- Empty state cuando no hay tareas -->
                    <div v-else
                        class="h-full flex flex-col items-center justify-center bg-card rounded-xl border border-transparent shadow-[0_4px_20px_-4px_rgba(0,0,0,0.25)] dark:shadow-sm p-8">
                        <div class="w-16 h-16 rounded-full bg-muted flex items-center justify-center mb-4">
                            <ListTodo :size="32" class="text-muted-foreground/50" />
                        </div>
                        <p class="text-sm font-medium text-foreground mb-1">
                            No hay tareas
                        </p>
                        <p class="text-xs text-muted-foreground text-center max-w-xs">
                            Agrega tu primera tarea usando el formulario de la izquierda
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </LayoutPrincipal>
</template>

<style scoped>
/* Animación fade-in para distinciones expandidas */
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(-4px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out forwards;
}

/* ANIMACIONES ADICIONALES */

/* Slide in para tareas individuales */
@keyframes slide-in {
    from {
        opacity: 0;
        transform: translateX(-10px);
    }

    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.animate-slide-in {
    animation: slide-in 0.3s ease-out forwards;
    opacity: 0;
}

/* SCROLLBAR PERSONALIZADO */

.scrollbar-thin {
    scrollbar-width: thin;
    scrollbar-color: rgba(156, 163, 175, 0.2) transparent;
}

.scrollbar-thin::-webkit-scrollbar {
    width: 6px;
}

.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
    border-radius: 3px;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.2);
    border-radius: 3px;
    transition: background 0.2s ease;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: rgba(156, 163, 175, 0.4);
}

/* Dark mode scrollbar */
.dark .scrollbar-thin {
    scrollbar-color: rgba(75, 85, 99, 0.3) transparent;
}

.dark .scrollbar-thin::-webkit-scrollbar-thumb {
    background: rgba(75, 85, 99, 0.3);
}

.dark .scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: rgba(75, 85, 99, 0.5);
}

/* EFECTOS DE HOVER Y TRANSICIONES */

/* Transición suave para line-through */
.line-through {
    text-decoration-thickness: 2px;
}

/* TransitionGroup animations para tareas */
.tarea-list-move,
.tarea-list-enter-active,
.tarea-list-leave-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.tarea-list-enter-from {
    opacity: 0;
    transform: translateX(-20px) scale(0.95);
}

.tarea-list-leave-to {
    opacity: 0;
    transform: translateX(20px) scale(0.95);
}

.tarea-list-leave-active {
    position: absolute;
    width: 100%;
}

/* EFECTOS DE HOVER EN ELEMENTOS INTERACTIVOS */

/* Hover suave en elementos clickeables */
.group:hover {
    transform: translateY(-1px);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Transiciones suaves en todos los elementos interactivos */
button,
a,
[role="button"],
.clickable {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

button:hover,
a:hover,
[role="button"]:hover,
.clickable:hover {
    transform: translateY(-1px);
}

/* MEJORAS DE ACCESIBILIDAD  */

/* Focus visible para navegación por teclado */
button:focus-visible,
a:focus-visible,
[role="button"]:focus-visible,
.clickable:focus-visible {
    outline: 2px solid rgb(99, 102, 241);
    outline-offset: 2px;
    border-radius: 0.375rem;
}

/* PERFORMANCE OPTIMIZATIONS */

/* Hardware acceleration para animaciones suaves */
.animate-fade-in,
.animate-slide-in,
.tarea-list-move,
.tarea-list-enter-active,
.tarea-list-leave-active {
    will-change: transform, opacity;
}

/* Después de la animación, remover will-change */
.animate-fade-in:not(:hover),
.animate-slide-in:not(:hover) {
    will-change: auto;
}

/* RESPONSIVE IMPROVEMENTS */

/* Ajustes para móviles */
@media (max-width: 640px) {

    .animate-fade-in,
    .animate-slide-in {
        animation-duration: 0.2s;
    }
}
</style>
