import { ref } from "vue";
import { router } from "@inertiajs/vue3";

/**
 * Composable para gestión de tareas con Inertia.js
 *
 * Proporciona funciones para CRUD de tareas usando Inertia
 * con manejo de loading states y preservación de estado.
 */
export function usarTareas() {
    const cargando = ref(false);
    const procesando = ref(false);

    /**
     * Obtener tareas con filtros aplicados.
     *
     * @param {Object} filtros - Filtros a aplicar (estado, prioridad, categoria_id, etc)
     * @param {Object} opciones - Opciones adicionales de Inertia
     */
    const obtenerTareas = (filtros = {}, opciones = {}) => {
        cargando.value = true;

        const opcionesPorDefecto = {
            preserveState: true,
            preserveScroll: true,
            only: ["tareas", "filtros"],
            onFinish: () => {
                cargando.value = false;
            },
        };

        router.get(route("tareas.index"), filtros, {
            ...opcionesPorDefecto,
            ...opciones,
        });
    };

    /**
     * Crear una nueva tarea.
     *
     * @param {Object} data - Datos de la tarea
     * @param {Object} opciones - Opciones adicionales de Inertia
     */
    const crearTarea = (data, opciones = {}) => {
        procesando.value = true;

        const opcionesPorDefecto = {
            preserveScroll: true,
            onSuccess: () => {
                // Callback de éxito (cerrar modal, limpiar form, etc)
                opciones.onSuccess?.();
            },
            onError: (errors) => {
                // Callback de error (mostrar errores, etc)
                opciones.onError?.(errors);
            },
            onFinish: () => {
                procesando.value = false;
            },
        };

        router.post(route("tareas.store"), data, {
            ...opcionesPorDefecto,
            ...opciones,
        });
    };

    /**
     * Actualizar una tarea existente.
     *
     * @param {Number} id - ID de la tarea
     * @param {Object} data - Datos actualizados
     * @param {Object} opciones - Opciones adicionales de Inertia
     */
    const actualizarTarea = (id, data, opciones = {}) => {
        procesando.value = true;

        const opcionesPorDefecto = {
            preserveScroll: true,
            onSuccess: () => {
                opciones.onSuccess?.();
            },
            onError: (errors) => {
                opciones.onError?.(errors);
            },
            onFinish: () => {
                procesando.value = false;
            },
        };

        router.put(route("tareas.update", id), data, {
            ...opcionesPorDefecto,
            ...opciones,
        });
    };

    /**
     * Eliminar una tarea.
     *
     * @param {Number} id - ID de la tarea
     * @param {Object} opciones - Opciones adicionales de Inertia
     */
    const eliminarTarea = (id, opciones = {}) => {
        if (!confirm("¿Estás seguro de eliminar esta tarea?")) {
            return;
        }

        procesando.value = true;

        const opcionesPorDefecto = {
            preserveScroll: true,
            onSuccess: () => {
                opciones.onSuccess?.();
            },
            onFinish: () => {
                procesando.value = false;
            },
        };

        router.delete(route("tareas.destroy", id), {
            ...opcionesPorDefecto,
            ...opciones,
        });
    };

    /**
     * Toggle estado de una tarea (pendiente ↔ completada).
     *
     * @param {Number} id - ID de la tarea
     * @param {Object} opciones - Opciones adicionales de Inertia
     */
    const toggleEstado = (id, opciones = {}) => {
        const opcionesPorDefecto = {
            preserveScroll: true,
            preserveState: true,
            only: ["tareas"],
            onSuccess: () => {
                opciones.onSuccess?.();
            },
        };

        router.patch(
            route("tareas.toggle", id),
            {},
            { ...opcionesPorDefecto, ...opciones }
        );
    };

    return {
        // Estados
        cargando,
        procesando,

        // Métodos
        obtenerTareas,
        crearTarea,
        actualizarTarea,
        eliminarTarea,
        toggleEstado,
    };
}
