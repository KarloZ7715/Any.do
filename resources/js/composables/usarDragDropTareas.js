import { dragAndDrop } from '@formkit/drag-and-drop'
import { router } from '@inertiajs/vue3'
import { onMounted, onUnmounted, ref } from 'vue'

/**
 * Composable para implementar drag & drop de tareas entre columnas de días.
 * 
 * Actualiza la fecha_vencimiento en el backend cuando se mueve una tarea.
 */
export function usarDragDropTareas() {
    const instanciasDrag = ref([])

    /**
     * Inicializar drag & drop en múltiples listas (columnas de días).
     * 
     * @param {Array} elementosLista - Array de refs a los elementos DOM de las listas
     * @param {Function} onDrop - Callback cuando se suelta una tarea
     */
    const inicializarDragDrop = (elementosLista, onDrop) => {
        // Limpiar instancias previas
        destruirDragDrop()

        elementosLista.forEach((elemento) => {
            if (!elemento) return

            const instancia = dragAndDrop({
                parent: elemento,
                draggable: (el) => {
                    // Solo elementos con data-tarea-id son arrastrables
                    return el.hasAttribute('data-tarea-id')
                },
                dragHandle: (el) => {
                    // Toda la tarjeta es el handle de arrastre
                    return el
                },
                dropZoneClass: 'drop-zone-active',
                draggedClass: 'dragging',
                onDrop: (event) => {
                    if (onDrop) {
                        onDrop(event)
                    }
                },
            })

            instanciasDrag.value.push(instancia)
        })
    }

    /**
     * Destruir todas las instancias de drag & drop.
     */
    const destruirDragDrop = () => {
        instanciasDrag.value.forEach((instancia) => {
            if (instancia && typeof instancia.destroy === 'function') {
                instancia.destroy()
            }
        })
        instanciasDrag.value = []
    }

    /**
     * Actualizar fecha de vencimiento de una tarea en el backend.
     */
    const actualizarFechaTarea = (tareaId, nuevaFecha) => {
        router.patch(
            route('tareas.update', tareaId),
            { fecha_vencimiento: nuevaFecha },
            {
                preserveScroll: true,
                preserveState: true,
            },
        )
    }

    // Limpiar al desmontar
    onUnmounted(() => {
        destruirDragDrop()
    })

    return {
        inicializarDragDrop,
        destruirDragDrop,
        actualizarFechaTarea,
    }
}
