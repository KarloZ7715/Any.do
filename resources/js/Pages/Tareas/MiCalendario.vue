<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { CalendarRange, ChevronLeft, ChevronRight, Calendar as CalendarIcon, Flag, Folder, X } from 'lucide-vue-next'
import { usarSidebar } from '@/composables/usarSidebar'
import LayoutPrincipal from '@/Layouts/LayoutPrincipal.vue'
import QuickAddInput from '@/Components/QuickAddInput.vue'
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

// Nombres de meses y días
const nombresMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
const diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']

// Día seleccionado para ver detalles
const diaSeleccionado = ref(null)
const modalAbierto = ref(false)

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

    router.get(route('tareas.calendario', { mes: nuevoMes, anio: nuevoAnio }), {}, {
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

// Formatear fecha legible
const formatearFechaLegible = (fecha) => {
    const fechaObj = new Date(fecha + 'T00:00:00')
    return `${fechaObj.getDate()} de ${nombresMeses[fechaObj.getMonth()]} de ${fechaObj.getFullYear()}`
}

// Toggle estado de tarea desde modal
const toggleEstadoTarea = (tarea) => {
    const nuevoEstado = tarea.estado === 'pendiente' ? 'completada' : 'pendiente'
    
    // Actualización optimista
    tarea.estado = nuevoEstado
    if (nuevoEstado === 'completada') {
        tarea.fecha_completada = new Date().toISOString()
    } else {
        tarea.fecha_completada = null
    }
    
    // Petición al backend
    router.patch(
        route('tareas.toggle', tarea.id),
        {},
        {
            preserveScroll: true,
            preserveState: true,
        },
    )
}
</script>

<template>
    <LayoutPrincipal>
        <!-- Contenedor principal con fondo uniforme -->
        <div class="h-screen flex flex-col overflow-hidden bg-background">
            <!-- Header con título minimalista y sombra sutil -->
            <div class="flex-shrink-0 px-6 pt-6 pb-4 bg-background">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <!-- Título con estilo consistente -->
                    <div 
                        class="inline-flex items-center gap-3 px-4 py-3 bg-card rounded-xl border border-border shadow-sm hover:shadow-md transition-all duration-200 group"
                    >
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 group-hover:bg-primary/20 transition-colors duration-200">
                            <CalendarRange :size="18" class="text-primary" :stroke-width="2.5" />
                        </div>
                        <div>
                            <h1 class="text-lg font-semibold text-foreground">
                                Mi Calendario
                            </h1>
                            <p class="text-xs text-muted-foreground">
                                {{ nombresMeses[mes - 1] }} {{ anio }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Navegación del calendario -->
                    <div class="flex items-center gap-2">
                        <Button variant="outline" size="sm" @click="navegarMes(-1)">
                            <ChevronLeft :size="16" />
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            @click="router.get(route('tareas.calendario'))"
                            class="hidden sm:flex"
                        >
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

            <!-- Contenedor del calendario con scroll -->
            <div class="flex-1 overflow-y-auto px-6 pb-6 bg-background scrollbar-thin">
                <!-- Calendario con estilo consistente -->
                <div class="bg-card rounded-xl border border-border shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden animate-fade-in">
                    <!-- Header días de la semana -->
                    <div class="grid grid-cols-7 border-b border-border bg-gradient-to-b from-muted/50 to-transparent dark:from-muted/30 dark:to-transparent">
                        <div
                            v-for="dia in diasSemana"
                            :key="dia"
                            class="p-1.5 sm:p-2 md:p-3 text-center text-xs sm:text-sm font-semibold text-muted-foreground"
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
                                'min-h-[80px] sm:min-h-[90px] md:min-h-[100px] p-1 sm:p-1.5 md:p-2 border-r border-b border-border transition-all duration-200',
                                dia ? 'cursor-pointer hover:bg-accent' : 'bg-muted/30',
                                dia?.esHoy ? 'bg-primary/10 hover:bg-primary/20' : '',
                            ]"
                            @click="dia && seleccionarDia(dia)"
                        >
                            <template v-if="dia">
                                <div class="flex items-center justify-between mb-1 sm:mb-2">
                                    <span
                                        :class="[
                                            'text-xs sm:text-sm font-medium transition-all',
                                            dia.esHoy
                                                ? 'w-6 h-6 sm:w-7 sm:h-7 flex items-center justify-center rounded-full bg-primary text-primary-foreground shadow-md text-xs'
                                                : 'text-foreground',
                                        ]"
                                    >
                                        {{ dia.numero }}
                                    </span>
                                </div>

                                <!-- Indicador de tareas -->
                                <div v-if="dia.cantidadTareas > 0" class="space-y-0.5 sm:space-y-1">
                                    <div
                                        v-for="tarea in dia.tareas.slice(0, 2)"
                                        :key="tarea.id"
                                        :class="[
                                            'text-[10px] sm:text-xs p-1 sm:p-1.5 rounded text-left truncate transition-all',
                                            tarea.estado === 'completada'
                                                ? 'bg-muted text-muted-foreground line-through opacity-60'
                                                : 'bg-primary/20 text-primary hover:bg-primary/30'
                                        ]"
                                    >
                                        {{ tarea.titulo }}
                                    </div>
                                    <div
                                        v-if="dia.cantidadTareas > 2"
                                        class="text-[10px] sm:text-xs text-muted-foreground font-medium pl-1 sm:pl-1.5"
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
                                {{ diaSeleccionado?.cantidadTareas }} {{ diaSeleccionado?.cantidadTareas === 1 ? 'tarea' : 'tareas' }}
                            </p>
                        </div>
                    </div>
                </DialogHeader>

                <div class="overflow-y-auto px-6 py-4 max-h-[500px] scrollbar-thin">
                    <div class="space-y-2">
                        <div
                            v-for="(tarea, tareaIndex) in diaSeleccionado?.tareas"
                            :key="tarea.id"
                            :class="[
                                'p-4 rounded-lg border transition-all group animate-slide-in',
                                tarea.estado === 'completada'
                                    ? 'bg-muted/50 border-border'
                                    : 'bg-card border-border hover:border-primary/50'
                            ]"
                            :style="{ animationDelay: `${tareaIndex * 30}ms` }"
                        >
                            <div class="flex items-start gap-3">
                                <!-- Checkbox -->
                                <div class="mt-0.5">
                                    <CheckboxRedondo
                                        :checked="tarea.estado === 'completada'"
                                        @cambiar="toggleEstadoTarea(tarea)"
                                    />
                                </div>

                                <!-- Contenido -->
                                <div class="flex-1 min-w-0">
                                    <h3
                                        :class="[
                                            'font-medium text-foreground transition-all',
                                            tarea.estado === 'completada' ? 'line-through opacity-60' : ''
                                        ]"
                                    >
                                        {{ tarea.titulo }}
                                    </h3>
                                    <p
                                        v-if="tarea.descripcion"
                                        :class="[
                                            'text-sm text-muted-foreground mt-1',
                                            tarea.estado === 'completada' ? 'opacity-60' : ''
                                        ]"
                                    >
                                        {{ tarea.descripcion }}
                                    </p>
                                    
                                    <!-- Metadatos -->
                                    <div class="flex items-center gap-2 mt-3 flex-wrap">
                                        <!-- Prioridad -->
                                        <span
                                            class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-md font-medium"
                                            :class="{
                                                'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300': tarea.prioridad === 1,
                                                'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300': tarea.prioridad === 2,
                                                'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300': tarea.prioridad === 3,
                                            }"
                                        >
                                            <Flag :size="12" />
                                            {{ tarea.prioridad === 1 ? 'Alta' : tarea.prioridad === 2 ? 'Media' : 'Baja' }}
                                        </span>
                                        
                                        <!-- Categoría -->
                                        <span
                                            v-if="tarea.categoria"
                                            class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-md font-medium"
                                            :style="{
                                                backgroundColor: tarea.categoria.color + '20',
                                                color: tarea.categoria.color
                                            }"
                                        >
                                            <Folder :size="12" />
                                            {{ tarea.categoria.nombre }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-border bg-gradient-to-t from-muted/30 to-transparent dark:from-muted/20 dark:to-transparent">
                    <Button variant="outline" class="w-full" @click="cerrarModal">
                        Cerrar
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    </LayoutPrincipal>
</template>

<style scoped>
/* ANIMACIONES */

/* Fade in para elementos principales */
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

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

.animate-fade-in {
    animation: fade-in 0.4s ease-out forwards;
    opacity: 0;
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

/* Hover suave en elementos clickeables */
.group:hover {
    transform: translateY(-1px);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Transiciones suaves en todos los elementos interactivos */
button, a, [role="button"], .clickable {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

button:hover, a:hover, [role="button"]:hover, .clickable:hover {
    transform: translateY(-1px);
}

/* MEJORAS DE ACCESIBILIDAD */

/* Focus visible para navegación por teclado */
button:focus-visible,
a:focus-visible,
[role="button"]:focus-visible,
.clickable:focus-visible {
    outline: 2px solid rgb(99, 102, 241);
    outline-offset: 2px;
    border-radius: 0.375rem;
}

/* PERFORMANCE OPTIMIZATIONS*/

/* Hardware acceleration para animaciones suaves */
.animate-fade-in,
.animate-slide-in {
    will-change: transform, opacity;
}

/* Después de la animación, remover will-change */
.animate-fade-in:not(:hover),
.animate-slide-in:not(:hover) {
    will-change: auto;
}

/* RESPONSIVE IMPROVEMENTS*/

/* Ajustes para móviles */
@media (max-width: 640px) {
    .animate-fade-in,
    .animate-slide-in {
        animation-duration: 0.2s;
    }
}
</style>
