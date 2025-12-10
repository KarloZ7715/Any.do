<script setup>
import { CalendarDays } from 'lucide-vue-next'
import { ref, onMounted, nextTick, watch } from 'vue'
import { Container, Draggable } from 'vue3-smooth-dnd'
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

// Composable (solo necesitamos la función de actualizar)
const { actualizarFechaTarea } = usarDragDropTareas()

// Estado local para los días y tareas (necesario para mutaciones de D&D)
const sieteDias = ref([])

// Generar estructura de días
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

        // Ordenar completadas
        tareasCompletadas.sort((a, b) => new Date(b.fecha_completada) - new Date(a.fecha_completada))

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
            todasLasTareas: [...tareasPendientes, ...tareasCompletadas],
            tareasPendientes, // Referencia para contador
        })
    }
    return dias
}

// Sincronizar estado local cuando cambian las props
watch(() => props.tareasPorFecha, () => {
    sieteDias.value = generarSieteDias()
}, { immediate: true, deep: true })

// Helper para aplicar el resultado del drag
const applyDrag = (arr, dragResult) => {
    const { removedIndex, addedIndex, payload } = dragResult
    if (removedIndex === null && addedIndex === null) return arr

    const result = [...arr]
    let itemToAdd = payload

    if (removedIndex !== null) {
        itemToAdd = result.splice(removedIndex, 1)[0]
    }

    if (addedIndex !== null) {
        result.splice(addedIndex, 0, itemToAdd)
    }

    return result
}

// Payload para D&D: Retorna el objeto tarea
const getChildPayload = (diaIndex) => {
    return (index) => {
        return sieteDias.value[diaIndex].todasLasTareas[index]
    }
}

// Manejador del Drop
const onColumnDrop = (diaIndex, dropResult) => {
    const { removedIndex, addedIndex, payload } = dropResult

    // Si no hubo cambios en esta columna, salir
    if (removedIndex === null && addedIndex === null) return

    const dia = sieteDias.value[diaIndex]

    // Actualizar estado local
    dia.todasLasTareas = applyDrag(dia.todasLasTareas, dropResult)

    // Actualizar contadores de pendientes (recalculando)
    dia.tareasPendientes = dia.todasLasTareas.filter(t => t.estado === 'pendiente')

    // Lógica de backend: Si se agregó una tarea (addedIndex !== null)
    if (addedIndex !== null && payload) {
        const tarea = payload
        const nuevaFecha = dia.fecha

        if (removedIndex === null) {
            // Tarea vino de OTRA columna
            // Obtener hora vencimiento si existe
            let horaVencimiento = null
            if (tarea.hora_vencimiento && tarea.hora_vencimiento !== '00:00') {
                horaVencimiento = tarea.hora_vencimiento
            }

            actualizarFechaTarea(tarea.id, nuevaFecha, horaVencimiento)
        }
    }
}

// Estado del modal de edición
const tareaSeleccionada = ref(null)
const modalEditarAbierto = ref(false)

const abrirModalEdicion = (tarea) => {
    tareaSeleccionada.value = tarea
    modalEditarAbierto.value = true
}

onMounted(() => {
    // Scroll inicial
    nextTick(() => {
        const contenedorScroll = document.querySelector('.scrollbar-custom')
        if (contenedorScroll) {
            contenedorScroll.scrollLeft = 0
        }
    })
})

// Fix para el posicionamiento del ghost: usar document.body como parent
// Esto evita problemas cuando hay transforms en elementos ancestros
const getGhostParent = () => {
    return document.body
}
</script>

