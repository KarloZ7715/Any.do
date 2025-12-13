<script setup>
import { ref, toRef } from 'vue'
import { router } from '@inertiajs/vue3'
import { CalendarRange, ChevronLeft, ChevronRight, Flag, Folder } from 'lucide-vue-next'
import { usarSidebar } from '@/composables/usarSidebar'
import { usarCalendario } from '@/composables/usarCalendario'
import LayoutPrincipal from '@/Layouts/LayoutPrincipal.vue'
import QuickAddInput from '@/Components/QuickAddInput.vue'
import HeaderPagina from '@/Components/ui/HeaderPagina.vue'
import BadgePrioridad from '@/Components/ui/BadgePrioridad.vue'
import BadgeCategoria from '@/Components/ui/BadgeCategoria.vue'
import { Button } from '@/Components/ui/button'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/Components/ui/dialog'
import CheckboxRedondo from '@/Components/CheckboxRedondo.vue'

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

// Composable del sidebar para responsive
const { estaColapsado } = usarSidebar()

// Usar composable de calendario
const {
    diasDelMes,
    nombreMesActual,
    nombresMeses,
    diasSemana,
    formatearFechaLegible,
    calcularNuevoMes,
} = usarCalendario(toRef(props, 'mes'), toRef(props, 'anio'), toRef(props, 'tareasPorDia'))

// Día seleccionado para ver detalles
const diaSeleccionado = ref(null)
const modalAbierto = ref(false)

// Navegar mes usando el composable
const navegarMes = (direccion) => {
    const { mes, anio } = calcularNuevoMes(direccion)
    router.get(route('tareas.calendario', { mes, anio }), {}, {
        preserveState: false,
        preserveScroll: false,
    })
}

// Seleccionar día
const seleccionarDia = (dia) => {
    if (dia && dia.cantidadTareas > 0) {
        diaSeleccionado.value = dia
        modalAbierto.value = true
    }
}

// Cerrar modal
const cerrarModal = () => {
    modalAbierto.value = false
    setTimeout(() => {
        diaSeleccionado.value = null
    }, 200)
}

// Toggle estado de tarea desde modal
const toggleEstadoTarea = (tarea) => {
    const nuevoEstado = tarea.estado === 'pendiente' ? 'completada' : 'pendiente'

    // Actualización optimista
    tarea.estado = nuevoEstado
    tarea.fecha_completada = nuevoEstado === 'completada' ? new Date().toISOString() : null

    // Petición al backend
    router.patch(route('tareas.toggle', tarea.id), {}, {
        preserveScroll: true,
        preserveState: true,
    })
}
</script>

