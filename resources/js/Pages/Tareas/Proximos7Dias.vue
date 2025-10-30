<script setup>
import { CalendarDays } from 'lucide-vue-next'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import QuickAddInput from '@/Components/QuickAddInput.vue'
import TareaCardMinimalista from '@/Components/TareaCardMinimalista.vue'

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

                        <!-- Lista de tareas con scroll vertical -->
                        <div class="flex-1 overflow-y-auto p-3 space-y-2">
                            <!-- Tareas de este día -->
                            <TareaCardMinimalista
                                v-for="tarea in dia.todasLasTareas"
                                :key="tarea.id"
                                :tarea="tarea"
                            />

                            <!-- Empty state si no hay tareas -->
                            <div
                                v-if="dia.totalTareas === 0"
                                class="flex flex-col items-center justify-center py-8 text-center"
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
    </AuthenticatedLayout>
</template>
