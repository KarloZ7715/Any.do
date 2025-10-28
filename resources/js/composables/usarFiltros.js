import { ref, watch } from "vue";
import { useDebounceFn } from "@vueuse/core";
import { usarTareas } from "./usarTareas";

/**
 * Composable para gestión de filtros de tareas.
 *
 * Maneja el estado de filtros con sincronización a URL
 * y debounce en búsqueda para optimizar requests.
 */
export function usarFiltros(filtrosIniciales = {}) {
    const { obtenerTareas } = usarTareas();

    // Estado de filtros
    const filtros = ref({
        estado: filtrosIniciales.estado || "todas",
        prioridad: filtrosIniciales.prioridad || null,
        categoria_id: filtrosIniciales.categoria_id || null,
        vencimiento: filtrosIniciales.vencimiento || null,
        buscar: filtrosIniciales.buscar || "",
        ordenar: filtrosIniciales.ordenar || "created_at",
        direccion: filtrosIniciales.direccion || "desc",
    });

    /**
     * Aplicar filtros con búsqueda debounced.
     */
    const aplicarFiltros = useDebounceFn(() => {
        const filtrosAplicar = {};

        // Solo incluir filtros con valores
        Object.keys(filtros.value).forEach((key) => {
            const valor = filtros.value[key];

            // Excluir valores vacíos, null, 'todas'
            if (valor && valor !== "todas" && valor !== "") {
                filtrosAplicar[key] = valor;
            }
        });

        obtenerTareas(filtrosAplicar);
    }, 300); // 300ms debounce

    /**
     * Limpiar todos los filtros.
     */
    const limpiarFiltros = () => {
        filtros.value = {
            estado: "todas",
            prioridad: null,
            categoria_id: null,
            vencimiento: null,
            buscar: "",
            ordenar: "created_at",
            direccion: "desc",
        };

        obtenerTareas({});
    };

    /**
     * Cambiar ordenamiento.
     *
     * @param {String} campo - Campo por el cual ordenar
     */
    const cambiarOrden = (campo) => {
        if (filtros.value.ordenar === campo) {
            // Toggle dirección si es el mismo campo
            filtros.value.direccion =
                filtros.value.direccion === "asc" ? "desc" : "asc";
        } else {
            // Nuevo campo, empezar con descendente
            filtros.value.ordenar = campo;
            filtros.value.direccion = "desc";
        }

        aplicarFiltros();
    };

    /**
     * Establecer filtro específico.
     *
     * @param {String} clave - Clave del filtro
     * @param {any} valor - Valor del filtro
     */
    const establecerFiltro = (clave, valor) => {
        filtros.value[clave] = valor;
        aplicarFiltros();
    };

    // Watch para cambios en filtros (excepto búsqueda que ya tiene debounce)
    watch(
        () => [
            filtros.value.estado,
            filtros.value.prioridad,
            filtros.value.categoria_id,
            filtros.value.vencimiento,
        ],
        () => {
            aplicarFiltros();
        }
    );

    // Watch separado para búsqueda con debounce
    watch(
        () => filtros.value.buscar,
        () => {
            aplicarFiltros();
        }
    );

    return {
        // Estado
        filtros,

        // Métodos
        aplicarFiltros,
        limpiarFiltros,
        cambiarOrden,
        establecerFiltro,
    };
}
