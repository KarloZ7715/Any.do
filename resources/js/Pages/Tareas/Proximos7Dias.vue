<script setup>
import { CalendarDays } from 'lucide-vue-next'
import { ref, onMounted, nextTick, computed } from 'vue'
import LayoutPrincipal from '@/Layouts/LayoutPrincipal.vue'
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

    const tareaId = parseInt(tareaElement.getAttribute('data-tarea-id'))
    const nuevaFecha = parentElement.getAttribute('data-fecha')

    if (tareaId && nuevaFecha) {
        // Buscar la tarea en los datos para obtener la hora existente
        let tareaEncontrada = null
        for (const dia of sieteDias.value) {
            const tarea = dia.todasLasTareas.find(t => t.id === tareaId)
            if (tarea) {
                tareaEncontrada = tarea
                break
            }
        }

        // Usar hora_vencimiento directamente si existe, sino intentar extraerla
        let horaVencimiento = null
        if (tareaEncontrada) {
            // Usar hora_vencimiento del Resource si existe
            if (tareaEncontrada.hora_vencimiento && tareaEncontrada.hora_vencimiento !== '00:00') {
                horaVencimiento = tareaEncontrada.hora_vencimiento
            }
        }

        actualizarFechaTarea(tareaId, nuevaFecha, horaVencimiento)
    }
}

// Inicializar drag & drop después de montar
onMounted(() => {
    nextTick(() => {
        if (listasRefs.value.length > 0) {
            inicializarDragDrop(listasRefs.value, manejarDrop)
        }

        // Scroll automático a la izquierda (mostrar "Hoy" primero)
        const contenedorScroll = document.querySelector('.scrollbar-custom')
        if (contenedorScroll) {
            contenedorScroll.scrollLeft = 0
        }

        // También resetear el scroll del body por si acaso
        window.scrollTo(0, 0)
        document.documentElement.scrollLeft = 0
        document.body.scrollLeft = 0
    })
})
</script>

<template>
    <LayoutPrincipal>
        <!-- Contenedor principal con fondo uniforme-->
        <div class="h-screen flex flex-col overflow-hidden bg-background dark:bg-background">
            <!-- Header con título minimalista y sombra sutil -->
            <div class="flex-shrink-0 px-6 pt-6 pb-4 bg-background dark:bg-background">
                <div
                    class="inline-flex items-center gap-3 px-4 py-3 bg-card dark:bg-card rounded-xl border border-border dark:border-border shadow-sm hover:shadow-md transition-all duration-200 group">
                    <div
                        class="flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 group-hover:bg-primary/20 transition-colors duration-200">
                        <CalendarDays :size="18" class="text-primary" :stroke-width="2.5" />
                    </div>
                    <h1 class="text-lg font-semibold text-foreground dark:text-foreground">
                        Próximos 7 Días
                    </h1>
                </div>
            </div>

            <!-- Contenedor horizontal con scroll (SOLO horizontal, gap 24px) -->
            <div
                class="flex-1 overflow-x-auto overflow-y-hidden px-6 pb-6 bg-background dark:bg-background scrollbar-custom">
                <div class="h-full flex gap-6 pb-2">
                    <!-- Columna por cada día (ancho fijo) -->
                    <div v-for="(dia, index) in sieteDias" :key="dia.fecha"
                        class="flex-shrink-0 w-[320px] flex flex-col animate-fade-in"
                        :style="{ animationDelay: `${index * 50}ms` }">
                        <!-- Box de la lista con sombra y hover effect -->
                        <div
                            class="flex flex-col bg-card dark:bg-card rounded-xl border border-border dark:border-border shadow-sm transition-all duration-200 max-h-full overflow-hidden">
                            <!-- Header de la columna con efecto hover -->
                            <div class="flex-shrink-0 px-4 py-3 border-b border-border dark:border-border">
                                <h2
                                    class="text-sm font-semibold text-foreground dark:text-foreground flex items-center gap-2">
                                    <span class="flex items-center gap-1.5">
                                        {{ dia.etiqueta }}
                                        <span v-if="dia.subEtiqueta"
                                            class="text-xs font-normal text-muted-foreground dark:text-muted-foreground">
                                            {{ dia.subEtiqueta }}
                                        </span>
                                    </span>
                                    <span v-if="dia.tareasPendientes.length > 0"
                                        class="ml-auto flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-primary/10 text-[11px] font-medium text-primary">
                                        {{ dia.tareasPendientes.length }}
                                    </span>
                                </h2>
                            </div>

                            <!-- Lista de tareas con scroll vertical suave -->
                            <div class="flex-1 overflow-y-auto px-3 py-3 min-h-0 scrollbar-thin">
                                <!-- Contenedor drag & drop -->
                                <div :ref="el => listasRefs[sieteDias.indexOf(dia)] = el" :data-fecha="dia.fecha"
                                    class="space-y-2 min-h-[40px] transition-all duration-300 rounded-lg"
                                    :class="{ 'drop-zone-ready': true }">
                                    <!-- Tareas de este día con animación de entrada -->
                                    <div v-for="(tarea, tareaIndex) in dia.todasLasTareas" :key="tarea.id"
                                        :data-tarea-id="tarea.id" class="animate-slide-in"
                                        :style="{ animationDelay: `${tareaIndex * 30}ms` }">
                                        <TareaCardMinimalista :tarea="tarea" @editar="abrirModalEdicion" />
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Add Input al final con sombra superior sutil -->
                            <div class="flex-shrink-0 px-3 pb-3 pt-2 border-t border-border dark:border-border">
                                <QuickAddInput :categorias="categorias" :fecha-predeterminada="dia.fecha"
                                    placeholder="+ Agregar tarea" />
                            </div>
                        </div>
                    </div>

                    <!-- Espaciador al final para mejor scroll -->
                    <div class="flex-shrink-0 w-4"></div>
                </div>
            </div>
        </div>

        <!-- Modal de edición de tarea con animación -->
        <ModalEditarTarea v-model:open="modalEditarAbierto" :tarea="tareaSeleccionada" :categorias="categorias"
            @cerrar="tareaSeleccionada = null" />
    </LayoutPrincipal>
