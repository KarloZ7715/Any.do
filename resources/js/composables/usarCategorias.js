import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

/**
 * Composable para gestión de categorías.
 */
export function usarCategorias() {
    const procesando = ref(false)

    /**
     * Navegar a la página de categorías.
     */
    const irACategorias = () => {
        router.get(route('categorias.index'))
    }

    /**
     * Crear una nueva categoría.
     * 
     * @param {Object} data - Datos de la categoría
     * @param {Object} opciones - Opciones adicionales de Inertia
     */
    const crearCategoria = (data, opciones = {}) => {
        procesando.value = true

        const opcionesPorDefecto = {
            preserveScroll: true,
            onSuccess: () => {
                opciones.onSuccess?.()
            },
            onError: (errors) => {
                opciones.onError?.(errors)
            },
            onFinish: () => {
                procesando.value = false
            },
        }

        router.post(route('categorias.store'), data, {
            ...opcionesPorDefecto,
            ...opciones,
        })
    }

    /**
     * Actualizar una categoría existente.
     * 
     * @param {Number} id - ID de la categoría
     * @param {Object} data - Datos actualizados
     * @param {Object} opciones - Opciones adicionales de Inertia
     */
    const actualizarCategoria = (id, data, opciones = {}) => {
        procesando.value = true

        const opcionesPorDefecto = {
            preserveScroll: true,
            onSuccess: () => {
                opciones.onSuccess?.()
            },
            onError: (errors) => {
                opciones.onError?.(errors)
            },
            onFinish: () => {
                procesando.value = false
            },
        }

        router.put(route('categorias.update', { categoria: id }), data, {
            ...opcionesPorDefecto,
            ...opciones,
        })
    }

    /**
     * Eliminar una categoría.
     * Si tiene tareas, se moverán a la categoría Personal.
     * 
     * @param {Number} id - ID de la categoría
     * @param {Object} categoria - Objeto categoría con es_personal y tareas_count
     * @param {Object} opciones - Opciones adicionales de Inertia
     */
    const eliminarCategoria = (id, categoria, opciones = {}) => {
        // Validación: No eliminar categoría Personal
        if (categoria.es_personal) {
            alert('No se puede eliminar la categoría Personal')
            return
        }

        // Mensaje de confirmación personalizado
        let mensaje = '¿Estás seguro de eliminar esta categoría?'
        
        if (categoria.tareas_count > 0) {
            mensaje = `Esta categoría tiene ${categoria.tareas_count} tarea(s). ¿Deseas moverlas a la categoría Personal y eliminar la categoría?`
        }

        if (!confirm(mensaje)) {
            return
        }

        procesando.value = true

        const opcionesPorDefecto = {
            preserveScroll: true,
            onSuccess: () => {
                opciones.onSuccess?.()
            },
            onFinish: () => {
                procesando.value = false
            },
        }

        router.delete(route('categorias.destroy', { categoria: id }), {
            ...opcionesPorDefecto,
            ...opciones,
        })
    }

    return {
        procesando,
        irACategorias,
        crearCategoria,
        actualizarCategoria,
        eliminarCategoria,
    }
}