<template>
    <LayoutPrincipal>
        <div class="h-screen flex flex-col overflow-hidden bg-background dark:bg-background">
            <!-- Header -->
            <div class="shrink-0 px-6 pt-6 pb-4 bg-background dark:bg-background">
                <div
                    class="inline-flex items-center gap-3 px-4 py-3 bg-card dark:bg-card rounded-xl border border-transparent shadow-[0_2px_16px_5px_rgba(0,0,0,0.10)] hover:shadow-[0_8px_24px_0px_rgba(0,0,0,0.15)] dark:shadow-none dark:hover:shadow-none transition-all duration-200 header-card group">
                    <div
                        class="flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 group-hover:bg-primary/20 transition-colors duration-200">
                        <CalendarDays :size="18" class="text-primary" :stroke-width="2.5" />
                    </div>
                    <h1 class="text-lg font-semibold text-foreground dark:text-foreground">
                        Próximos 7 Días
                    </h1>
                </div>
            </div>

            <!-- Contenedor Horizontal -->
            <div
                class="flex-1 overflow-x-auto overflow-y-hidden px-6 pb-6 bg-background dark:bg-background scrollbar-custom">
                <div class="h-full flex gap-3 pb-2">
                    <div v-for="(dia, diaIndex) in sieteDias" :key="dia.fecha"
                        class="shrink-0 w-[270px] flex flex-col animate-fade-in"
                        :style="{ animationDelay: `${diaIndex * 50}ms` }">

                        <div
                            class="flex flex-col bg-card dark:bg-card rounded-xl border border-transparent shadow-[0_5px_16px_-6px_rgba(0,0,0,0.30)] dark:shadow-none transition-all duration-200 max-h-full">
                            <!-- Header Columna -->
                            <div class="shrink-0 px-3 py-2.5 border-b border-transparent">
                                <h2
                                    class="text-[13px] font-semibold text-foreground dark:text-foreground flex items-center gap-2">
                                    <span class="flex items-center gap-1.5">
                                        {{ dia.etiqueta }}
                                        <span v-if="dia.subEtiqueta"
                                            class="text-[11px] font-normal text-muted-foreground dark:text-muted-foreground">
                                            {{ dia.subEtiqueta }}
                                        </span>
                                    </span>
                                    <span v-if="dia.tareasPendientes.length > 0"
                                        class="ml-auto flex items-center justify-center min-w-[18px] h-4.5 px-1 rounded-full bg-primary/10 text-[10px] font-medium text-primary">
                                        {{ dia.tareasPendientes.length }}
                                    </span>
                                </h2>
                            </div>

                            <!-- Lista / Container D&D -->
                            <div class="flex-1 overflow-y-auto overflow-x-clip px-3 py-2 min-h-0 scrollbar-thin">
                                <Container orientation="vertical" group-name="col"
                                    :get-child-payload="getChildPayload(diaIndex)" :get-ghost-parent="getGhostParent"
                                    :animation-duration="250" @drop="onColumnDrop(diaIndex, $event)"
                                    drag-class="card-ghost" drop-class="card-ghost-drop" :drop-placeholder="{
                                        className: 'drop-preview',
                                        animationDuration: 250,
                                        showOnTop: true
                                    }" class="min-h-[10px]">
                                    <Draggable v-for="tarea in dia.todasLasTareas" :key="tarea.id">
                                        <div class="draggable-item">
                                            <TareaCardMinimalista :tarea="tarea" @editar="abrirModalEdicion" />
                                        </div>
                                    </Draggable>
                                </Container>
                            </div>

                            <!-- Quick Add -->
                            <div class="shrink-0 px-2 pb-0 pt-0 border-t border-transparent">
                                <QuickAddInput :categorias="categorias" :fecha-predeterminada="dia.fecha"
                                    placeholder="+ Añadir tarea" />
                            </div>
                        </div>
                    </div>
                    <div class="shrink-0 w-4"></div>
                </div>
            </div>
        </div>

        <ModalEditarTarea v-model:open="modalEditarAbierto" :tarea="tareaSeleccionada" :categorias="categorias"
            @cerrar="tareaSeleccionada = null" />
    </LayoutPrincipal>
</template>

<style scoped>
/* Estilos para Smooth DnD */

/* Clase para el elemento que se está arrastrando (ghost) */
:deep(.card-ghost) {
    transition: transform 0.25s ease !important;
    opacity: 0.85;
    z-index: 10000;
    cursor: grabbing;
}

/* Aplicar la rotación y estilos visuales al primer hijo (el contenido real) */
:deep(.card-ghost > div) {
    transform: rotateZ(5deg);
    background-color: var(--task-card);
    box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.35);
    border-radius: 0.5rem;
}

:deep(.dark .card-ghost > div) {
    background-color: #393939;
}

:deep(.card-ghost-drop) {
    transition: transform 0.25s ease-in-out !important;
}

:deep(.card-ghost-drop > div) {
    transform: rotateZ(0deg);
    transition: transform 0.25s ease-in-out;
}

/* Estilos para items arrastrables */
.draggable-item {
    margin-bottom: 0.5rem;
}

/* Animación suave cuando los items se reorganizan */
:deep(.smooth-dnd-draggable-wrapper) {
    transition: transform 0.25s ease-out;
}

/* Placeholder donde caerá el elemento */
:deep(.drop-preview) {
    background-color: rgba(99, 102, 241, 0.15);
    border: 2px dashed rgba(99, 102, 241, 0.5);
    border-radius: 0.5rem;
    margin-bottom: 0.5rem;
    min-height: 60px;
}

/* Animaciones de entrada */
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

.animate-fade-in {
    animation: fade-in 0.4s ease-out forwards;
    opacity: 0;
}

/* Scrollbars */
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

.dark .scrollbar-custom {
    scrollbar-color: rgba(75, 85, 99, 0.4) transparent;
}

.dark .scrollbar-custom::-webkit-scrollbar-thumb {
    background: rgba(75, 85, 99, 0.4);
}

.dark .scrollbar-custom::-webkit-scrollbar-thumb:hover {
    background: rgba(75, 85, 99, 0.6);
}

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

.dark .scrollbar-thin {
    scrollbar-color: rgba(75, 85, 99, 0.3) transparent;
}

.dark .scrollbar-thin::-webkit-scrollbar-thumb {
    background: rgba(75, 85, 99, 0.3);
}

.dark .scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: rgba(75, 85, 99, 0.5);
}

/* Hover elements */
.header-card:hover {
    transform: translateY(-1px);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Override para las tareas: NADA de transform en hover para evitar glitch visual en D&D */
:deep([data-tarea-id]) {
    cursor: grab;
    transition: background-color 0.2s ease, border-color 0.2s ease;
    transform: none !important;
    /* Importante para sobreescribir .group:hover */
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
