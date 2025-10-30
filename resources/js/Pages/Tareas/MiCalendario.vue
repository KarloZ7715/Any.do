<script setup>
import { CalendarRange, ChevronLeft, ChevronRight } from 'lucide-vue-next'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import QuickAddInput from '@/Components/QuickAddInput.vue'
import { Button } from '@/Components/ui/button'

const props = defineProps({
    tareasPorDia: {
        type: Object,
        required: true,
    },
    mes: {
        type: Number,
        required: true,
    },
    anio: {
        type: Number,
        required: true,
    },
    categorias: {
        type: Array,
        default: () => [],
    },
})

// Nombres de meses y días
const nombresMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
const diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']

// Calcular días del mes
const diasDelMes = computed(() => {
    const primerDia = new Date(props.anio, props.mes - 1, 1)
    const ultimoDia = new Date(props.anio, props.mes, 0)
    const dias = []

    // Agregar espacios vacíos antes del primer día
    for (let i = 0; i < primerDia.getDay(); i++) {
        dias.push(null)
    }

    // Agregar todos los días del mes
    for (let dia = 1; dia <= ultimoDia.getDate(); dia++) {
        const fecha = `${props.anio}-${String(props.mes).padStart(2, '0')}-${String(dia).padStart(2, '0')}`
        const tareas = props.tareasPorDia[fecha] || []

        dias.push({
            numero: dia,
            fecha,
            tareas,
            cantidadTareas: tareas.length,
            esHoy: esHoy(props.anio, props.mes - 1, dia),
        })
    }

    return dias
})

// Verificar si es hoy
const esHoy = (anio, mes, dia) => {
    const hoy = new Date()
    return (
        hoy.getFullYear() === anio &&
        hoy.getMonth() === mes &&
        hoy.getDate() === dia
    )
}

// Navegar mes
const navegarMes = (direccion) => {
    let nuevoMes = props.mes + direccion
    let nuevoAnio = props.anio

    if (nuevoMes < 1) {
        nuevoMes = 12
        nuevoAnio--
    } else if (nuevoMes > 12) {
        nuevoMes = 1
        nuevoAnio++
    }

    router.get(route('tareas.calendario', { mes: nuevoMes, anio: nuevoAnio }))
}

// Día seleccionado para ver detalles
const diaSeleccionado = ref(null)

const seleccionarDia = (dia) => {
    if (dia && dia.cantidadTareas > 0) {
        diaSeleccionado.value = dia
    }
}

const cerrarModal = () => {
    diaSeleccionado.value = null
}

// Formatear fecha legible
const formatearFechaLegible = (fecha) => {
    const fechaObj = new Date(fecha + 'T00:00:00')
    return `${fechaObj.getDate()} de ${nombresMeses[fechaObj.getMonth()]} de ${fechaObj.getFullYear()}`
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-950">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 sticky top-0 z-10">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                                <CalendarRange :size="24" class="text-indigo-600 dark:text-indigo-400" :stroke-width="2" />
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                    Mi Calendario
                                </h1>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ nombresMeses[mes - 1] }} {{ anio }}
                                </p>
                            </div>
                        </div>

                        <!-- Navegación de meses -->
                        <div class="flex items-center gap-2">
                            <Button variant="outline" size="sm" @click="navegarMes(-1)">
                                <ChevronLeft :size="16" />
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                @click="router.get(route('tareas.calendario'))"
                            >
                                Hoy
                            </Button>
                            <Button variant="outline" size="sm" @click="navegarMes(1)">
                                <ChevronRight :size="16" />
                            </Button>
                        </div>
                    </div>

                    <!-- Quick Add Input -->
                    <div class="mt-6">
                        <QuickAddInput :categorias="categorias" />
                    </div>
                </div>
            </div>

            <!-- Contenido Principal -->
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Calendario -->
                <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden">
                    <!-- Header días de la semana -->
                    <div class="grid grid-cols-7 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                        <div
                            v-for="dia in diasSemana"
                            :key="dia"
                            class="p-3 text-center text-sm font-semibold text-gray-700 dark:text-gray-300"
                        >
                            {{ dia }}
                        </div>
                    </div>

                    <!-- Grid de días -->
                    <div class="grid grid-cols-7">
                        <div
                            v-for="(dia, index) in diasDelMes"
                            :key="index"
                            :class="[
                                'min-h-[100px] p-2 border-r border-b border-gray-200 dark:border-gray-800',
                                dia ? 'cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors' : 'bg-gray-50 dark:bg-gray-900',
                                dia?.esHoy ? 'bg-indigo-50 dark:bg-indigo-900/20' : '',
                            ]"
                            @click="dia && seleccionarDia(dia)"
                        >
                            <template v-if="dia">
                                <div class="flex items-center justify-between mb-2">
                                    <span
                                        :class="[
                                            'text-sm font-medium',
                                            dia.esHoy
                                                ? 'w-7 h-7 flex items-center justify-center rounded-full bg-indigo-600 text-white'
                                                : 'text-gray-900 dark:text-white',
                                        ]"
                                    >
                                        {{ dia.numero }}
                                    </span>
                                </div>

                                <!-- Indicador de tareas -->
                                <div v-if="dia.cantidadTareas > 0" class="space-y-1">
                                    <div
                                        v-for="(tarea, idx) in dia.tareas.slice(0, 2)"
                                        :key="tarea.id"
                                        class="text-xs p-1 rounded bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 truncate"
                                    >
                                        {{ tarea.titulo }}
                                    </div>
                                    <div
                                        v-if="dia.cantidadTareas > 2"
                                        class="text-xs text-gray-500 dark:text-gray-400 font-medium"
                                    >
                                        +{{ dia.cantidadTareas - 2 }} más
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal detalle del día (simple) -->
        <Teleport to="body">
            <div
                v-if="diaSeleccionado"
                class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
                @click.self="cerrarModal"
            >
                <div class="bg-white dark:bg-gray-900 rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[80vh] overflow-y-auto">
                    <div class="sticky top-0 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 p-6">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ formatearFechaLegible(diaSeleccionado.fecha) }}
                        </h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ diaSeleccionado.cantidadTareas }} {{ diaSeleccionado.cantidadTareas === 1 ? 'tarea' : 'tareas' }}
                        </p>
                    </div>

                    <div class="p-6 space-y-2">
                        <div
                            v-for="tarea in diaSeleccionado.tareas"
                            :key="tarea.id"
                            class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg"
                        >
                            <h3 class="font-medium text-gray-900 dark:text-white">
                                {{ tarea.titulo }}
                            </h3>
                            <p v-if="tarea.descripcion" class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                {{ tarea.descripcion }}
                            </p>
                            <div class="flex items-center gap-2 mt-2">
                                <span
                                    class="text-xs px-2 py-1 rounded-full"
                                    :class="{
                                        'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300': tarea.prioridad === 1,
                                        'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300': tarea.prioridad === 2,
                                        'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300': tarea.prioridad === 3,
                                    }"
                                >
                                    {{ tarea.prioridad === 1 ? 'Alta' : tarea.prioridad === 2 ? 'Media' : 'Baja' }}
                                </span>
                                <span
                                    v-if="tarea.categoria"
                                    class="text-xs px-2 py-1 rounded-full"
                                    :style="{ backgroundColor: tarea.categoria.color + '20', color: tarea.categoria.color }"
                                >
                                    {{ tarea.categoria.nombre }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="sticky bottom-0 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 p-6">
                        <Button variant="outline" class="w-full" @click="cerrarModal">
                            Cerrar
                        </Button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
