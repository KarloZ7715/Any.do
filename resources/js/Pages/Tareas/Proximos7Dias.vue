<script setup>
import { CalendarDays } from 'lucide-vue-next'
import { ref, onMounted, nextTick, toRef } from 'vue'
import { Container, Draggable } from 'vue3-smooth-dnd'
import LayoutPrincipal from '@/Layouts/LayoutPrincipal.vue'
import QuickAddInput from '@/Components/QuickAddInput.vue'
import TareaCardMinimalista from '@/Components/TareaCardMinimalista.vue'
import ModalEditarTarea from '@/Components/ModalEditarTarea.vue'
import HeaderPagina from '@/Components/ui/HeaderPagina.vue'
import { usarDragDropTareas } from '@/composables/usarDragDropTareas'
import { usarProximos7Dias } from '@/composables/usarProximos7Dias'

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

// Composable para drag & drop
const { actualizarFechaTarea } = usarDragDropTareas()

// Usar composable para generar estructura de 7 días
const { sieteDias } = usarProximos7Dias(toRef(props, 'tareasPorFecha'))

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
        <div class="h-screen flex flex-col overflow-hidden bg-background">
            <!-- Header -->
            <div class="shrink-0 px-6 pt-6 pb-4 bg-background">
                <HeaderPagina titulo="Próximos 7 Días">
                    <template #icono>
                        <CalendarDays :size="18" class="text-primary" :stroke-width="2.5" />
                    </template>
                </HeaderPagina>
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
/* ========================================
   ESTILOS ESPECÍFICOS DE DRAG & DROP
   ======================================== */

/* Ghost element mientras se arrastra */
:deep(.card-ghost) {
    transition: transform 0.25s ease !important;
    opacity: 0.85;
    z-index: 10000;
    cursor: grabbing;
}

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

/* Items arrastrables */
.draggable-item {
    margin-bottom: 0.5rem;
}

:deep(.smooth-dnd-draggable-wrapper) {
    transition: transform 0.25s ease-out;
}

/* Placeholder de drop */
:deep(.drop-preview) {
    background-color: rgba(99, 102, 241, 0.15);
    border: 2px dashed rgba(99, 102, 241, 0.5);
    border-radius: 0.5rem;
    margin-bottom: 0.5rem;
    min-height: 60px;
}

/* Tarjetas de tareas en D&D - sin transform en hover */
:deep([data-tarea-id]) {
    cursor: grab;
    transition: background-color 0.2s ease, border-color 0.2s ease;
    transform: none !important;
}

:deep([data-tarea-id]:focus-visible) {
    outline: 2px solid rgb(99, 102, 241);
    outline-offset: 2px;
    border-radius: 0.5rem;
}

/* Header card hover */
.header-card:hover {
    transform: translateY(-1px);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
