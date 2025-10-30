import { dragAndDrop } from '@formkit/drag-and-drop'
import { router } from '@inertiajs/vue3'
import { onUnmounted, ref } from 'vue'

export function usarDragDropTareas() {
    const instanciasDrag = ref([])

    const inicializarDragDrop = (elementosLista, onDrop) => {
        destruirDragDrop()

        elementosLista.forEach((elemento) => {
            if (!elemento) return

            const instancia = dragAndDrop({
                parent: elemento,
                getValues: (parent) => {
                    return Array.from(parent.children)
                },
                setValues: (values, parent) => {
                    values.forEach(value => parent.appendChild(value))
                },
                config: {
                    group: 'tareas',
                    draggable: (el) => {
                        // Solo elementos con data-tarea-id son arrastrables
                        return el.hasAttribute('data-tarea-id')
                    },
                    draggedClass: 'dragging',
                    dropZoneClass: 'drop-zone-active',
                    handleEnd: (data) => {
                        // Limpiar clases de selección manualmente
                        const allTareas = document.querySelectorAll('[data-tarea-id]')
                        allTareas.forEach(el => {
                            el.classList.remove('dragging', 'drop-zone-active')
                            el.style.removeProperty('outline')
                            el.style.removeProperty('outline-offset')
                        })
                        
                        if (onDrop) {
                            onDrop(data)
                        }
                    },
                },
            })

            instanciasDrag.value.push(instancia)
        })
    }

    const destruirDragDrop = () => {
        instanciasDrag.value.forEach((instancia) => {
            if (instancia && typeof instancia.destroy === 'function') {
                instancia.destroy()
            }
        })
        instanciasDrag.value = []
    }

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

    onUnmounted(() => {
        destruirDragDrop()
    })

    return {
        inicializarDragDrop,
        destruirDragDrop,
        actualizarFechaTarea,
    }
}
