import { ref, computed } from 'vue'

// Estado global del sidebar (persistente entre componentes)
const estaColapsado = ref(false)
const estaFijado = ref(true) // Por defecto está fijado (expandido)
const estaHover = ref(false)

const STORAGE_KEY_FIJADO = 'anydo_sidebar_fijado'

// Cargar estado inicial desde localStorage
const cargarEstadoInicial = () => {
    // eslint-disable-next-line no-undef
    const estadoGuardado = localStorage.getItem(STORAGE_KEY_FIJADO)
    if (estadoGuardado !== null) {
        estaFijado.value = estadoGuardado === 'true'
        // Si no está fijado, empieza colapsado
        if (!estaFijado.value) {
            estaColapsado.value = true
        }
    }
}

// Inicializar al cargar el módulo
cargarEstadoInicial()

export function usarSidebar() {
    /**
     * Alternar el estado de fijado del sidebar
     */
    const alternarFijado = () => {
        estaFijado.value = !estaFijado.value
        // eslint-disable-next-line no-undef
        localStorage.setItem(STORAGE_KEY_FIJADO, estaFijado.value.toString())
        
        // Si se desfija, colapsar inmediatamente
        if (!estaFijado.value && !estaHover.value) {
            estaColapsado.value = true
        }
        // Si se fija, expandir inmediatamente
        if (estaFijado.value) {
            estaColapsado.value = false
        }
    }

    /**
     * Manejar mouse enter en el sidebar
     */
    const handleMouseEnter = () => {
        estaHover.value = true
        // Si no está fijado, expandir al hacer hover
        if (!estaFijado.value) {
            estaColapsado.value = false
        }
    }

    /**
     * Manejar mouse leave del sidebar
     */
    const handleMouseLeave = () => {
        estaHover.value = false
        // Si no está fijado, colapsar al quitar el mouse
        if (!estaFijado.value) {
            estaColapsado.value = true
        }
    }

    /**
     * Ancho dinámico del sidebar (para animaciones)
     */
    const anchoSidebar = computed(() => {
        return estaColapsado.value ? '60px' : '250px'
    })

    /**
     * Clases CSS dinámicas según estado
     */
    const clasesSidebar = computed(() => ({
        'w-[60px]': estaColapsado.value,
        'w-[250px]': !estaColapsado.value,
    }))

    return {
        estaColapsado,
        estaFijado,
        estaHover,
        alternarFijado,
        handleMouseEnter,
        handleMouseLeave,
        anchoSidebar,
        clasesSidebar,
    }
}