</template>

<style scoped>
/* ========================================
   ANIMACIONES
   ======================================== */

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

/* ========================================
   DRAG & DROP MEJORADO
   ======================================== */

:deep(.dragging) {
    opacity: 0.6;
    transform: rotate(3deg) scale(1.02);
    cursor: grabbing !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

:deep(.drop-zone-active) {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.03) 0%, rgba(99, 102, 241, 0.08) 100%);
    border: 2px dashed rgb(99, 102, 241);
    border-radius: 0.75rem;
    min-height: 100px;
    animation: pulse-border 1.5s ease-in-out infinite;
}

@keyframes pulse-border {

    0%,
    100% {
        border-color: rgb(99, 102, 241);
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.03) 0%, rgba(99, 102, 241, 0.08) 100%);
    }

    50% {
        border-color: rgb(129, 140, 248);
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(99, 102, 241, 0.12) 100%);
    }
}

:deep(.drop-zone-ready) {
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

:deep(.drop-zone-ready:hover) {
    background-color: rgba(99, 102, 241, 0.01);
}

:deep([data-tarea-id]) {
    cursor: grab;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

:deep([data-tarea-id]:hover) {
    transform: translateY(-2px);
}

:deep([data-tarea-id]:active) {
    cursor: grabbing;
    transform: scale(0.98);
}

/* ========================================
   SCROLLBAR PERSONALIZADO - HORIZONTAL
   ======================================== */

.scrollbar-custom {
    scrollbar-width: thin;
    scrollbar-color: rgba(156, 163, 175, 0.3) transparent;
}

.scrollbar-custom::-webkit-scrollbar {
    height: 8px;
}

.scrollbar-custom::-webkit-scrollbar-track {
    background: transparent;
    border-radius: 4px;
}

.scrollbar-custom::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.3);
    border-radius: 4px;
    transition: background 0.2s ease;
}

.scrollbar-custom::-webkit-scrollbar-thumb:hover {
    background: rgba(156, 163, 175, 0.5);
}

/* Dark mode scrollbar horizontal */
.dark .scrollbar-custom {
    scrollbar-color: rgba(75, 85, 99, 0.4) transparent;
}

.dark .scrollbar-custom::-webkit-scrollbar-thumb {
    background: rgba(75, 85, 99, 0.4);
}

.dark .scrollbar-custom::-webkit-scrollbar-thumb:hover {
    background: rgba(75, 85, 99, 0.6);
}

/* ========================================
   SCROLLBAR PERSONALIZADO - VERTICAL (LISTAS)
   ======================================== */

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

/* Dark mode scrollbar vertical */
.dark .scrollbar-thin {
    scrollbar-color: rgba(75, 85, 99, 0.3) transparent;
}

.dark .scrollbar-thin::-webkit-scrollbar-thumb {
    background: rgba(75, 85, 99, 0.3);
}

.dark .scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: rgba(75, 85, 99, 0.5);
}

/* ========================================
   EFECTOS DE HOVER EN ELEMENTOS INTERACTIVOS
   ======================================== */

/* Hover suave en elementos clickeables */
.group:hover {
    transform: translateY(-1px);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* ========================================
   MEJORAS DE ACCESIBILIDAD
   ======================================== */

/* Focus visible para navegación por teclado */
:deep([data-tarea-id]:focus-visible) {
    outline: 2px solid rgb(99, 102, 241);
    outline-offset: 2px;
    border-radius: 0.5rem;
}

/* Transiciones suaves en todos los elementos interactivos */
button,
a,
[role="button"] {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* ========================================
   RESPONSIVE IMPROVEMENTS
   ======================================== */

/* Ajuste de padding en móviles */
@media (max-width: 640px) {
    .scrollbar-custom {
        padding-left: 1rem;
        padding-right: 1rem;
    }
}

/* ========================================
   PERFORMANCE OPTIMIZATIONS
   ======================================== */

/* Hardware acceleration para animaciones suaves */
.animate-fade-in,
.animate-slide-in,
:deep([data-tarea-id]),
:deep(.dragging) {
    will-change: transform, opacity;
}

/* Después de la animación, remover will-change */
.animate-fade-in:not(:hover),
.animate-slide-in:not(:hover) {
    will-change: auto;
}
</style>
