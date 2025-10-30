<script setup>
import { CalendarDays } from 'lucide-vue-next'
import { ref, onMounted, nextTick } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import QuickAddInput from '@/Components/QuickAddInput.vue'
import TareaCardMinimalista from '@/Components/TareaCardMinimalista.vue'
import ModalEditarTarea from '@/Components/ModalEditarTarea.vue'
import { usarDragDropTareas } from '@/composables/usarDragDropTareas'

const props = defineProps({
    tareasPorFecha: {
        type: Object,
        required: true,
    },
    categorias: {
        type: Array,
        default: () => [],
    },
})

// Nombres de días de la semana
const diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
const diasSemanaCortos = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']

// Refs para las listas de drag & drop
const listasRefs = ref([])

// Composable drag & drop
const { inicializarDragDrop, actualizarFechaTarea } = usarDragDropTareas()

// Generar array de 7 días desde hoy
const generarSieteDias = () => {
    const dias = []
    const hoy = new Date()
    hoy.setHours(0, 0, 0, 0)

    for (let i = 0; i < 7; i++) {
        const fecha = new Date(hoy)
        fecha.setDate(hoy.getDate() + i)

        const fechaStr = fecha.toISOString().split('T')[0]
        const tareas = props.tareasPorFecha[fechaStr] || []

        // Separar tareas pendientes y completadas
        const tareasPendientes = tareas.filter(t => t.estado === 'pendiente')
        const tareasCompletadas = tareas.filter(t => t.estado === 'completada')

        // Ordenar completadas: últimas completadas primero
        tareasCompletadas.sort((a, b) => {
            return new Date(b.fecha_completada) - new Date(a.fecha_completada)
        })

        let etiqueta = ''
        let subEtiqueta = ''

        if (i === 0) {
            etiqueta = 'Hoy'
            subEtiqueta = diasSemanaCortos[fecha.getDay()]
        } else if (i === 1) {
            etiqueta = 'Mañana'
            subEtiqueta = diasSemanaCortos[fecha.getDay()]
        } else {
            etiqueta = diasSemana[fecha.getDay()]
            subEtiqueta = ''
        }

        dias.push({
            fecha: fechaStr,
            fechaObj: fecha,
            etiqueta,
            subEtiqueta,
            tareasPendientes,
            tareasCompletadas,
            todasLasTareas: [...tareasPendientes, ...tareasCompletadas],
            totalTareas: tareas.length,
        })
    }

    return dias
}

const sieteDias = computed(() => generarSieteDias())

// Contador total de tareas pendientes
const totalTareasPendientes = computed(() => {
    return sieteDias.value.reduce((total, dia) => total + dia.tareasPendientes.length, 0)
})

// Estado del modal de edición
const tareaSeleccionada = ref(null)
const modalEditarAbierto = ref(false)

// Abrir modal para editar tarea
const abrirModalEdicion = (tarea) => {
    tareaSeleccionada.value = tarea
    modalEditarAbierto.value = true
}

// Handler cuando se suelta una tarea en otra columna
const manejarDrop = (data) => {
    // Extraer el elemento arrastrado
    const tareaElement = data.draggedNode?.el
    if (!tareaElement) return
    
    // Obtener el parent destino (el contenedor con data-fecha)
    const parentElement = tareaElement.parentElement
    if (!parentElement) return
    
    const tareaId = tareaElement.getAttribute('data-tarea-id')
    const nuevaFecha = parentElement.getAttribute('data-fecha')

    if (tareaId && nuevaFecha) {
        actualizarFechaTarea(parseInt(tareaId), nuevaFecha)
    }
}

// Inicializar drag & drop después de montar
onMounted(() => {
    nextTick(() => {
        if (listasRefs.value.length > 0) {
            inicializarDragDrop(listasRefs.value, manejarDrop)
        }
        
        // Scroll a la columna del día actual (primera columna: Hoy)
        const primerColumna = listasRefs.value[0]
        if (primerColumna) {
            primerColumna.parentElement?.scrollIntoView({ 
                behavior: 'instant', 
                block: 'nearest',
                inline: 'start',
            })
        }
    })
})
</script>

<template>
    <AuthenticatedLayout>
        <div class="h-screen flex flex-col bg-gray-50 dark:bg-gray-950">
            <!-- Header fijo -->
            <div class="flex-shrink-0 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                        <CalendarDays :size="20" class="text-indigo-600 dark:text-indigo-400" :stroke-width="2" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                            Próximos 7 Días
                        </h1>
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            {{ totalTareasPendientes }} {{ totalTareasPendientes === 1 ? 'tarea pendiente' : 'tareas pendientes' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Contenedor horizontal con scroll -->
            <div class="flex-1 overflow-x-auto overflow-y-hidden">
                <div class="h-full flex gap-4 px-6 py-6 min-w-max">
                    <!-- Columna por cada día -->
                    <div
                        v-for="dia in sieteDias"
                        :key="dia.fecha"
                        class="flex-shrink-0 w-80 flex flex-col bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden"
                    >
                        <!-- Header de la columna -->
                        <div class="flex-shrink-0 px-4 py-3 border-b border-gray-200 dark:border-gray-800">
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ dia.etiqueta }}
                                <span v-if="dia.subEtiqueta" class="text-xs font-normal text-gray-500 dark:text-gray-400 ml-1">
                                    {{ dia.subEtiqueta }}
                                </span>
                            </h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ dia.totalTareas }} {{ dia.totalTareas === 1 ? 'tarea' : 'tareas' }}
                            </p>
                        </div>

                        <!-- Wrapper para lista de tareas -->
                        <div class="flex-1 overflow-y-auto p-3 relative">
                            <!-- Lista de tareas con scroll vertical -->
                            <div
                                :ref="el => listasRefs[sieteDias.indexOf(dia)] = el"
                                :data-fecha="dia.fecha"
                                class="space-y-2 min-h-[60px]"
                            >
                                <!-- Tareas de este día -->
                                <div
                                    v-for="tarea in dia.todasLasTareas"
                                    :key="tarea.id"
                                    :data-tarea-id="tarea.id"
                                >
                                    <TareaCardMinimalista :tarea="tarea" @editar="abrirModalEdicion" />
                                </div>
                            </div>

                            <!-- Empty state si no hay tareas (fuera del contenedor drag) -->
                            <div
                                v-if="dia.totalTareas === 0"
                                class="absolute inset-0 flex flex-col items-center justify-center text-center pointer-events-none"
                            >
                                <p class="text-sm text-gray-400 dark:text-gray-600">
                                    Sin tareas
                                </p>
                            </div>
                        </div>

                        <!-- Quick Add Input al final de cada columna -->
                        <div class="flex-shrink-0 p-3 border-t border-gray-200 dark:border-gray-800">
                            <QuickAddInput
                                :categorias="categorias"
                                :fecha-predeterminada="dia.fecha"
                                placeholder="+ Agregar tarea"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de edición de tarea -->
        <ModalEditarTarea
            v-model:open="modalEditarAbierto"
            :tarea="tareaSeleccionada"
            :categorias="categorias"
            @cerrar="tareaSeleccionada = null"
        />
    </AuthenticatedLayout>
</template>

<style scoped>
/* Estilos para drag & drop */
:deep(.dragging) {
    opacity: 0.5;
    transform: rotate(2deg);
    cursor: grabbing !important;
}

:deep(.drop-zone-active) {
    background-color: rgba(99, 102, 241, 0.1);
    border: 2px dashed rgb(99, 102, 241);
}

:deep([data-tarea-id]) {
    cursor: grab;
}

:deep([data-tarea-id]:active) {
    cursor: grabbing;
}
</style>
