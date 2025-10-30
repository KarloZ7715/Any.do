<script setup>
import { CalendarDays } from 'lucide-vue-next'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import QuickAddInput from '@/Components/QuickAddInput.vue'
import TareaCard from '@/Components/TareaCard.vue'

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

// Procesar tareas por fecha y generar días de la semana
const diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']

const tareasAgrupadas = computed(() => {
    const grupos = []
    const fechas = Object.keys(props.tareasPorFecha).sort()

    fechas.forEach((fecha) => {
        const tareas = props.tareasPorFecha[fecha]
        const fechaObj = new Date(fecha + 'T00:00:00')
        const hoy = new Date()
        hoy.setHours(0, 0, 0, 0)

        // Determinar etiqueta del día
        let etiquetaDia = ''
        const diffDias = Math.floor((fechaObj - hoy) / (1000 * 60 * 60 * 24))

        if (diffDias === 0) {
            etiquetaDia = 'Hoy'
        } else if (diffDias === 1) {
            etiquetaDia = 'Mañana'
        } else {
            etiquetaDia = diasSemana[fechaObj.getDay()]
        }

        grupos.push({
            fecha,
            fechaObj,
            etiquetaDia,
            fechaFormateada: `${fechaObj.getDate()} de ${meses[fechaObj.getMonth()]}`,
            tareas,
            cantidadTareas: tareas.length,
        })
    })

    return grupos
})

// Contador total de tareas pendientes
const totalTareasPendientes = computed(() => {
    return Object.values(props.tareasPorFecha).reduce((total, tareas) => total + tareas.length, 0)
})
</script>

<template>
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-950">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 sticky top-0 z-10">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                            <CalendarDays :size="24" class="text-indigo-600 dark:text-indigo-400" :stroke-width="2" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                Próximos 7 Días
                            </h1>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ totalTareasPendientes }} {{ totalTareasPendientes === 1 ? 'tarea pendiente' : 'tareas pendientes' }}
                            </p>
                        </div>
                    </div>

                    <!-- Quick Add Input -->
                    <div class="mt-6">
                        <QuickAddInput :categorias="categorias" />
                    </div>
                </div>
            </div>

            <!-- Contenido Principal -->
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Lista de días agrupados -->
                <div v-if="tareasAgrupadas.length > 0" class="space-y-8">
                    <div
                        v-for="grupo in tareasAgrupadas"
                        :key="grupo.fecha"
                        class="group"
                    >
                        <!-- Header del día -->
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ grupo.etiquetaDia }}
                                </h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ grupo.fechaFormateada }} • {{ grupo.cantidadTareas }} {{ grupo.cantidadTareas === 1 ? 'tarea' : 'tareas' }}
                                </p>
                            </div>
                        </div>

                        <!-- Lista de tareas del día -->
                        <div class="space-y-2">
                            <TareaCard
                                v-for="tarea in grupo.tareas"
                                :key="tarea.id"
                                :tarea="tarea"
                            />
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="p-4 bg-gray-100 dark:bg-gray-800 rounded-full mb-4">
                        <CalendarDays :size="48" class="text-gray-400 dark:text-gray-600" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                        No hay tareas programadas
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 max-w-sm">
                        No tienes tareas pendientes para los próximos 7 días. Usa el campo de arriba para crear una nueva tarea.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
