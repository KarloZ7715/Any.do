import { ref, computed } from 'vue';

// Estado global del sidebar (persistente entre componentes)
const estaColapsado = ref(false);
const STORAGE_KEY = 'anydo_sidebar_colapsado';

// Cargar estado inicial desde localStorage
const cargarEstadoInicial = () => {
    const estadoGuardado = localStorage.getItem(STORAGE_KEY);
    if (estadoGuardado !== null) {
        estaColapsado.value = estadoGuardado === 'true';
    }
};

// Inicializar al cargar el módulo
cargarEstadoInicial();

export function usarSidebar() {
    /**
     * Alternar el estado del sidebar (colapsado/expandido)
     */
    const alternarSidebar = () => {
        estaColapsado.value = !estaColapsado.value;
        localStorage.setItem(STORAGE_KEY, estaColapsado.value.toString());
    };

    /**
     * Colapsar el sidebar
     */
    const colapsar = () => {
        estaColapsado.value = true;
        localStorage.setItem(STORAGE_KEY, 'true');
    };

    /**
     * Expandir el sidebar
     */
    const expandir = () => {
        estaColapsado.value = false;
        localStorage.setItem(STORAGE_KEY, 'false');
    };

    /**
     * Ancho dinámico del sidebar (para animaciones)
     */
    const anchoSidebar = computed(() => {
        return estaColapsado.value ? '60px' : '250px';
    });

    /**
     * Clases CSS dinámicas según estado
     */
    const clasesSidebar = computed(() => ({
        'w-[60px]': estaColapsado.value,
        'w-[250px]': !estaColapsado.value,
    }));

    return {
        estaColapsado,
        alternarSidebar,
        colapsar,
        expandir,
        anchoSidebar,
        clasesSidebar,
    };
}