<template>
    <LayoutPrincipal>
        <!-- Contenedor principal con fondo uniforme -->
        <div class="h-screen flex flex-col overflow-hidden bg-background">
            <!-- Header con título minimalista y sombra sutil -->
            <div class="shrink-0 px-6 pt-6 pb-4 bg-background">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <!-- Header -->
                    <HeaderPagina titulo="Mi Calendario" :subtitulo="`${nombreMesActual} ${anio}`">
                        <template #icono>
                            <CalendarRange :size="18" class="text-primary" :stroke-width="2.5" />
                        </template>
                    </HeaderPagina>

                    <!-- Navegación del calendario -->
                    <div class="flex items-center gap-2">
                        <Button variant="outline" size="sm" @click="navegarMes(-1)">
                            <ChevronLeft :size="16" />
                        </Button>
                        <Button variant="outline" size="sm" @click="router.get(route('tareas.calendario'))"
                            class="hidden sm:flex">
                            Hoy
                        </Button>
                        <Button variant="outline" size="sm" @click="navegarMes(1)">
                            <ChevronRight :size="16" />
                        </Button>
                    </div>
                </div>

                <!-- Quick Add Input -->
                <QuickAddInput :categorias="categorias" placeholder="+ Agregar tarea" />
            </div>

            <!-- Contenedor del calendario -->
            <div class="flex-1 overflow-y-auto px-6 pb-6 bg-background scrollbar-thin">
                <!-- Calendario -->
                <div
                    class="bg-card rounded-xl border border-border shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden animate-fade-in">
                    <!-- Header días de la semana -->
                    <div
                        class="grid grid-cols-7 border-b border-border bg-linear-to-b from-muted/50 to-transparent dark:from-muted/30 dark:to-transparent">
                        <div v-for="dia in diasSemana" :key="dia"
                            class="p-1.5 sm:p-2 md:p-3 text-center text-xs sm:text-sm font-semibold text-muted-foreground">
                            {{ dia }}
                        </div>
                    </div>

                    <!-- Grid de días -->
                    <div class="grid grid-cols-7">
                        <div v-for="(dia, index) in diasDelMes" :key="index" :class="[
                            'min-h-[80px] sm:min-h-[90px] md:min-h-[100px] p-1 sm:p-1.5 md:p-2 border-r border-b border-border transition-all duration-200',
                            dia ? 'cursor-pointer hover:bg-accent' : 'bg-muted/30',
                            dia?.esHoy ? 'bg-primary/10 hover:bg-primary/20' : '',
                        ]" @click="dia && seleccionarDia(dia)">
                            <template v-if="dia">
                                <div class="flex items-center justify-between mb-1 sm:mb-2">
                                    <span :class="[
                                        'text-xs sm:text-sm font-medium transition-all',
                                        dia.esHoy
                                            ? 'w-6 h-6 sm:w-7 sm:h-7 flex items-center justify-center rounded-full bg-primary text-primary-foreground shadow-md text-xs'
                                            : 'text-foreground',
                                    ]">
                                        {{ dia.numero }}
                                    </span>
                                </div>

                                <!-- Indicador de tareas -->
                                <div v-if="dia.cantidadTareas > 0" class="space-y-0.5 sm:space-y-1">
                                    <div v-for="tarea in dia.tareas.slice(0, 2)" :key="tarea.id" :class="[
                                        'text-[10px] sm:text-xs p-1 sm:p-1.5 rounded text-left truncate transition-all',
                                        tarea.estado === 'completada'
                                            ? 'bg-muted text-muted-foreground line-through opacity-60'
                                            : 'bg-primary/20 text-primary hover:bg-primary/30'
                                    ]">
                                        {{ tarea.titulo }}
                                    </div>
                                    <div v-if="dia.cantidadTareas > 2"
                                        class="text-[10px] sm:text-xs text-muted-foreground font-medium pl-1 sm:pl-1.5">
                                        +{{ dia.cantidadTareas - 2 }} más
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal detalle del día -->
        <Dialog v-model:open="modalAbierto">
            <DialogContent class="sm:max-w-[600px] max-h-[700px] p-0 overflow-hidden">
                <DialogHeader class="px-6 pt-6 pb-4 border-b border-border">
                    <div class="flex items-start justify-between">
                        <div>
                            <DialogTitle class="text-xl font-bold text-foreground">
                                {{ diaSeleccionado ? formatearFechaLegible(diaSeleccionado.fecha) : '' }}
                            </DialogTitle>
                            <p class="text-sm text-muted-foreground mt-1">
                                {{ diaSeleccionado?.cantidadTareas }} {{ diaSeleccionado?.cantidadTareas === 1 ? 'tarea'
                                    :
                                    'tareas' }}
                            </p>
                        </div>
                    </div>
                </DialogHeader>

                <div class="overflow-y-auto px-6 py-4 max-h-[500px] scrollbar-thin">
                    <div class="space-y-2">
                        <div v-for="(tarea, tareaIndex) in diaSeleccionado?.tareas" :key="tarea.id" :class="[
                            'p-4 rounded-lg border transition-all group animate-slide-in',
                            tarea.estado === 'completada'
                                ? 'bg-muted/50 border-border'
                                : 'bg-card border-border hover:border-primary/50'
                        ]" :style="{ animationDelay: `${tareaIndex * 30}ms` }">
                            <div class="flex items-start gap-3">
                                <!-- Checkbox -->
                                <div class="mt-0.5">
                                    <CheckboxRedondo :checked="tarea.estado === 'completada'"
                                        @cambiar="toggleEstadoTarea(tarea)" />
                                </div>

                                <!-- Contenido -->
                                <div class="flex-1 min-w-0">
                                    <h3 :class="[
                                        'font-medium text-foreground transition-all',
                                        tarea.estado === 'completada' ? 'line-through opacity-60' : ''
                                    ]">
                                        {{ tarea.titulo }}
                                    </h3>
                                    <p v-if="tarea.descripcion" :class="[
                                        'text-sm text-muted-foreground mt-1',
                                        tarea.estado === 'completada' ? 'opacity-60' : ''
                                    ]">
                                        {{ tarea.descripcion }}
                                    </p>

                                    <!-- Metadatos -->
                                    <div class="flex items-center gap-2 mt-3 flex-wrap">
                                        <!-- Prioridad -->
                                        <BadgePrioridad :prioridad="tarea.prioridad" />

                                        <!-- Categoría -->
                                        <BadgeCategoria v-if="tarea.categoria" :categoria="tarea.categoria" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="px-6 py-4 border-t border-border bg-linear-to-t from-muted/30 to-transparent dark:from-muted/20 dark:to-transparent">
                    <Button variant="outline" class="w-full" @click="cerrarModal">
                        Cerrar
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    </LayoutPrincipal>
</template>
