import { defineStore } from "pinia";

/**
 * Store para gestión de tareas
 * Maneja el estado global de las tareas de la aplicación Any.do
 */
export const usarStoreTareas = defineStore("tareas", () => {
    // Estado
    const tareas = ref([]);
    const cargando = ref(false);
    const error = ref(null);
    const filtro = ref("todas"); // 'todas', 'pendientes', 'completadas'

    // Getters (computed)
    const tareasFiltradas = computed(() => {
        switch (filtro.value) {
            case "pendientes":
                return tareas.value.filter(
                    (tarea) => tarea.estado !== "completada"
                );
            case "completadas":
                return tareas.value.filter(
                    (tarea) => tarea.estado === "completada"
                );
            default:
                return tareas.value;
        }
    });

    const totalTareas = computed(() => tareas.value.length);
    const tareasPendientes = computed(
        () => tareas.value.filter((t) => t.estado !== "completada").length
    );
    const tareasCompletadas = computed(
        () => tareas.value.filter((t) => t.estado === "completada").length
    );

    // Actions (methods)
    function agregarTarea(tarea) {
        tareas.value.push({
            ...tarea,
            id: Date.now(),
            fecha_creacion: new Date().toISOString(),
        });
    }

    function actualizarTarea(id, datos) {
        const indice = tareas.value.findIndex((t) => t.id === id);
        if (indice !== -1) {
            tareas.value[indice] = { ...tareas.value[indice], ...datos };
        }
    }

    function eliminarTarea(id) {
        const indice = tareas.value.findIndex((t) => t.id === id);
        if (indice !== -1) {
            tareas.value.splice(indice, 1);
        }
    }

    function completarTarea(id) {
        actualizarTarea(id, {
            estado: "completada",
            fecha_completada: new Date().toISOString(),
        });
    }

    function establecerFiltro(nuevoFiltro) {
        filtro.value = nuevoFiltro;
    }

    async function cargarTareas() {
        cargando.value = true;
        error.value = null;
        try {
            // Aquí iría la llamada a la API
            // const response = await axios.get('/api/tareas')
            // tareas.value = response.data

            // Por ahora, datos de ejemplo
            tareas.value = [];
        } catch (err) {
            error.value = err.message;
        } finally {
            cargando.value = false;
        }
    }

    function reiniciar() {
        tareas.value = [];
        cargando.value = false;
        error.value = null;
        filtro.value = "todas";
    }

    return {
        // Estado
        tareas,
        cargando,
        error,
        filtro,
        // Getters
        tareasFiltradas,
        totalTareas,
        tareasPendientes,
        tareasCompletadas,
        // Actions
        agregarTarea,
        actualizarTarea,
        eliminarTarea,
        completarTarea,
        establecerFiltro,
        cargarTareas,
        reiniciar,
    };
});
